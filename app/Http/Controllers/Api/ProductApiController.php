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

        $query = SupplierInventory::with(['distributorCategory', 'distributorBrand'])
            ->where('stock_quantity', '>', 0);

        if ($request->filled('category_id')) {
            $query->where('distributor_category_id', $request->category_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('distributor_brand_id', $request->brand_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $paginated = $query->orderBy('product_name')->paginate(24);

        $mapped = $paginated->through(fn (SupplierInventory $item) => $this->mapSupplierInventoryToApiProduct($item));

        return response()->json($mapped);
    }

    /**
     * Listado destacados: solo inventario proveedor/distribuidora (no productos de peluquería).
     */
    private function featuredIndex(Request $request): JsonResponse
    {
        $merged = SupplierInventory::with(['distributorCategory', 'distributorBrand'])
            ->where('is_featured', true)
            ->orderByRaw('CASE WHEN stock_quantity > 0 THEN 0 ELSE 1 END')
            ->orderBy('product_name')
            ->get()
            ->map(fn (SupplierInventory $item) => $this->mapSupplierInventoryToApiProduct($item))
            ->sortBy('name')
            ->values();

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
        $inventory = SupplierInventory::with(['distributorCategory', 'distributorBrand'])->find($id);
        if ($inventory) {
            return response()->json($this->mapSupplierInventoryToApiProduct($inventory));
        }

        $product = Product::with(['category', 'brand'])->find($id);
        if ($product) {
            return response()->json($product);
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
        $imageUrls = $item->image_urls;
        $imageUrl = $imageUrls[0] ?? null;

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
            'image_url' => $imageUrl,
            'image_urls' => $imageUrls,
            'created_at' => $item->created_at?->toIso8601String(),
            'updated_at' => $item->updated_at?->toIso8601String(),
        ];
    }
}
