@extends('admin.layout')
@section('title', 'User – '.($user->first_name ?: $user->email))
@section('page_title', 'User Detail')

@section('content')
<div class="space-y-6">

    {{-- Back --}}
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900 transition-colors">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Back to Users
    </a>

    {{-- User Info Card --}}
    <div class="rounded-2xl border border-zinc-200/80 bg-white shadow-sm">
        <div class="border-b border-zinc-100 px-6 py-4">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">Profile</h2>
        </div>
        <div class="flex flex-wrap items-start gap-6 p-6">
            {{-- Avatar --}}
            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-[#3c8dbc]/10 text-2xl font-bold text-[#3c8dbc]">
                {{ strtoupper(substr($user->first_name ?: $user->name ?: $user->email, 0, 1)) }}
            </div>
            {{-- Details --}}
            <div class="grid flex-1 grid-cols-1 gap-x-10 gap-y-3 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Name</p>
                    <p class="mt-0.5 font-semibold text-zinc-900">
                        {{ trim($user->first_name.' '.$user->last_name) ?: ($user->name ?: '—') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Email</p>
                    <p class="mt-0.5 text-zinc-700">{{ $user->email }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Phone</p>
                    <p class="mt-0.5 text-zinc-700">{{ $user->phone ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Registered</p>
                    <p class="mt-0.5 text-zinc-700">{{ $user->created_at->format('d M Y, H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Email Verified</p>
                    <p class="mt-0.5">
                        @if($user->email_verified_at)
                            <span class="inline-flex items-center gap-1 text-emerald-600 font-medium text-sm">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                {{ $user->email_verified_at->format('d M Y') }}
                            </span>
                        @else
                            <span class="text-sm text-zinc-400">Not verified</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Sign-in Method</p>
                    <p class="mt-0.5 text-zinc-700">{{ $user->google_id ? 'Google' : 'Email/Password' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Order Stats --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm text-center">
            <p class="text-2xl font-bold tracking-tight text-zinc-900">{{ $stats['total_orders'] }}</p>
            <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-zinc-400">Total Orders</p>
        </div>
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm text-center">
            <p class="text-2xl font-bold tracking-tight text-emerald-600">{{ $stats['completed_orders'] }}</p>
            <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-zinc-400">Completed</p>
        </div>
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm text-center">
            <p class="text-2xl font-bold tracking-tight text-amber-500">{{ $stats['pending_orders'] }}</p>
            <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-zinc-400">Pending</p>
        </div>
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm text-center">
            <p class="text-2xl font-bold tracking-tight text-blue-600">₹{{ number_format($stats['total_spent'], 2) }}</p>
            <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-zinc-400">Total Spent</p>
        </div>
    </div>

    {{-- Orders Table --}}
    <div class="rounded-2xl border border-zinc-200/80 bg-white shadow-sm">
        <div class="border-b border-zinc-100 px-6 py-4">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">Orders & Payments</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100 bg-zinc-50/60">
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-400">Order Token</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-400">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-400">Payment TxnID</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-400">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-400">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-400">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-400">Woohoo Ref</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse($orders as $order)
                        <tr x-data="{ open: false }" class="hover:bg-zinc-50/50 transition-colors">
                            {{-- Collapsible row --}}
                            <td colspan="7" class="p-0">
                                {{-- Summary row --}}
                                <div class="grid grid-cols-7 px-6 py-4 cursor-pointer" onclick="this.closest('tr').querySelector('[data-details]').classList.toggle('hidden')">
                                    {{-- Token --}}
                                    <div class="col-span-1 font-mono text-xs text-zinc-600 truncate pr-3 my-auto">
                                        {{ $order->order_token ?? '—' }}
                                    </div>
                                    {{-- Status --}}
                                    <div class="col-span-1 my-auto">
                                        @php
                                            $statusColors = [
                                                'completed' => 'bg-emerald-100 text-emerald-700',
                                                'pending'   => 'bg-amber-100 text-amber-700',
                                                'cancelled' => 'bg-red-100 text-red-700',
                                            ];
                                            $color = $statusColors[$order->status] ?? 'bg-zinc-100 text-zinc-600';
                                        @endphp
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $color }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </div>
                                    {{-- TxnID --}}
                                    <div class="col-span-1 my-auto font-mono text-xs text-zinc-500 truncate pr-3">
                                        {{ $order->payment_txnid ?? '—' }}
                                    </div>
                                    {{-- Amount --}}
                                    <div class="col-span-1 my-auto font-semibold text-zinc-900">
                                        {{ $order->currency_code ?? 'INR' }} {{ number_format($order->total_amount, 2) }}
                                    </div>
                                    {{-- Items count --}}
                                    <div class="col-span-1 my-auto">
                                        <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-semibold text-zinc-700">
                                            {{ $order->items->count() }} item{{ $order->items->count() !== 1 ? 's' : '' }}
                                        </span>
                                    </div>
                                    {{-- Date --}}
                                    <div class="col-span-1 my-auto text-xs text-zinc-500">
                                        {{ $order->created_at->format('d M Y, H:i') }}
                                    </div>
                                    {{-- Woohoo ref --}}
                                    <div class="col-span-1 my-auto font-mono text-xs text-zinc-500 truncate">
                                        {{ $order->woohoo_refno ?? '—' }}
                                    </div>
                                </div>

                                {{-- Expandable items --}}
                                <div data-details class="hidden border-t border-zinc-100 bg-zinc-50/70 px-6 py-4">
                                    <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-zinc-400">Order Items</p>
                                    <div class="space-y-2">
                                        @foreach($order->items as $item)
                                            <div class="flex items-center gap-4 rounded-xl border border-zinc-200 bg-white px-4 py-3 shadow-sm">
                                                @if($item->product && ($item->product->image_url ?? $item->product->thumbnail_url))
                                                    <img src="{{ $item->product->image_url ?? $item->product->thumbnail_url }}" alt="" class="h-10 w-10 rounded-lg object-contain bg-zinc-50 p-1 shrink-0">
                                                @else
                                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-lg font-bold text-zinc-400">
                                                        {{ strtoupper(substr($item->product->name ?? 'P', 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-semibold text-zinc-900 truncate">{{ $item->product->name ?? '(Deleted product)' }}</p>
                                                    <p class="text-xs text-zinc-400">SKU: {{ $item->sku ?? '—' }} &middot; Qty: {{ $item->quantity }}</p>
                                                </div>
                                                <div class="text-right shrink-0">
                                                    <p class="font-semibold text-zinc-900">₹{{ number_format($item->unit_price * $item->quantity, 2) }}</p>
                                                    <p class="text-xs text-zinc-400">@ ₹{{ number_format($item->unit_price, 2) }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- Payment / Billing info --}}
                                    @if($order->billing_name || $order->billing_email || $order->billing_telephone)
                                        <p class="mb-2 mt-5 text-xs font-semibold uppercase tracking-wide text-zinc-400">Billing Info</p>
                                        <div class="flex flex-wrap gap-4 rounded-xl border border-zinc-200 bg-white px-5 py-4 text-sm shadow-sm">
                                            @if($order->billing_name)
                                                <div><span class="text-zinc-400">Name:</span> <span class="font-medium text-zinc-800">{{ $order->billing_name }}</span></div>
                                            @endif
                                            @if($order->billing_email)
                                                <div><span class="text-zinc-400">Email:</span> <span class="font-medium text-zinc-800">{{ $order->billing_email }}</span></div>
                                            @endif
                                            @if($order->billing_telephone)
                                                <div><span class="text-zinc-400">Phone:</span> <span class="font-medium text-zinc-800">{{ $order->billing_telephone }}</span></div>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Gift Card Details --}}
                                    @if($order->card_details_encrypted)
                                        @php
                                            try {
                                                $cardJson = \Illuminate\Support\Facades\Crypt::decryptString($order->card_details_encrypted);
                                                $cards = json_decode($cardJson, true) ?? [];
                                            } catch (\Throwable $e) {
                                                $cards = [];
                                            }
                                        @endphp
                                        @if(count($cards))
                                            <p class="mb-2 mt-5 text-xs font-semibold uppercase tracking-wide text-zinc-400">Gift Card Details</p>
                                            <div class="space-y-2">
                                                @foreach($cards as $card)
                                                    <div class="flex flex-wrap gap-4 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm">
                                                        @foreach($card as $key => $value)
                                                            @if($value)
                                                                <div>
                                                                    <span class="text-zinc-400 capitalize">{{ str_replace('_', ' ', $key) }}:</span>
                                                                    <span class="font-mono font-semibold text-zinc-800 ml-1">{{ $value }}</span>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endif

                                    {{-- Woohoo info --}}
                                    @if($order->woohoo_order_id || $order->delivery_status)
                                        <p class="mb-2 mt-5 text-xs font-semibold uppercase tracking-wide text-zinc-400">Woohoo / Delivery</p>
                                        <div class="flex flex-wrap gap-4 rounded-xl border border-zinc-200 bg-white px-5 py-4 text-sm shadow-sm">
                                            @if($order->woohoo_order_id)
                                                <div><span class="text-zinc-400">Woohoo Order ID:</span> <span class="font-mono font-medium text-zinc-800">{{ $order->woohoo_order_id }}</span></div>
                                            @endif
                                            @if($order->woohoo_refno)
                                                <div><span class="text-zinc-400">Ref No:</span> <span class="font-mono font-medium text-zinc-800">{{ $order->woohoo_refno }}</span></div>
                                            @endif
                                            @if($order->delivery_status)
                                                <div><span class="text-zinc-400">Delivery:</span>
                                                    <span class="font-medium {{ $order->delivery_status === 'success' ? 'text-emerald-600' : 'text-amber-600' }}">
                                                        {{ ucfirst($order->delivery_status) }}
                                                    </span>
                                                </div>
                                            @endif
                                            <div><span class="text-zinc-400">Woohoo Synced:</span>
                                                <span class="font-medium {{ $order->woohoo_sync ? 'text-emerald-600' : 'text-zinc-500' }}">
                                                    {{ $order->woohoo_sync ? 'Yes' : 'No' }}
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-zinc-400">
                                This user has no orders yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($orders->hasPages())
            <div class="border-t border-zinc-100 px-6 py-4">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
