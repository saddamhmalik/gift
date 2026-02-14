@extends('admin.layout')

@section('title', 'Categories')
@section('page_title', 'Categories')

@section('content')
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-base font-semibold text-slate-800">Synced categories</h2>
        <div class="flex flex-wrap gap-2">
            <form action="{{ route('admin.categories.sync') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-all hover:bg-emerald-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Sync from Woohoo
                </button>
            </form>
            <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-all hover:bg-slate-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add category
            </a>
        </div>
    </div>
    <div class="p-6">
        <form method="GET" class="mb-6 flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" placeholder="Search categories..." value="{{ request('search') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 transition-all focus:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-200">
            </div>
            <button type="submit" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 shadow-sm transition-all hover:bg-slate-50">Search</button>
        </form>
        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/80">
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Name</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Slug</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">URL</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Parent</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">External ID</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Subs</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Last sync</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                        <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($categories as $cat)
                    <tr class="transition-colors hover:bg-slate-50/50">
                        <td class="px-5 py-4 text-sm font-medium text-slate-800">{{ $cat->name }}</td>
                        <td class="px-5 py-4 text-sm text-slate-600">{{ $cat->slug }}</td>
                        <td class="px-5 py-4 text-sm text-slate-500">{{ $cat->url ?? '—' }}</td>
                        <td class="px-5 py-4 text-sm text-slate-500">{{ $cat->parent?->name ?? '—' }}</td>
                        <td class="px-5 py-4 text-sm text-slate-500">{{ $cat->external_id ?? '—' }}</td>
                        <td class="px-5 py-4 text-sm text-slate-500">{{ $cat->subcategories_count }}</td>
                        <td class="px-5 py-4 text-sm text-slate-500">{{ $cat->last_synced_at?->format('M j, Y H:i') ?? '—' }}</td>
                        <td class="px-5 py-4">
                            @if($cat->is_active)
                                <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">Active</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.categories.edit', $cat) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-600 shadow-sm transition-all hover:bg-slate-50">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-sm text-slate-500">No categories yet. Sync from Woohoo or add one manually.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
            <div class="mt-6">{{ $categories->links() }}</div>
        @endif
    </div>
</div>
@endsection
