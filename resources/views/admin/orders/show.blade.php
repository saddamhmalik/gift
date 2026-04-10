@extends('admin.layout')
@section('title', 'Order #'.$order->id)
@section('page_title', 'Order #'.$order->id)

@section('content')
@php
    $statusColor = match($order->status) {
        'completed' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
        'cancelled' => 'text-red-700 bg-red-50 border-red-200',
        default     => 'text-amber-700 bg-amber-50 border-amber-200',
    };
    $deliveryColor = match($order->delivery_status) {
        'fulfilled' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
        'failed'    => 'text-red-700 bg-red-50 border-red-200',
        default     => 'text-zinc-600 bg-zinc-50 border-zinc-200',
    };
    $refundColor = match($order->refund_status) {
        'refunded' => 'text-blue-700 bg-blue-50 border-blue-200',
        'pending'  => 'text-indigo-700 bg-indigo-50 border-indigo-200',
        'failed'   => 'text-rose-700 bg-rose-50 border-rose-200',
        default    => null,
    };
    $woohooResp = is_array($order->woohoo_response) ? $order->woohoo_response : [];
    $woohooReq  = is_array($order->woohoo_request)  ? $order->woohoo_request  : [];
    $item       = $order->items->first();
    $product    = $item?->product;
@endphp

