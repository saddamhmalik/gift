<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') – {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-50/80 antialiased">
    <div class="flex min-h-screen">
        {{-- Sidebar (AdminLTE-inspired dark) --}}
        <aside class="fixed inset-y-0 left-0 z-30 flex w-64 flex-col bg-[#222d32] shadow-xl">
            <div class="flex h-16 shrink-0 items-center gap-3 border-b border-white/10 px-4">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#3c8dbc] text-white">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                </div>
                <span class="text-base font-semibold text-white">{{ config('app.name') }}</span>
            </div>
            <nav class="flex-1 overflow-y-auto py-4">
                <ul class="space-y-0.5 px-3">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 rounded-md border-l-4 border-transparent px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('admin.dashboard') ? 'border-[#3c8dbc] bg-white/10 text-white' : 'text-[#b8c7ce] hover:bg-white/5 hover:text-white' }}">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 rounded-md border-l-4 border-transparent px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('admin.categories.*') ? 'border-[#3c8dbc] bg-white/10 text-white' : 'text-[#b8c7ce] hover:bg-white/5 hover:text-white' }}">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            Categories
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 rounded-md border-l-4 border-transparent px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('admin.products.*') ? 'border-[#3c8dbc] bg-white/10 text-white' : 'text-[#b8c7ce] hover:bg-white/5 hover:text-white' }}">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8 4-8-4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            Products
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.tags.index') }}" class="flex items-center gap-3 rounded-md border-l-4 border-transparent px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('admin.tags.*') ? 'border-[#3c8dbc] bg-white/10 text-white' : 'text-[#b8c7ce] hover:bg-white/5 hover:text-white' }}">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            Tags
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 rounded-md border-l-4 border-transparent px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('admin.users.*') ? 'border-[#3c8dbc] bg-white/10 text-white' : 'text-[#b8c7ce] hover:bg-white/5 hover:text-white' }}">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Users
                        </a>
                    </li>
                    <li class="mt-4 border-t border-white/10 pt-4">
                        <a href="{{ url('/horizon') }}" target="_blank" rel="noopener" class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium text-[#b8c7ce] transition-colors hover:bg-white/5 hover:text-white">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            Horizon
                            <svg class="ml-auto h-4 w-4 shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="shrink-0 border-t border-white/10 p-4">
                @if(isset($admin))
                    <p class="truncate px-3 text-xs font-medium text-[#8aa4af]">{{ $admin->name }}</p>
                @endif
            </div>
        </aside>

        {{-- Main content --}}
        <div class="flex flex-1 flex-col pl-64">
            <header class="sticky top-0 z-20 flex h-[4.5rem] shrink-0 items-center justify-between gap-4 border-b border-zinc-200/60 bg-white/90 px-8 backdrop-blur-md">
                <h1 class="text-lg font-semibold tracking-tight text-zinc-900">@yield('page_title', 'Admin')</h1>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-500 transition-colors hover:bg-zinc-100 hover:text-zinc-800">Dashboard</a>
                    <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-500 transition-colors hover:bg-zinc-100 hover:text-zinc-800">Logout</button>
                    </form>
                </div>
            </header>

            <main class="flex-1 px-8 py-6">
                @if(session('success'))
                    <div class="mb-6 rounded-xl border border-emerald-200/80 bg-emerald-50/80 px-4 py-3 text-sm font-medium text-emerald-800 shadow-sm">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="mb-6 rounded-xl border border-red-200/80 bg-red-50/80 px-4 py-3 text-sm font-medium text-red-800 shadow-sm">{{ session('error') }}</div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
