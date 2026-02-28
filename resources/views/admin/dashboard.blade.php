@extends('admin.layout')
@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- ── KPI Cards ────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-4">

        {{-- Revenue this month --}}
        <div class="col-span-2 sm:col-span-2 rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Revenue this month</p>
                    <p class="mt-1 text-3xl font-bold tracking-tight text-zinc-900">₹{{ number_format($thisMonth, 0) }}</p>
                    <p class="mt-1 text-xs text-zinc-400">Total all-time: ₹{{ number_format($totalRevenue, 0) }}</p>
                </div>
                @if($revenueGrowth !== null)
                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold
                        {{ $revenueGrowth >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                        {{ $revenueGrowth >= 0 ? '↑' : '↓' }} {{ abs($revenueGrowth) }}% vs last month
                    </span>
                @endif
            </div>
        </div>

        {{-- Orders --}}
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Total orders</p>
            <p class="mt-1 text-3xl font-bold tracking-tight text-zinc-900">{{ number_format($orderCount) }}</p>
            <div class="mt-2 flex items-center gap-3 text-xs">
                <span class="font-medium text-emerald-600">{{ $completedOrders }} done</span>
                <span class="text-zinc-300">·</span>
                <span class="font-medium text-amber-500">{{ $pendingOrders }} pending</span>
            </div>
        </div>

        {{-- Users --}}
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Users</p>
            <p class="mt-1 text-3xl font-bold tracking-tight text-[#3c8dbc]">{{ number_format($userCount) }}</p>
            <div class="mt-2 flex items-center gap-3 text-xs text-zinc-400">
                <span>{{ number_format($productCount) }} products</span>
                <span class="text-zinc-300">·</span>
                <span>{{ number_format($categoryCount) }} categories</span>
            </div>
        </div>

    </div>

    {{-- ── Revenue & Orders combined line chart ────────────────────────── --}}
    <div class="rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-sm font-semibold text-zinc-900">Revenue &amp; Orders</h2>
                <p class="text-xs text-zinc-400 mt-0.5">Last 30 days</p>
            </div>
            <div class="flex items-center gap-4 text-xs text-zinc-400">
                <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 rounded-full bg-[#3c8dbc]"></span>Revenue (₹)</span>
                <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 rounded-full bg-amber-400"></span>Orders</span>
            </div>
        </div>
        <div class="relative h-64">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    {{-- ── Order status doughnut + New users bar ────────────────────────── --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Order status doughnut --}}
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-zinc-900">Order Status</h2>
            <p class="text-xs text-zinc-400 mt-0.5 mb-5">All time breakdown</p>
            <div class="relative h-52 flex items-center justify-center">
                <canvas id="statusChart"></canvas>
            </div>
            <div class="mt-4 grid grid-cols-3 gap-2 text-center text-xs">
                <div>
                    <div class="font-bold text-emerald-600 text-base">{{ $completedOrders }}</div>
                    <div class="text-zinc-400">Completed</div>
                </div>
                <div>
                    <div class="font-bold text-amber-500 text-base">{{ $pendingOrders }}</div>
                    <div class="text-zinc-400">Pending</div>
                </div>
                <div>
                    <div class="font-bold text-red-500 text-base">{{ $cancelledOrders }}</div>
                    <div class="text-zinc-400">Cancelled</div>
                </div>
            </div>
        </div>

        {{-- New users bar --}}
        <div class="lg:col-span-2 rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-sm font-semibold text-zinc-900">New Users</h2>
                    <p class="text-xs text-zinc-400 mt-0.5">Daily signups – last 30 days</p>
                </div>
            </div>
            <div class="relative h-52">
                <canvas id="usersChart"></canvas>
            </div>
        </div>

    </div>

    {{-- ── Top categories horizontal bar ───────────────────────────────── --}}
    @if($topCategories->isNotEmpty())
    <div class="rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-sm">
        <h2 class="text-sm font-semibold text-zinc-900 mb-5">Top Categories by Revenue</h2>
        <div class="relative h-56">
            <canvas id="categoriesChart"></canvas>
        </div>
    </div>
    @endif

    {{-- ── Recent orders table ──────────────────────────────────────────── --}}
    <div class="rounded-2xl border border-zinc-200/80 bg-white shadow-sm overflow-hidden">
        <div class="flex items-center justify-between border-b border-zinc-100 px-6 py-4">
            <h2 class="text-sm font-semibold text-zinc-900">Recent Orders</h2>
            <a href="{{ route('admin.users.index') }}" class="text-xs font-medium text-[#3c8dbc] hover:underline">View all users →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100 bg-zinc-50/60">
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-400">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-400">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-400">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-400">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-400">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-50">
                    @forelse($recentOrders as $order)
                    <tr class="hover:bg-zinc-50/60 transition-colors">
                        <td class="px-6 py-3.5 font-mono text-xs text-zinc-500">
                            #{{ $order->id }}
                            @if($order->order_token)
                                <span class="ml-1 text-zinc-300">{{ Str::limit($order->order_token, 8, '') }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-3.5">
                            @if($order->user)
                                <a href="{{ route('admin.users.show', $order->user) }}" class="font-medium text-zinc-800 hover:text-[#3c8dbc]">{{ $order->user->name }}</a>
                                <div class="text-xs text-zinc-400">{{ $order->user->email }}</div>
                            @else
                                <span class="text-zinc-400">{{ $order->billing_name ?? 'Guest' }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-3.5 font-semibold text-zinc-800">₹{{ number_format($order->total_amount, 0) }}</td>
                        <td class="px-6 py-3.5">
                            @php
                                $badge = match($order->status) {
                                    'completed' => 'bg-emerald-50 text-emerald-700',
                                    'pending'   => 'bg-amber-50 text-amber-700',
                                    default     => 'bg-red-50 text-red-600',
                                };
                            @endphp
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badge }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-xs text-zinc-400">{{ $order->created_at->format('d M Y, H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-sm text-zinc-400">No orders yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Quick actions ────────────────────────────────────────────────── --}}
    <section class="rounded-2xl border border-zinc-200/80 bg-white shadow-sm">
        <div class="border-b border-zinc-100 px-6 py-4">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">Quick actions</h2>
        </div>
        <div class="flex flex-wrap gap-3 p-6">
            <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center gap-2.5 rounded-xl bg-zinc-900 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-zinc-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                Manage Categories
            </a>
            <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-2.5 rounded-xl border border-zinc-200 bg-white px-5 py-2.5 text-sm font-medium text-zinc-700 shadow-sm transition-colors hover:border-zinc-300 hover:bg-zinc-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8 4-8-4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Manage Products
            </a>
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2.5 rounded-xl border border-zinc-200 bg-white px-5 py-2.5 text-sm font-medium text-zinc-700 shadow-sm transition-colors hover:border-zinc-300 hover:bg-zinc-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                View Users
            </a>
            <a href="{{ route('admin.settings.index') }}" class="inline-flex items-center gap-2.5 rounded-xl border border-zinc-200 bg-white px-5 py-2.5 text-sm font-medium text-zinc-700 shadow-sm transition-colors hover:border-zinc-300 hover:bg-zinc-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                Settings
            </a>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
(function () {
    'use strict';

    // ── shared palette ──────────────────────────────────────────────────
    const blue   = '#3c8dbc';
    const amber  = '#f59e0b';
    const emerald= '#10b981';
    const red    = '#ef4444';
    const zinc   = '#e4e4e7';

    Chart.defaults.font.family = '"Inter", system-ui, sans-serif';
    Chart.defaults.color       = '#a1a1aa';

    // ── Revenue & Orders line chart ─────────────────────────────────────
    const labels  = @json($revenueDays);
    const revenue = @json($revenueValues);
    const orders  = @json($ordersValues);

    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Revenue (₹)',
                    data: revenue,
                    borderColor: blue,
                    backgroundColor: blue + '18',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    yAxisID: 'yRevenue',
                },
                {
                    label: 'Orders',
                    data: orders,
                    borderColor: amber,
                    backgroundColor: amber + '18',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    yAxisID: 'yOrders',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { display: false } },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { maxTicksLimit: 8, font: { size: 11 } },
                },
                yRevenue: {
                    position: 'left',
                    grid: { color: '#f4f4f5' },
                    ticks: {
                        font: { size: 11 },
                        callback: v => '₹' + (v >= 1000 ? (v/1000).toFixed(0) + 'k' : v),
                    },
                },
                yOrders: {
                    position: 'right',
                    grid: { display: false },
                    ticks: { font: { size: 11 }, precision: 0 },
                },
            },
        },
    });

    // ── Order status doughnut ───────────────────────────────────────────
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'Pending', 'Cancelled'],
            datasets: [{
                data: [
                    {{ $completedOrders }},
                    {{ $pendingOrders }},
                    {{ $cancelledOrders }},
                ],
                backgroundColor: [emerald, amber, red],
                borderWidth: 0,
                hoverOffset: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.label}: ${ctx.formattedValue}`,
                    },
                },
            },
        },
    });

    // ── New users bar chart ─────────────────────────────────────────────
    new Chart(document.getElementById('usersChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'New Users',
                data: @json($usersValues),
                backgroundColor: blue + 'cc',
                borderRadius: 4,
                borderSkipped: false,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { maxTicksLimit: 8, font: { size: 11 } } },
                y: {
                    grid: { color: '#f4f4f5' },
                    ticks: { precision: 0, font: { size: 11 } },
                    beginAtZero: true,
                },
            },
        },
    });

    // ── Top categories horizontal bar ───────────────────────────────────
    const catCanvas = document.getElementById('categoriesChart');
    if (catCanvas) {
        const catLabels = @json($topCategories->pluck('name'));
        const catValues = @json($topCategories->pluck('revenue')->map(fn($v) => round($v, 0)));

        const catColors = [blue, emerald, amber, '#8b5cf6', '#f43f5e', '#06b6d4'];

        new Chart(catCanvas, {
            type: 'bar',
            data: {
                labels: catLabels,
                datasets: [{
                    label: 'Revenue (₹)',
                    data: catValues,
                    backgroundColor: catColors,
                    borderRadius: 6,
                    borderSkipped: false,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        grid: { color: '#f4f4f5' },
                        ticks: {
                            font: { size: 11 },
                            callback: v => '₹' + (v >= 1000 ? (v/1000).toFixed(0) + 'k' : v),
                        },
                        beginAtZero: true,
                    },
                    y: { grid: { display: false }, ticks: { font: { size: 12 } } },
                },
            },
        });
    }
})();
</script>
@endpush
