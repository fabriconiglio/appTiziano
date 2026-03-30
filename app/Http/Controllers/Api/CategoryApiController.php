<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryApiController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->where('module_type', 'peluqueria')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description']);

        return response()->json($categories);
    }
}
