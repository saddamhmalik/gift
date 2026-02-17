@extends('admin.layout')

@section('title', 'Product details')
@section('page_title', $product->name)

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to products
            </a>
            <a href="{{ route('admin.products.edit', $product) }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Edit marketing & tags</a>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 px-6 py-4 flex flex-wrap items-center gap-4">
            @if($product->thumbnail_url)
                <img src="{{ $product->thumbnail_url }}" alt="" class="h-16 w-16 rounded-lg object-cover bg-slate-100">
            @endif
            <div>
                <h2 class="text-lg font-semibold text-slate-800">{{ $product->name }}</h2>
                <p class="text-sm text-slate-500">SKU: {{ $product->external_id ?? '—' }} · Category: {{ $product->category?->name ?? '—' }}</p>
            </div>
        </div>
        <div class="p-6 grid gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Description</h3>
                <p class="text-sm text-slate-600 whitespace-pre-wrap">{{ $product->description ?? '—' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Offer short description</h3>
                <p class="text-sm text-slate-600">{{ $product->offer_short_desc ?? '—' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Type</h3>
                <p class="text-sm text-slate-600">{{ $product->product_type ?? '—' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Price</h3>
                <p class="text-sm text-slate-600">
                    @if($product->min_price !== null || $product->max_price !== null)
                        {{ $product->min_price !== null ? number_format($product->min_price, 2) : '—' }} – {{ $product->max_price !== null ? number_format($product->max_price, 2) : '—' }} {{ $product->currency_code ?? '' }}
                    @else
                        —
                    @endif
                    @if($product->price_type)
                        <span class="text-slate-400">({{ $product->price_type }})</span>
                    @endif
                </p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Denominations</h3>
                <p class="text-sm text-slate-600">
                    @if($product->denominations && count($product->denominations) > 0)
                        {{ implode(', ', $product->denominations) }}
                    @else
                        —
                    @endif
                </p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Purchaser limit</h3>
                <p class="text-sm text-slate-600">{{ $product->purchaser_limit ?? '—' }}</p>
            </div>
            <div class="sm:col-span-2">
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Purchaser description</h3>
                <p class="text-sm text-slate-600 whitespace-pre-wrap">{{ $product->purchaser_description ?? '—' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-700 mb-2">URL</h3>
                <p class="text-sm text-slate-600 break-all">{{ $product->url ?? '—' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Status</h3>
                <p class="text-sm">
                    @if($product->is_active)
                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">Active</span>
                    @else
                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">Inactive</span>
                    @endif
                </p>
            </div>
            @if($product->image_url)
            <div class="sm:col-span-2">
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Image</h3>
                <img src="{{ $product->image_url }}" alt="" class="max-h-48 rounded-lg border border-slate-200 object-contain bg-slate-50">
            </div>
            @endif
            @if($product->tnc_link || $product->tnc_content)
            <div class="sm:col-span-2">
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Terms & conditions</h3>
                @if($product->tnc_link)
                    <p class="text-sm mb-3"><a href="{{ $product->tnc_link }}" target="_blank" rel="noopener" class="text-slate-600 hover:underline break-all">{{ $product->tnc_link }}</a></p>
                @endif
                @if($product->tnc_content)
                    <div class="text-sm text-slate-600 rounded-lg border border-slate-200 bg-slate-50 p-4 max-h-96 overflow-y-auto [&_ol]:list-decimal [&_ol]:pl-5 [&_ol]:space-y-1 [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:space-y-1 [&_li]:my-0.5">
                        {!! $product->tnc_content !!}
                    </div>
                @endif
            </div>
            @endif
            @if($product->related_product_options && count((array) $product->related_product_options) > 0)
            <div class="sm:col-span-2">
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Related product options</h3>
                <pre class="text-xs text-slate-600 bg-slate-50 rounded-lg p-4 overflow-x-auto">{{ json_encode($product->related_product_options, JSON_PRETTY_PRINT) }}</pre>
            </div>
            @endif
            @if($product->corporate_discounts && count((array) $product->corporate_discounts) > 0)
            <div class="sm:col-span-2">
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Corporate discounts</h3>
                <pre class="text-xs text-slate-600 bg-slate-50 rounded-lg p-4 overflow-x-auto max-h-64 overflow-y-auto">{{ json_encode($product->corporate_discounts, JSON_PRETTY_PRINT) }}</pre>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
