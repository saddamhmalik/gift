@extends('admin.layout')

@section('title', 'Tags')
@section('page_title', 'Tags')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <a href="{{ route('admin.tags.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-all hover:bg-slate-700">
            Add tag
        </a>
        <form action="{{ route('admin.cache.clear') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 shadow-sm transition-all hover:bg-slate-50">
                Clear API cache
            </button>
        </form>
    </div>

    <form method="GET" class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tags..." class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm">
        <button type="submit" class="rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-medium text-white">Search</button>
    </form>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-600">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-600">Slug</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-600">Products</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-600">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-slate-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
                @forelse($tags as $tag)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-slate-800">{{ $tag->name }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $tag->slug }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $tag->products_count }}</td>
                        <td class="px-6 py-4">
                            @if($tag->is_active)
                                <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">Active</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.tags.edit', $tag) }}" class="text-slate-600 hover:text-slate-800">Edit</a>
                            <form action="{{ route('admin.tags.destroy', $tag) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Delete this tag?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">No tags yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($tags->hasPages())
            <div class="border-t border-slate-200 px-6 py-3">{{ $tags->links() }}</div>
        @endif
    </div>
</div>
@endsection
