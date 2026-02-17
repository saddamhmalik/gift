@extends('admin.layout')

@section('title', 'Products')
@section('page_title', 'Products')

@section('content')
<div class="rounded-2xl border border-zinc-200/80 bg-white shadow-sm">
    <div class="flex flex-col gap-4 border-b border-zinc-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">Products</h2>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.products.index', request()->query()) }}" class="inline-flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-600 shadow-sm transition-colors hover:bg-zinc-50 hover:border-zinc-300" title="Refresh page">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Refresh
            </a>
            <form action="{{ route('admin.products.sync') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-emerald-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Sync product list
                </button>
            </form>
            <form action="{{ route('admin.products.sync-details') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 shadow-sm transition-colors hover:bg-zinc-50 hover:border-zinc-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Sync product details
                </button>
            </form>
        </div>
    </div>
    <div class="p-6">
        <form method="GET" class="mb-6 flex flex-wrap items-end gap-3">
            <div class="min-w-[200px] flex-1">
                <input type="text" name="search" placeholder="Search products..." value="{{ request('search') }}" class="w-full rounded-xl border border-zinc-200 bg-zinc-50/50 px-4 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-zinc-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-zinc-200">
            </div>
            <div class="min-w-[180px]">
                <select name="category" class="w-full rounded-xl border border-zinc-200 bg-zinc-50/50 px-4 py-2.5 text-sm text-zinc-900 transition-colors focus:border-zinc-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-zinc-200">
                    <option value="">All categories</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" {{ request('category') == (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50">Search</button>
        </form>
        <div class="overflow-x-auto rounded-xl border border-zinc-200/80">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-zinc-200 bg-zinc-50/80">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Name</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">SKU</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Category</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Price range</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Status</th>
                        <th class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse($products as $p)
                    <tr class="transition-colors hover:bg-zinc-50/50">
                        <td class="px-5 py-3.5 text-sm font-medium text-zinc-900">
                            <a href="{{ route('admin.products.show', $p) }}" class="text-zinc-900 hover:underline">{{ $p->name }}</a>
                        </td>
                        <td class="px-5 py-3.5 text-sm text-zinc-600">{{ $p->external_id ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-sm text-zinc-500">{{ $p->category?->name ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-sm text-zinc-500">
                            @if($p->min_price !== null || $p->max_price !== null)
                                {{ $p->min_price !== null ? number_format($p->min_price, 2) : '—' }} – {{ $p->max_price !== null ? number_format($p->max_price, 2) : '—' }} {{ $p->currency_code ?? '' }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            @if($p->is_active)
                                <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">Active</span>
                            @else
                                <span class="inline-flex rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-600">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('admin.products.show', $p) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-600 transition-colors hover:bg-zinc-50">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-sm text-zinc-500">No products yet. Sync categories first, then sync products from Woohoo.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="mt-6">{{ $products->links() }}</div>
        @endif
    </div>
</div>
@endsection
