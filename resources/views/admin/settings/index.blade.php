@extends('admin.layout')
@section('title', 'Settings')
@section('page_title', 'Settings')

@section('content')
<div class="space-y-6">

    @if($groups->isEmpty())
        <div class="rounded-2xl border border-amber-200/80 bg-amber-50/90 px-6 py-5 text-sm text-amber-950 shadow-sm">
            <p class="font-semibold">No settings found</p>
            <p class="mt-2 text-amber-900/90">The database has no settings rows yet. Seed them so this page can show loyalty and other options.</p>
            <pre class="mt-4 overflow-x-auto rounded-lg bg-amber-950/5 px-4 py-3 font-mono text-xs text-amber-950">php artisan db:seed --class=SettingsSeeder</pre>
        </div>
    @else
    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        @method('PUT')

        @foreach($groups as $group => $settings)
            <section class="rounded-2xl border border-zinc-200/80 bg-white shadow-sm overflow-hidden">
                {{-- Group header --}}
                <div class="flex items-center gap-3 border-b border-zinc-100 px-6 py-4">
                    @if($group === 'loyalty')
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100">
                            <svg class="h-4 w-4 text-amber-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                    @else
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-100">
                            <svg class="h-4 w-4 text-zinc-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </div>
                    @endif
                    <div>
                        <h2 class="text-sm font-semibold text-zinc-900 capitalize">{{ ucfirst($group) }} Settings</h2>
                        @if($group === 'loyalty')
                            <p class="text-xs text-zinc-400 mt-0.5">Configure the PayFlex Loyalty Program rules</p>
                        @endif
                    </div>
                </div>

                {{-- Settings rows --}}
                <div class="divide-y divide-zinc-50">
                    @foreach($settings as $i => $setting)
                        <div class="grid grid-cols-1 gap-4 px-6 py-5 sm:grid-cols-3 sm:items-start">
                            {{-- Hidden key --}}
                            <input type="hidden" name="settings[{{ $loop->parent->index * 100 + $i }}][key]" value="{{ $setting->key }}">

                            {{-- Label + description --}}
                            <div class="sm:col-span-1">
                                <label for="s_{{ $setting->key }}" class="block text-sm font-semibold text-zinc-800">
                                    {{ $setting->label }}
                                </label>
                                @if($setting->description)
                                    <p class="mt-0.5 text-xs text-zinc-400 leading-relaxed">{{ $setting->description }}</p>
                                @endif
                                <span class="mt-1 inline-block text-[10px] font-mono text-zinc-300">{{ $setting->key }}</span>
                            </div>

                            {{-- Input --}}
                            <div class="sm:col-span-2">
                                @if($setting->type === 'boolean')
                                    <select
                                        id="s_{{ $setting->key }}"
                                        name="settings[{{ $loop->parent->index * 100 + $i }}][value]"
                                        class="rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-2.5 text-sm text-zinc-800 outline-none focus:border-[#3c8dbc] focus:ring-2 focus:ring-[#3c8dbc]/20 w-full max-w-xs"
                                    >
                                        <option value="1" @selected($setting->value == '1')>Enabled</option>
                                        <option value="0" @selected($setting->value == '0')>Disabled</option>
                                    </select>
                                @else
                                    <div class="flex items-center gap-2 max-w-xs">
                                        @if(in_array($setting->key, ['loyalty.default_rate', 'loyalty.min_redeem', 'loyalty.max_redeem_per_order']))
                                            <span class="text-sm text-zinc-400 font-medium shrink-0">
                                                {{ $setting->key === 'loyalty.default_rate' ? '%' : '₹' }}
                                            </span>
                                        @endif
                                        <input
                                            id="s_{{ $setting->key }}"
                                            type="{{ in_array($setting->type, ['integer','float']) ? 'number' : 'text' }}"
                                            @if($setting->type === 'float') step="0.001" @endif
                                            @if($setting->type === 'integer') step="1" @endif
                                            name="settings[{{ $loop->parent->index * 100 + $i }}][value]"
                                            value="{{ $setting->type === 'float' && $setting->key === 'loyalty.default_rate'
                                                ? number_format((float)$setting->value * 100, 2)
                                                : $setting->value }}"
                                            class="rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-2.5 text-sm text-zinc-800 outline-none focus:border-[#3c8dbc] focus:ring-2 focus:ring-[#3c8dbc]/20 w-full"
                                            @if($setting->key === 'loyalty.default_rate') min="0" max="100" placeholder="e.g. 1 (for 1%)" @endif
                                            @if($setting->key === 'loyalty.validity_days') min="1" max="365" @endif
                                            @if($setting->key === 'loyalty.max_redeem_per_order') min="0" @endif
                                        />
                                    </div>
                                    @if($setting->key === 'loyalty.default_rate')
                                        <p class="mt-1 text-xs text-zinc-400">Enter as percentage, e.g. <strong>1</strong> for 1%, <strong>2</strong> for 2%.</p>
                                    @endif
                                    @if($setting->key === 'loyalty.max_redeem_per_order')
                                        <p class="mt-1 text-xs text-zinc-400">Set to <strong>0</strong> to allow up to the full order total (only ₹1 minimum for PayU).</p>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        <div class="flex justify-end gap-3">
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-xl bg-zinc-900 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-zinc-700 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Save Settings
            </button>
        </div>
    </form>
    @endif

</div>
@endsection
