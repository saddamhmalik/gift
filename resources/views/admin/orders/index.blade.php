@extends('admin.layout')
@section('title', 'Orders')
@section('page_title', 'Orders')

@section('content')
<div class="space-y-6">

    {{-- Stats row --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7">
        @foreach([
            ['label'=>'Total',          'val'=>$stats['total'],          'color'=>'text-zinc-700',    'bg'=>'bg-zinc-50'],
            ['label'=>'Completed',      'val'=>$stats['completed'],      'color'=>'text-emerald-700', 'bg'=>'bg-emerald-50'],
            ['label'=>'Pending',        'val'=>$stats['pending'],        'color'=>'text-amber-700',   'bg'=>'bg-amber-50'],
            ['label'=>'Cancelled',      'val'=>$stats['cancelled'],      'color'=>'text-red-700',     'bg'=>'bg-red-50'],
            ['label'=>'Refunded',       'val'=>$stats['refunded'],       'color'=>'text-blue-700',    'bg'=>'bg-blue-50'],
            ['label'=>'Refund Pending', 'val'=>$stats['refund_pending'], 'color'=>'text-indigo-700',  'bg'=>'bg-indigo-50'],
            ['label'=>'Refund Failed',  'val'=>$stats['refund_failed'],  'color'=>'text-rose-700',    'bg'=>'bg-rose-50 ring-1 ring-rose-200'],
        ] as $s)
        <div class="rounded-xl border border-zinc-200/70 {{ $s['bg'] }} px-4 py-3 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ $s['label'] }}</p>
            <p class="mt-1 text-xl font-bold {{ $s['color'] }}">{{ number_format($s['val']) }}</p>
        </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-semibold text-zinc-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ $search }}"
                placeholder="Order ID, Woohoo refno, PayU ID, user email…"
                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-[#3c8dbc] focus:outline-none focus:ring-1 focus:ring-[#3c8dbc]">
        </div>
        <div>
            <label class="block text-xs font-semibold text-zinc-500 mb-1">Order Status</label>
            <select name="status" class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-[#3c8dbc] focus:outline-none">
                <option value="">All</option>
                <option value="pending"   {{ $status === 'pending'   ? 'selected' : '' }}>Pending</option>
                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-zinc-500 mb-1">Refund Status</label>
            <select name="refund" class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-[#3c8dbc] focus:outline-none">
                <option value="">All</option>
                <option value="needed"  {{ $refund === 'needed'  ? 'selected' : '' }}>Needs Refund</option>
                <option value="pending" {{ $refund === 'pending' ? 'selected' : '' }}>Refund Pending</option>
                <option value="failed"  {{ $refund === 'failed'  ? 'selected' : '' }}>Refund Failed</option>
            </select>
        </div>
        <button type="submit" class="rounded-lg bg-[#3c8dbc] px-4 py-2 text-sm font-semibold text-white hover:bg-[#357ca5] transition-colors">Filter</button>
        @if($search || $status || $refund)
            <a href="{{ route('admin.orders.index') }}" class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-600 hover:bg-zinc-50 transition-colors">Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-zinc-100 text-sm">
            <thead class="bg-zinc-50/80">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-400">#</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-400">Customer</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-400">Product</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-400">Amount</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-zinc-400">Status</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-zinc-400">Delivery</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-zinc-400">Refund</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-400">Date</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
            @forelse($orders as $order)
                @php
                    $statusBadge = match($order->status) {
                        'completed' => 'bg-emerald-100 text-emerald-800',
                        'cancelled' => 'bg-red-100 text-red-700',
                        default     => 'bg-amber-100 text-amber-800',
                    };
                    $deliveryBadge = match($order->delivery_status) {
                        'fulfilled' => 'bg-emerald-100 text-emerald-800',
                        'failed'    => 'bg-red-100 text-red-700',
                        default     => 'bg-zinc-100 text-zinc-600',
                    };
                    $refundBadge = match($order->refund_status) {
                        'refunded' => 'bg-blue-100 text-blue-800',
                        'pending'  => 'bg-indigo-100 text-indigo-800',
                        'failed'   => 'bg-rose-100 text-rose-800',
                        default    => '',
                    };
                @endphp
                <tr class="hover:bg-zinc-50/50 transition-colors">
                    <td class="px-5 py-3 font-mono text-xs text-zinc-500">{{ $order->id }}</td>
                    <td class="px-5 py-3">
                        @if($order->user)
                            <a href="{{ route('admin.users.show', $order->user) }}" class="font-medium text-zinc-800 hover:text-[#3c8dbc] transition-colors">
                                {{ $order->user->first_name ?: $order->user->name ?: '—' }}
                            </a>
                            <p class="text-xs text-zinc-400">{{ $order->user->email }}</p>
                        @else
                            <span class="text-zinc-400">Guest</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 max-w-[180px]">
                        <p class="truncate text-zinc-700">{{ $order->items->first()?->product?->name ?? '—' }}</p>
                        <p class="text-xs text-zinc-400">Qty: {{ $order->items->first()?->quantity ?? '—' }}</p>
                    </td>
                    <td class="px-5 py-3 text-right font-semibold text-zinc-800">
                        ₹{{ number_format($order->total_amount, 0) }}
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusBadge }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $deliveryBadge }}">
                            {{ ucfirst($order->delivery_status ?? 'Pending') }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        @if($order->refund_status)
                            <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $refundBadge }}">
                                {{ ucfirst($order->refund_status) }}
                            </span>
                        @else
                            <span class="text-xs text-zinc-300">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-xs text-zinc-500 whitespace-nowrap">
                        {{ $order->created_at->format('d M Y, H:i') }}
                    </td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('admin.orders.show', $order) }}"
                            class="rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition-colors shadow-sm">
                            View
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-5 py-12 text-center text-sm text-zinc-400">No orders found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        @if($orders->hasPages())
            <div class="border-t border-zinc-100 px-5 py-4">{{ $orders->links() }}</div>
        @endif
    </div>

</div>
@endsection
