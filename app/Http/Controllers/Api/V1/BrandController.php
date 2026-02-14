<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $brands = Brand::query()
            ->where('is_active', true)
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->boolean('with_category'), fn ($q) => $q->with('category'))
            ->when($request->boolean('with_vouchers'), fn ($q) => $q->with(['vouchers' => fn ($q) => $q->where('is_active', true)]))
            ->orderBy('name')
            ->get();

        return $this->success($brands);
    }

    public function show(Brand $brand): JsonResponse
    {
        if (! $brand->is_active) {
            return $this->error('Brand not found', 404);
        }

        $brand->load(['category', 'vouchers' => fn ($q) => $q->where('is_active', true)]);

        return $this->success($brand);
    }
}
