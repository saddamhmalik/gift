<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $vouchers = Voucher::query()
            ->where('is_active', true)
            ->when($request->filled('brand_id'), fn ($q) => $q->where('brand_id', $request->brand_id))
            ->when($request->filled('category_id'), fn ($q) => $q->whereHas('brand', fn ($q) => $q->where('category_id', $request->category_id)))
            ->when($request->boolean('with_brand'), fn ($q) => $q->with('brand'))
            ->orderBy('name')
            ->get();

        return $this->success($vouchers);
    }

    public function show(Voucher $voucher): JsonResponse
    {
        if (! $voucher->is_active) {
            return $this->error('Voucher not found', 404);
        }

        $voucher->load('brand');

        return $this->success($voucher);
    }
}
