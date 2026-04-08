<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login – {{ config('app.name') }}</title>
    @include('admin.partials.styles')
</head>
<body class="flex min-h-screen items-center justify-center bg-zinc-50/80 p-4 antialiased">
    <div class="w-full max-w-[400px]">
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-8 shadow-sm">
            <div class="mb-8 flex items-center gap-3">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-zinc-900 text-white">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                </div>
                <div>
                    <h1 class="text-xl font-semibold tracking-tight text-zinc-900">Admin</h1>
                    <p class="text-sm text-zinc-500">Sign in to {{ config('app.name') }}</p>
                </div>
            </div>
            @if($errors->any())
                <div class="mb-6 rounded-xl border border-red-200/80 bg-red-50/80 px-4 py-3 text-sm font-medium text-red-800">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('admin.login') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-zinc-700">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus class="w-full rounded-xl border border-zinc-200 bg-zinc-50/50 px-4 py-3 text-zinc-900 placeholder-zinc-400 transition-colors focus:border-zinc-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-zinc-200">
                </div>
                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-zinc-700">Password</label>
                    <input type="password" id="password" name="password" required class="w-full rounded-xl border border-zinc-200 bg-zinc-50/50 px-4 py-3 text-zinc-900 placeholder-zinc-400 transition-colors focus:border-zinc-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-zinc-200">
                </div>
                <button type="submit" class="w-full rounded-xl bg-zinc-900 px-4 py-3 text-sm font-medium text-white shadow-sm transition-colors hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-500 focus:ring-offset-2">Sign in</button>
            </form>
        </div>
    </div>
</body>
</html>
