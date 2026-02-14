@extends('admin.layout')

@section('title', 'Change password')
@section('page_title', 'Change password')

@section('content')
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden max-w-xl">
    <div class="border-b border-slate-100 px-6 py-4">
        <h2 class="text-base font-semibold text-slate-800">Change password</h2>
    </div>
    <form method="POST" action="{{ route('admin.profile.password.update') }}" class="p-6">
        @csrf
        @method('PUT')
        <div class="space-y-6">
            <div>
                <label for="current_password" class="block text-sm font-medium text-slate-700 mb-1.5">Current password</label>
                <input type="password" id="current_password" name="current_password" required class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-slate-800 transition-all focus:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-200">
                @error('current_password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">New password</label>
                <input type="password" id="password" name="password" required minlength="8" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-slate-800 transition-all focus:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-200">
                @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1.5">Confirm new password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-slate-800 transition-all focus:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-200">
            </div>
        </div>
        <div class="mt-6 flex flex-wrap gap-3">
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-5 py-2.5 font-medium text-white shadow-sm transition-all hover:bg-slate-700">Change password</button>
            <a href="{{ route('admin.profile.edit') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 font-medium text-slate-600 shadow-sm transition-all hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
@endsection
