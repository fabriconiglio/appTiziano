<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SupplierInventory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($request->filled('featured')) {
            return $this->featuredIndex($request);
        }

        $query = Product::with(['category', 'brand'])
            ->where('current_stock', '>', 0);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->orderBy('name')->paginate(24);

        return response()->json($products);
    }

    /**
     * Listado destacados: productos de peluquería + inventario proveedor/distribuidora.
     */
    private function featuredIndex(Request $request): JsonResponse
    {
        $fromInventory = SupplierInventory::with(['distributorCategory', 'distributorBrand'])
            ->where('is_featured', true)
            ->where('stock_quantity', '>', 0)
            ->orderBy('product_name')
            ->get()
            ->map(fn (SupplierInventory $item) => $this->mapSupplierInventoryToApiProduct($item));

        $fromProducts = Product::with(['category', 'brand'])
            ->where('is_featured', true)
            ->where('current_stock', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(fn (Product $p) => $this->mapProductToApiArray($p));

        $merged = $fromInventory->concat($fromProducts)->sortBy('name')->values();

        $page = max(1, (int) $request->input('page', 1));
        $perPage = 24;
        $total = $merged->count();
        $items = $merged->slice(($page - 1) * $perPage, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return response()->json($paginator);
    }

    public function show(int $id): JsonResponse
    {
        $product = Product::with(['category', 'brand'])->find($id);
        if ($product) {
            return response()->json($product);
        }

        $inventory = SupplierInventory::with(['distributorCategory', 'distributorBrand'])->find($id);
        if ($inventory) {
            return response()->json($this->mapSupplierInventoryToApiProduct($inventory));
        }

        abort(404);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapSupplierInventoryToApiProduct(SupplierInventory $item): array
    {
        $cat = $item->distributorCategory;
        $brand = $item->distributorBrand;
        $price = $item->precio_menor ?? $item->price ?? 0;

        return [
            'id' => $item->id,
            'name' => $item->product_name,
            'description' => $item->description,
            'sku' => $item->sku,
            'price' => $price,
            'is_featured' => (bool) $item->is_featured,
            'current_stock' => $item->stock_quantity,
            'minimum_stock' => 0,
            'supplier_name' => $item->supplier_name,
            'category_id' => $cat?->id,
            'brand_id' => $brand?->id,
            'category' => $cat ? [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'description' => $cat->description,
            ] : null,
            'brand' => $brand ? [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'description' => $brand->description,
                'logo_url' => $brand->logo_url,
            ] : null,
            'created_at' => $item->created_at?->toIso8601String(),
            'updated_at' => $item->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapProductToApiArray(Product $p): array
    {
        $cat = $p->category;
        $brand = $p->brand;

        return [
            'id' => $p->id,
            'name' => $p->name,
            'description' => $p->description,
            'sku' => $p->sku,
            'price' => $p->price,
            'is_featured' => (bool) $p->is_featured,
            'current_stock' => $p->current_stock,
            'minimum_stock' => $p->minimum_stock,
            'supplier_name' => $p->supplier_name,
            'category_id' => $p->category_id,
            'brand_id' => $p->brand_id,
            'category' => $cat ? [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'description' => $cat->description,
            ] : null,
            'brand' => $brand ? [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'description' => $brand->description,
                'logo_url' => $brand->logo_url,
            ] : null,
            'created_at' => $p->created_at?->toIso8601String(),
            'updated_at' => $p->updated_at?->toIso8601String(),
        ];
    }
}
