<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DistributorBrand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = DistributorBrand::where('is_active', true);

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        $brands = $query->orderBy('name')
            ->get(['id', 'name', 'slug', 'description', 'logo_url', 'is_featured']);

        return response()->json($brands);
    }
}
