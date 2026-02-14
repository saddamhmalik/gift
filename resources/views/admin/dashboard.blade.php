@extends('admin.layout')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 px-6 py-4">
            <h2 class="text-base font-semibold text-slate-800">Overview</h2>
        </div>
        <div class="p-6">
            <div class="flex flex-wrap gap-8">
                <div class="rounded-xl bg-slate-50 p-6 min-w-[180px]">
                    <div class="text-3xl font-bold text-slate-800">{{ $categoryCount }}</div>
                    <div class="mt-1 text-sm text-slate-500">Categories synced</div>
                </div>
            </div>
        </div>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 px-6 py-4">
            <h2 class="text-base font-semibold text-slate-800">Quick actions</h2>
        </div>
        <div class="p-6">
            <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-5 py-2.5 font-medium text-white shadow-sm transition-all hover:bg-slate-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                Manage categories
            </a>
        </div>
    </div>
</div>
@endsection
