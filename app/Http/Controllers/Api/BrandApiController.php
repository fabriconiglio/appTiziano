<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DistributorBrand;
use Illuminate\Http\JsonResponse;

class BrandApiController extends Controller
{
    public function index(): JsonResponse
    {
        $brands = DistributorBrand::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description', 'logo_url']);

        return response()->json($brands);
    }
}
