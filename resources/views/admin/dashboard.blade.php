@extends('admin.layout')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm">
            <div class="text-2xl font-bold tracking-tight text-zinc-900">{{ number_format($categoryCount) }}</div>
            <div class="mt-1 text-xs font-semibold uppercase tracking-wide text-zinc-400">Categories</div>
        </div>
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm">
            <div class="text-2xl font-bold tracking-tight text-zinc-900">{{ number_format($productCount) }}</div>
            <div class="mt-1 text-xs font-semibold uppercase tracking-wide text-zinc-400">Products</div>
        </div>
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm">
            <div class="text-2xl font-bold tracking-tight text-[#3c8dbc]">{{ number_format($userCount) }}</div>
            <div class="mt-1 text-xs font-semibold uppercase tracking-wide text-zinc-400">Users</div>
        </div>
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm">
            <div class="text-2xl font-bold tracking-tight text-zinc-900">{{ number_format($orderCount) }}</div>
            <div class="mt-1 text-xs font-semibold uppercase tracking-wide text-zinc-400">Total Orders</div>
        </div>
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm">
            <div class="text-2xl font-bold tracking-tight text-emerald-600">{{ number_format($completedOrders) }}</div>
            <div class="mt-1 text-xs font-semibold uppercase tracking-wide text-zinc-400">Completed</div>
        </div>
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm">
            <div class="text-2xl font-bold tracking-tight text-blue-600">₹{{ number_format($totalRevenue, 0) }}</div>
            <div class="mt-1 text-xs font-semibold uppercase tracking-wide text-zinc-400">Revenue</div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <section class="rounded-2xl border border-zinc-200/80 bg-white shadow-sm">
        <div class="border-b border-zinc-100 px-6 py-4">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">Quick actions</h2>
        </div>
        <div class="flex flex-wrap gap-3 p-6">
            <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center gap-2.5 rounded-xl bg-zinc-900 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-zinc-800">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                Manage categories
            </a>
            <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-2.5 rounded-xl border border-zinc-200 bg-white px-5 py-2.5 text-sm font-medium text-zinc-700 shadow-sm transition-colors hover:border-zinc-300 hover:bg-zinc-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8 4-8-4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Manage products
            </a>
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2.5 rounded-xl border border-zinc-200 bg-white px-5 py-2.5 text-sm font-medium text-zinc-700 shadow-sm transition-colors hover:border-zinc-300 hover:bg-zinc-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                View users
            </a>
        </div>
    </section>

</div>
@endsection
