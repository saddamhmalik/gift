@extends('admin.layout')

@section('title', $category ? 'Edit category' : 'Add category')
@section('page_title', $category ? 'Edit category' : 'Add category')

@section('content')
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="border-b border-slate-100 px-6 py-4">
        <h2 class="text-base font-semibold text-slate-800">{{ $category ? 'Edit category' : 'Add category' }}</h2>
    </div>
    <form method="POST" action="{{ $category ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="p-6">
        @csrf
        @if($category) @method('PUT') @endif
        <div class="grid gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $category?->name) }}" required class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-slate-800 transition-all focus:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-200">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="slug" class="block text-sm font-medium text-slate-700 mb-1.5">Slug</label>
                <input type="text" id="slug" name="slug" value="{{ old('slug', $category?->slug) }}" required class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-slate-800 transition-all focus:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-200">
                @error('slug')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="parent_id" class="block text-sm font-medium text-slate-700 mb-1.5">Parent</label>
                <select id="parent_id" name="parent_id" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-slate-800 transition-all focus:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-200">
                    <option value="">None</option>
                    @foreach($parents as $p)
                        <option value="{{ $p->id }}" {{ old('parent_id', $category?->parent_id) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
                @error('parent_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="description" class="block text-sm font-medium text-slate-700 mb-1.5">Description</label>
                <textarea id="description" name="description" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-slate-800 transition-all focus:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-200">{{ old('description', $category?->description) }}</textarea>
                @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="image_url" class="block text-sm font-medium text-slate-700 mb-1.5">Image URL</label>
                <input type="url" id="image_url" name="image_url" value="{{ old('image_url', $category?->image_url) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-slate-800 transition-all focus:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-200">
                @error('image_url')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="color_code" class="block text-sm font-medium text-slate-700 mb-1.5">Color code</label>
                <input type="text" id="color_code" name="color_code" value="{{ old('color_code', $category?->color_code) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-slate-800 transition-all focus:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-200">
                @error('color_code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="offer_description" class="block text-sm font-medium text-slate-700 mb-1.5">Offer description</label>
                <input type="text" id="offer_description" name="offer_description" value="{{ old('offer_description', $category?->offer_description) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-slate-800 transition-all focus:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-200">
                @error('offer_description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="sort_order" class="block text-sm font-medium text-slate-700 mb-1.5">Sort order</label>
                <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $category?->sort_order ?? 0) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-slate-800 transition-all focus:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-200">
                @error('sort_order')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2 flex items-center">
                <input type="hidden" name="is_active" value="0">
                <label class="flex cursor-pointer items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category?->is_active ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-slate-800 focus:ring-slate-500">
                    <span class="text-sm font-medium text-slate-700">Active</span>
                </label>
            </div>
        </div>
        <div class="mt-8 flex flex-wrap gap-3">
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-5 py-2.5 font-medium text-white shadow-sm transition-all hover:bg-slate-700">{{ $category ? 'Update' : 'Create' }}</button>
            <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 font-medium text-slate-600 shadow-sm transition-all hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
@endsection