<div class="space-y-6">

    {{-- Back --}}
    <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900 transition-colors">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Back to Orders
    </a>

    {{-- ── Status header ── --}}
    <div class="rounded-2xl border border-zinc-200/80 bg-white shadow-sm overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-100 px-6 py-4">
            <div>
                <p class="text-xs text-zinc-400 mb-1">Order</p>
                <h2 class="text-2xl font-bold tracking-tight text-zinc-900">#{{ $order->id }}</h2>
                <p class="text-xs text-zinc-400 mt-1">{{ $order->created_at->format('d M Y, H:i:s') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold {{ $statusColor }}">
                    Order: {{ ucfirst($order->status) }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold {{ $deliveryColor }}">
                    Delivery: {{ ucfirst($order->delivery_status ?? 'pending') }}
                </span>
                @if($refundColor)
                    <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold {{ $refundColor }}">
                        Refund: {{ ucfirst($order->refund_status) }}
                    </span>
                @endif
            </div>
        </div>

        {{-- ── Quick amounts ── --}}
        <div class="grid grid-cols-2 divide-x divide-zinc-100 sm:grid-cols-4">
            @foreach([
                ['label'=>'Total Amount',    'val'=>'₹'.number_format($order->total_amount, 2)],
                ['label'=>'Paid via PayU',   'val'=>$order->payu_paid_amount ? '₹'.number_format($order->payu_paid_amount, 2) : '—'],
                ['label'=>'Points Used',     'val'=>$order->points_used > 0 ? number_format($order->points_used, 0).' pts' : '—'],
                ['label'=>'Points Earned',   'val'=>$order->points_earned > 0 ? number_format($order->points_earned, 0).' pts' : '—'],
            ] as $f)
            <div class="px-5 py-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ $f['label'] }}</p>
                <p class="mt-1 text-lg font-bold text-zinc-900">{{ $f['val'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- ── Left column ── --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Product / Item --}}
            <div class="rounded-2xl border border-zinc-200/80 bg-white shadow-sm">
                <div class="border-b border-zinc-100 px-6 py-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">Order Item</h3>
                </div>
                <div class="p-6">
                    @if($item)
                        <div class="flex gap-4 items-start">
                            @if($product?->thumbnail_url)
                                <img src="{{ $product->thumbnail_url }}" alt="" class="h-16 w-16 rounded-xl object-cover border border-zinc-100 shrink-0">
                            @else
                                <div class="h-16 w-16 rounded-xl bg-zinc-100 flex items-center justify-center text-2xl shrink-0">🎁</div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-zinc-900">{{ $product?->name ?? 'Unknown product' }}</p>
                                @if($product?->external_id)
                                    <p class="text-xs text-zinc-400 mt-0.5 font-mono">SKU: {{ $product->external_id }}</p>
                                @endif
                                <div class="mt-3 grid grid-cols-2 gap-x-6 gap-y-2 sm:grid-cols-3 text-sm">
                                    <div>
                                        <p class="text-xs text-zinc-400">Quantity</p>
                                        <p class="font-semibold text-zinc-800">{{ $item->quantity }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-zinc-400">Unit Price</p>
                                        <p class="font-semibold text-zinc-800">₹{{ number_format($item->unit_price, 2) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-zinc-400">Line Total</p>
                                        <p class="font-semibold text-zinc-800">₹{{ number_format($item->unit_price * $item->quantity, 2) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-zinc-400">Order Mode</p>
                                        <p class="font-semibold text-zinc-800">{{ $order->order_mode ?? 'SELF' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-zinc-400">Delivery Mode</p>
                                        <p class="font-semibold text-zinc-800">{{ $order->delivery_mode ?? 'API' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-zinc-400">Currency</p>
                                        <p class="font-semibold text-zinc-800">{{ $order->currency_code ?? 'INR' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-zinc-400">No item on this order.</p>
                    @endif
                </div>
            </div>

            {{-- Gift Details --}}
            @if($order->order_mode === 'GIFT')
            <div class="rounded-2xl border border-violet-200/80 bg-violet-50/40 shadow-sm">
                <div class="border-b border-violet-200/40 px-6 py-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-violet-600">🎁 Gift Details</h3>
                </div>
                <div class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2">
                    @foreach([
                        ['Recipient Name',  $order->gift_recipient_name],
                        ['Recipient Email', $order->gift_recipient_email],
                        ['Recipient Phone', $order->gift_recipient_phone],
                    ] as [$label, $val])
                        <div>
                            <p class="text-xs text-zinc-400">{{ $label }}</p>
                            <p class="font-medium text-zinc-800">{{ $val ?: '—' }}</p>
                        </div>
                    @endforeach
                    @if($order->gift_message)
                        <div class="sm:col-span-2">
                            <p class="text-xs text-zinc-400 mb-1">Gift Message</p>
                            <p class="rounded-xl border border-violet-200 bg-white px-4 py-3 text-sm text-zinc-700 italic">&ldquo;{{ $order->gift_message }}&rdquo;</p>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Payment & Refund --}}
            <div class="rounded-2xl border border-zinc-200/80 bg-white shadow-sm">
                <div class="border-b border-zinc-100 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">Payment & Refund</h3>
                    {{-- Refund action button — only for non-successful, non-pending orders that have a PayU txn ID --}}
                    @if($order->refund_status === 'refunded')
                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">
                            ✓ Refunded
                        </span>
                    @elseif($order->status === 'completed' && $order->delivery_status === 'fulfilled')
                        {{-- Order fulfilled successfully — no refund applicable --}}
                        <span class="text-xs text-emerald-600 font-medium">✓ Order fulfilled — no refund needed</span>
                    @elseif(
                        $order->payu_mihpayid &&
                        in_array($order->status, ['cancelled', 'pending']) &&
                        $order->delivery_status !== 'fulfilled' &&
                        $order->refund_status !== 'pending'
                    )
                        <form method="POST" action="{{ route('admin.orders.refund', $order) }}"
                              onsubmit="return confirm('Initiate PayU refund of ₹{{ number_format($order->payu_paid_amount ?? $order->total_amount, 2) }} for order #{{ $order->id }}?')">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 transition-colors shadow-sm">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                {{ $order->refund_status === 'failed' ? 'Retry Refund' : 'Initiate Refund' }}
                            </button>
                        </form>
                    @elseif($order->refund_status === 'pending')
                        <span class="inline-flex items-center gap-1 rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-800">
                            ⏳ Refund in progress
                        </span>
                    @elseif(!$order->payu_mihpayid)
                        <span class="text-xs text-zinc-400">No PayU txn ID — cannot refund</span>
                    @endif
                </div>
                <div class="grid grid-cols-1 gap-x-8 gap-y-4 p-6 sm:grid-cols-2">
                    @foreach([
                        ['PayU Txn ID (txnid)',    $order->payment_txnid],
                        ['PayU Mihpayid',          $order->payu_mihpayid],
                        ['Paid Amount',            $order->payu_paid_amount ? '₹'.number_format($order->payu_paid_amount, 2) : null],
                        ['Refund Status',          $order->refund_status ? ucfirst($order->refund_status) : null],
                        ['Refund Reason',          $order->refund_reason],
                        ['Refunded At',            $order->refunded_at?->format('d M Y, H:i:s')],
                    ] as [$label, $val])
                        <div>
                            <p class="text-xs text-zinc-400">{{ $label }}</p>
                            <p class="mt-0.5 font-mono text-sm text-zinc-800 break-all">{{ $val ?: '—' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Woohoo Fulfillment --}}
            <div class="rounded-2xl border border-zinc-200/80 bg-white shadow-sm">
                <div class="border-b border-zinc-100 px-6 py-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">Woohoo Fulfillment</h3>
                </div>
                <div class="grid grid-cols-1 gap-x-8 gap-y-4 p-6 sm:grid-cols-2">
                    @foreach([
                        ['Refno',         $order->woohoo_refno],
                        ['Woohoo Order ID',$order->woohoo_order_id],
                        ['Sync Mode',     $order->woohoo_sync ? 'Synchronous (syncOnly=true)' : 'Async (syncOnly=false)'],
                        ['Delivery Status',$order->delivery_status ?? 'pending'],
                        ['Billing Email', $order->billing_email],
                        ['Billing Name',  $order->billing_name],
                    ] as [$label, $val])
                        <div>
                            <p class="text-xs text-zinc-400">{{ $label }}</p>
                            <p class="mt-0.5 font-mono text-sm text-zinc-800 break-all">{{ $val ?: '—' }}</p>
                        </div>
                    @endforeach
                </div>

                {{-- Woohoo error from response --}}
                @if(isset($woohooResp['fulfillment_error']))
                    @php $fe = $woohooResp['fulfillment_error']; @endphp
                    <div class="mx-6 mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-red-500 mb-1">Fulfillment Error</p>
                        <p class="text-sm font-medium text-red-800">{{ $fe['message'] ?? '—' }}</p>
                        <div class="mt-1.5 flex flex-wrap gap-4 text-xs text-red-600">
                            @if(!empty($fe['code']))   <span>Code: {{ $fe['code'] }}</span>   @endif
                            @if(!empty($fe['at']))     <span>At: {{ $fe['at'] }}</span>        @endif
                        </div>
                    </div>
                @endif

                {{-- Woohoo API Request payload --}}
                @if($woohooReq)
                    <details class="border-t border-zinc-100 px-6 py-4 group">
                        <summary class="cursor-pointer select-none text-xs font-semibold text-zinc-500 hover:text-zinc-800 transition-colors list-none flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            Raw Woohoo Request Payload
                        </summary>
                        <pre class="mt-3 overflow-x-auto rounded-xl bg-zinc-900 px-4 py-3 text-xs text-emerald-300 whitespace-pre-wrap">{{ json_encode($woohooReq, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </details>
                @endif

                {{-- Woohoo API Response --}}
                @if($woohooResp)
                    <details class="border-t border-zinc-100 px-6 py-4 group">
                        <summary class="cursor-pointer select-none text-xs font-semibold text-zinc-500 hover:text-zinc-800 transition-colors list-none flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            Raw Woohoo Response
                        </summary>
                        <pre class="mt-3 overflow-x-auto rounded-xl bg-zinc-900 px-4 py-3 text-xs text-emerald-300 whitespace-pre-wrap">{{ json_encode($woohooResp, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </details>
                @endif
            </div>

        </div>{{-- end left column --}}

        {{-- ── Right column ── --}}
        <div class="space-y-6">

            {{-- Customer --}}
            <div class="rounded-2xl border border-zinc-200/80 bg-white shadow-sm">
                <div class="border-b border-zinc-100 px-6 py-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">Customer</h3>
                </div>
                <div class="p-6 space-y-3">
                    @if($order->user)
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 shrink-0 rounded-full bg-[#3c8dbc]/10 flex items-center justify-center text-base font-bold text-[#3c8dbc]">
                                {{ strtoupper(substr($order->user->first_name ?: $order->user->email, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold text-zinc-900 truncate">{{ trim($order->user->first_name.' '.$order->user->last_name) ?: $order->user->name ?: '—' }}</p>
                                <p class="text-xs text-zinc-400 truncate">{{ $order->user->email }}</p>
                            </div>
                        </div>
                        @if($order->user->phone)
                            <p class="text-sm text-zinc-600">📞 {{ $order->user->phone }}</p>
                        @endif
                        <a href="{{ route('admin.users.show', $order->user) }}"
                            class="inline-flex items-center gap-1 text-xs font-semibold text-[#3c8dbc] hover:underline">
                            View customer profile →
                        </a>
                    @else
                        <p class="text-sm text-zinc-400">Guest order — no account linked</p>
                    @endif
                </div>
            </div>

            {{-- Timeline / Key IDs --}}
            <div class="rounded-2xl border border-zinc-200/80 bg-white shadow-sm">
                <div class="border-b border-zinc-100 px-6 py-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">Reference IDs</h3>
                </div>
                <div class="divide-y divide-zinc-50 px-6">
                    @foreach([
                        ['Order ID',          '#'.$order->id],
                        ['Order Token',       $order->order_token],
                        ['PayU Txnid',        $order->payment_txnid],
                        ['PayU Mihpayid',     $order->payu_mihpayid],
                        ['Woohoo Refno',      $order->woohoo_refno],
                        ['Woohoo Order ID',   $order->woohoo_order_id],
                    ] as [$label, $val])
                        <div class="py-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-zinc-400">{{ $label }}</p>
                            <p class="mt-0.5 font-mono text-xs text-zinc-700 break-all select-all">{{ $val ?: '—' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Loyalty --}}
            @php
                $loyaltyPoints = \App\Models\LoyaltyPoint::where('order_id', $order->id)->get();
            @endphp
            @if($loyaltyPoints->isNotEmpty())
            <div class="rounded-2xl border border-amber-200/60 bg-amber-50/50 shadow-sm">
                <div class="border-b border-amber-200/40 px-6 py-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-amber-700">⭐ Loyalty Points</h3>
                </div>
                <div class="divide-y divide-amber-100 px-6">
                    @foreach($loyaltyPoints as $lp)
                        <div class="flex items-center justify-between py-3">
                            <div>
                                <p class="text-xs font-semibold {{ $lp->type === 'credit' ? 'text-emerald-700' : 'text-red-600' }}">
                                    {{ ucfirst($lp->type) }}
                                </p>
                                <p class="text-[10px] text-zinc-400">{{ $lp->created_at->format('d M Y, H:i') }}</p>
                                @if($lp->expires_at)
                                    <p class="text-[10px] text-zinc-400">Expires {{ $lp->expires_at->format('d M Y') }}</p>
                                @endif
                            </div>
                            <p class="text-base font-bold {{ $lp->type === 'credit' ? 'text-emerald-600' : 'text-red-500' }}">
                                {{ $lp->type === 'credit' ? '+' : '−' }}{{ number_format($lp->points, 0) }} pts
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Billing Address --}}
            @if($order->billing_email || $order->billing_name)
            <div class="rounded-2xl border border-zinc-200/80 bg-white shadow-sm">
                <div class="border-b border-zinc-100 px-6 py-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">Billing Info</h3>
                </div>
                <div class="p-6 space-y-2 text-sm text-zinc-700">
                    @if($order->billing_name)     <p>{{ $order->billing_name }}</p>     @endif
                    @if($order->billing_email)    <p>{{ $order->billing_email }}</p>    @endif
                    @if($order->billing_telephone)<p>{{ $order->billing_telephone }}</p>@endif
                </div>
            </div>
            @endif

        </div>{{-- end right column --}}

    </div>{{-- end grid --}}

</div>
@endsection
