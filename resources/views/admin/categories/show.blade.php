@extends('admin.layout')

@section('title', 'Category details')
@section('page_title', $category->name)

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to categories
        </a>
        <a href="{{ route('admin.categories.edit', $category) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 shadow-sm hover:bg-slate-50">Edit</a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 px-6 py-4 flex flex-wrap items-center gap-4">
            @if($category->thumbnail_url || $category->image_url)
                <img src="{{ $category->thumbnail_url ?? $category->image_url }}" alt="" class="h-16 w-16 rounded-lg object-cover bg-slate-100">
            @endif
            <div>
                <h2 class="text-lg font-semibold text-slate-800">{{ $category->name }}</h2>
                <p class="text-sm text-slate-500">Slug: {{ $category->slug }} · External ID: {{ $category->external_id ?? '—' }}</p>
            </div>
        </div>
        <div class="p-6 grid gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Description</h3>
                <p class="text-sm text-slate-600 whitespace-pre-wrap">{{ $category->description ?? '—' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Short description</h3>
                <p class="text-sm text-slate-600">{{ $category->short_description ?? '—' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Parent</h3>
                <p class="text-sm text-slate-600">
                    @if($category->parent)
                        <a href="{{ route('admin.categories.show', $category->parent) }}" class="text-slate-600 hover:underline">{{ $category->parent->name }}</a>
                    @else
                        —
                    @endif
                </p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-700 mb-2">URL</h3>
                <p class="text-sm text-slate-600 break-all">{{ $category->url ?? '—' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Canonical URL</h3>
                <p class="text-sm text-slate-600 break-all">{{ $category->canonical_url ?? '—' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Offer description</h3>
                <p class="text-sm text-slate-600">{{ $category->offer_description ?? '—' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Color / Background</h3>
                <p class="text-sm text-slate-600">
                    @if($category->color_code || $category->bg_color_code)
                        {{ $category->color_code ?? '—' }} / {{ $category->bg_color_code ?? '—' }}
                    @else
                        —
                    @endif
                </p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Subcategories count</h3>
                <p class="text-sm text-slate-600">{{ $category->subcategories_count }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Sort order</h3>
                <p class="text-sm text-slate-600">{{ $category->sort_order }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Last sync</h3>
                <p class="text-sm text-slate-600">{{ $category->last_synced_at?->format('M j, Y H:i') ?? '—' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Status</h3>
                <p class="text-sm">
                    @if($category->is_active)
                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">Active</span>
                    @else
                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">Inactive</span>
                    @endif
                </p>
            </div>
            @if($category->image_url && $category->image_url !== ($category->thumbnail_url ?? ''))
            <div class="sm:col-span-2">
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Image</h3>
                <img src="{{ $category->image_url }}" alt="" class="max-h-48 rounded-lg border border-slate-200 object-contain bg-slate-50">
            </div>
            @endif
            @if($category->children->isNotEmpty())
            <div class="sm:col-span-2">
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Subcategories ({{ $category->children->count() }})</h3>
                <ul class="rounded-lg border border-slate-200 divide-y divide-slate-100">
                    @foreach($category->children as $child)
                    <li class="px-4 py-2 flex items-center justify-between">
                        <a href="{{ route('admin.categories.show', $child) }}" class="text-sm font-medium text-slate-800 hover:underline">{{ $child->name }}</a>
                        <span class="text-xs text-slate-500">{{ $child->products_count }} products</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
            <div class="sm:col-span-2">
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Products ({{ $category->products_count }})</h3>
                @if($products->isNotEmpty())
                <ul class="rounded-lg border border-slate-200 divide-y divide-slate-100">
                    @foreach($products as $product)
                    <li class="px-4 py-2">
                        <a href="{{ route('admin.products.show', $product) }}" class="text-sm font-medium text-slate-800 hover:underline">{{ $product->name }}</a>
                        <span class="text-xs text-slate-500 ml-2">{{ $product->external_id }}</span>
                    </li>
                    @endforeach
                </ul>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    {{ $products->links() }}
                    <a href="{{ route('admin.products.index', ['category' => $category->id]) }}" class="text-sm text-slate-600 hover:underline">View all in Products →</a>
                </div>
                @else
                <p class="text-sm text-slate-500">No products in this category.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
