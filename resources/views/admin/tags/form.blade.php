@extends('admin.layout')

@section('title', $tag ? 'Edit tag' : 'Add tag')
@section('page_title', $tag ? 'Edit tag' : 'Add tag')

@section('content')
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="border-b border-slate-100 px-6 py-4">
        <h2 class="text-base font-semibold text-slate-800">{{ $tag ? 'Edit tag' : 'Add tag' }}</h2>
    </div>
    <form method="POST" action="{{ $tag ? route('admin.tags.update', $tag) : route('admin.tags.store') }}" class="p-6">
        @csrf
        @if($tag) @method('PUT') @endif
        <div class="grid gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $tag?->name) }}" required class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-slate-800">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="slug" class="block text-sm font-medium text-slate-700 mb-1.5">Slug</label>
                <input type="text" id="slug" name="slug" value="{{ old('slug', $tag?->slug) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-slate-800" placeholder="Auto-generated if empty">
                @error('slug')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="color" class="block text-sm font-medium text-slate-700 mb-1.5">Color (hex)</label>
                <input type="text" id="color" name="color" value="{{ old('color', $tag?->color) }}" placeholder="#3c8dbc" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-slate-800">
                @error('color')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="sort_order" class="block text-sm font-medium text-slate-700 mb-1.5">Sort order</label>
                <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $tag?->sort_order ?? 0) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-slate-800">
                @error('sort_order')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2 flex items-center">
                <input type="hidden" name="is_active" value="0">
                <label class="flex cursor-pointer items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $tag?->is_active ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-slate-800">
                    <span class="text-sm font-medium text-slate-700">Active</span>
                </label>
            </div>
        </div>
        <div class="mt-8 flex flex-wrap gap-3">
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-5 py-2.5 font-medium text-white shadow-sm hover:bg-slate-700">{{ $tag ? 'Update' : 'Create' }}</button>
            <a href="{{ route('admin.tags.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 font-medium text-slate-600 shadow-sm hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
@endsection
