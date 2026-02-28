@extends('admin.layout')
@section('title', 'Users')
@section('page_title', 'Users')

@section('content')
<div class="space-y-6">

    {{-- Stats --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Total Users</p>
            <p class="mt-1.5 text-3xl font-bold tracking-tight text-zinc-900">{{ number_format($totalUsers) }}</p>
        </div>
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">New This Month</p>
            <p class="mt-1.5 text-3xl font-bold tracking-tight text-emerald-600">{{ number_format($newThisMonth) }}</p>
        </div>
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">With Orders</p>
            <p class="mt-1.5 text-3xl font-bold tracking-tight text-blue-600">{{ number_format($withOrders) }}</p>
        </div>
    </div>

    {{-- Search + Table --}}
    <div class="rounded-2xl border border-zinc-200/80 bg-white shadow-sm">
        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-100 px-6 py-4">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">All Users</h2>
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex gap-2">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search name, email or phone…"
                    class="rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-2 text-sm text-zinc-800 outline-none placeholder:text-zinc-400 focus:border-[#3c8dbc] focus:ring-2 focus:ring-[#3c8dbc]/20 w-64"
                />
                <button type="submit" class="rounded-xl bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700 transition-colors">Search</button>
                @if($search)
                    <a href="{{ route('admin.users.index') }}" class="rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-600 hover:bg-zinc-50 transition-colors">Clear</a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100 bg-zinc-50/60">
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-400">User</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-400">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-400">Orders</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-400">Total Spent</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-400">Joined</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-400"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-zinc-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#3c8dbc]/10 text-sm font-bold text-[#3c8dbc]">
                                        {{ strtoupper(substr($user->first_name ?: $user->name ?: $user->email, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-zinc-900">{{ trim(($user->first_name.' '.$user->last_name) ?: $user->name) ?: '—' }}</p>
                                        <p class="text-xs text-zinc-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-zinc-600">{{ $user->phone ?: '—' }}</td>
                                <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-semibold text-zinc-700">
                                    {{ $user->orders_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-semibold text-zinc-900">
                                @if($user->orders_sum_total_amount)
                                    ₹{{ number_format($user->orders_sum_total_amount, 2) }}
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-zinc-500 text-xs">{{ $user->created_at->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.users.show', $user) }}"
                                   class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-700 hover:border-[#3c8dbc] hover:text-[#3c8dbc] transition-colors shadow-sm">
                                    View
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-zinc-400">
                                No users found{{ $search ? ' matching "'.$search.'"' : '' }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
            <div class="border-t border-zinc-100 px-6 py-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
