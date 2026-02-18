@extends('admin.layout')

@section('title', 'Edit product')
@section('page_title', 'Edit product: ' . $product->name)

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <a href="{{ route('admin.products.show', $product) }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to product
        </a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 px-6 py-4">
            <h2 class="text-base font-semibold text-slate-800">Marketing & deal settings</h2>
        </div>
        <form method="POST" action="{{ route('admin.products.update', $product) }}" class="p-6">
            @csrf
            @method('PUT')
            <div class="grid gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2 flex flex-wrap gap-6">
                    <label class="flex cursor-pointer items-center gap-2">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-slate-800 focus:ring-slate-500">
                        <span class="text-sm font-medium text-slate-700">Featured</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-2">
                        <input type="hidden" name="is_trending" value="0">
                        <input type="checkbox" name="is_trending" value="1" {{ old('is_trending', $product->is_trending) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-slate-800 focus:ring-slate-500">
                        <span class="text-sm font-medium text-slate-700">Trending</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-slate-800 focus:ring-slate-500">
                        <span class="text-sm font-medium text-slate-700">Active</span>
                    </label>
                </div>
                <div>
                    <label for="total_sales" class="block text-sm font-medium text-slate-700 mb-1.5">Total sales</label>
                    <input type="number" id="total_sales" name="total_sales" value="{{ old('total_sales', $product->total_sales ?? 0) }}" min="0" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-slate-800">
                    @error('total_sales')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="popularity_score" class="block text-sm font-medium text-slate-700 mb-1.5">Popularity score</label>
                    <input type="number" id="popularity_score" name="popularity_score" value="{{ old('popularity_score', $product->popularity_score ?? 0) }}" min="0" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-slate-800">
                    @error('popularity_score')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2 border-t border-slate-200 pt-6">
                    <h3 class="text-sm font-semibold text-slate-700 mb-3">Hot deal</h3>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label for="deal_price" class="block text-sm font-medium text-slate-700 mb-1.5">Deal price</label>
                            <input type="number" id="deal_price" name="deal_price" value="{{ old('deal_price', $product->deal_price) }}" step="0.01" min="0" placeholder="Leave empty to disable" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-slate-800">
                            @error('deal_price')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="deal_start" class="block text-sm font-medium text-slate-700 mb-1.5">Deal start</label>
                            <input type="datetime-local" id="deal_start" name="deal_start" value="{{ old('deal_start', $product->deal_start?->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-slate-800">
                            @error('deal_start')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="deal_end" class="block text-sm font-medium text-slate-700 mb-1.5">Deal end</label>
                            <input type="datetime-local" id="deal_end" name="deal_end" value="{{ old('deal_end', $product->deal_end?->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-slate-800">
                            @error('deal_end')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-2 border-t border-slate-200 pt-6">
                    <label class="block text-sm font-medium text-slate-700 mb-3">Tags</label>
                    <p class="text-xs text-slate-500 mb-3">Select tags to assign to this product</p>
                    <div class="flex flex-wrap gap-2">
                        @php
                            $selectedTagIds = old('tag_ids', $product->tags->pluck('id')->toArray());
                        @endphp
                        @foreach($tags as $tag)
                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border-2 px-3 py-2 transition-all
                                {{ in_array($tag->id, $selectedTagIds) ? 'border-slate-800 bg-slate-100' : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50' }}">
                                <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" {{ in_array($tag->id, $selectedTagIds) ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-slate-300 text-slate-800 focus:ring-slate-500">
                                @if($tag->color)
                                    <span class="h-3 w-3 shrink-0 rounded-full border border-slate-200" style="background-color: {{ $tag->color }}"></span>
                                @endif
                                <span class="text-sm font-medium text-slate-700">{{ $tag->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @if($tags->isEmpty())
                        <p class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">No tags available. <a href="{{ route('admin.tags.create') }}" class="font-medium underline hover:no-underline">Create tags</a> first.</p>
                    @endif
                    @error('tag_ids')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="mt-8 flex flex-wrap gap-3">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-5 py-2.5 font-medium text-white shadow-sm transition-all hover:bg-slate-700">Update</button>
                <a href="{{ route('admin.products.show', $product) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 font-medium text-slate-600 shadow-sm transition-all hover:bg-slate-50">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
