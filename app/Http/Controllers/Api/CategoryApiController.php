<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DistributorCategory;
use Illuminate\Http\JsonResponse;

class CategoryApiController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = DistributorCategory::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description']);

        return response()->json($categories);
    }
}
