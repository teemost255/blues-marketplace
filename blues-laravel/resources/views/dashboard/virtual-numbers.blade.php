@extends('layouts.dashboard')
@section('title', 'Virtual Numbers')
@section('page-title', 'Virtual Numbers')
@section('content')

@if(!$enabled)
<div class="flex flex-col items-center justify-center py-24 text-center">
    <div class="w-16 h-16 rounded-2xl bg-slate-700 flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
    </div>
    <h2 class="text-xl font-semibold text-white mb-2">Virtual Numbers Unavailable</h2>
    <p class="text-slate-400 max-w-sm">This feature is currently disabled. Please check back later.</p>
</div>
@elseif(!$server1Enabled)
<div class="flex flex-col items-center justify-center py-24 text-center">
    <div class="w-16 h-16 rounded-2xl bg-orange-900/30 flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
    </div>
    <h2 class="text-xl font-semibold text-white mb-2">Server 1 Unavailable</h2>
    <p class="text-slate-400 max-w-sm">Server 1 is currently offline. Please try <a href="{{ route('dashboard.virtual-numbers.server2') }}" class="text-brand hover:underline">Server 2</a> or check back later.</p>
</div>
@elseif(!$configured)
<div class="flex flex-col items-center justify-center py-24 text-center">
    <div class="w-16 h-16 rounded-2xl bg-yellow-900/40 flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    </div>
    <h2 class="text-xl font-semibold text-white mb-2">Setup Required</h2>
    <p class="text-slate-400 max-w-sm">The virtual number API hasn't been configured yet. Please contact support.</p>
</div>
@else

{{-- ── Hero Bar ──────────────────────────────────────────────────────────────── --}}
<div class="rounded-2xl bg-gradient-to-r from-[#0f172a] via-[#0c1a3a] to-[#0f172a] border border-slate-700/60 p-5 mb-6 flex flex-wrap items-center justify-between gap-4">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-brand/20 border border-brand/30 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
        </div>
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-bold text-white">Virtual Numbers</h2>
                <span class="bg-purple-700/50 text-purple-300 px-2 py-0.5 rounded text-[10px] font-bold tracking-wider">Server 1</span>
            </div>
            <p class="text-xs text-slate-400">Receive SMS verification codes instantly · Valid ~20 minutes</p>
        </div>
    </div>
    <div class="flex items-center gap-3 flex-wrap">
        <div class="flex items-center gap-2 px-4 py-2 bg-slate-800/80 border border-slate-700 rounded-xl">
            <svg class="w-4 h-4 text-green-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="4"/></svg>
            <span class="text-xs text-slate-400">Balance:</span>
            <span class="font-bold text-white text-sm" id="wallet-display">₦{{ number_format($wallet->balance, 2) }}</span>
        </div>
        <a href="{{ route('dashboard.wallet') }}" class="px-4 py-2 bg-brand hover:bg-brand-dark text-white rounded-xl text-xs font-bold transition-colors">
            + Top Up
        </a>
    </div>
</div>


{{-- ── Tabs ──────────────────────────────────────────────────────────────────── --}}
<div class="flex items-center gap-1 mb-6 bg-slate-800/60 border border-slate-700/60 rounded-xl p-1 w-fit">
    <button onclick="switchTab('browse')" id="tab-browse"
        class="tab-btn flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold bg-brand text-white transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
        Services
    </button>
    <button onclick="switchTab('active')" id="tab-active"
        class="tab-btn flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-slate-400 hover:text-white transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
        Active
        @if($activeOrders->count())
        <span id="active-badge" class="bg-red-500 text-white text-[10px] rounded-full px-1.5 py-0.5 leading-none font-bold">{{ $activeOrders->count() }}</span>
        @endif
    </button>
    <button onclick="switchTab('history')" id="tab-history"
        class="tab-btn flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-slate-400 hover:text-white transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        History
    </button>
</div>

{{-- ═══════════════════════════════════════════════════════════
     TAB: BROWSE SERVICES
════════════════════════════════════════════════════════════ --}}
<div id="pane-browse">

    {{-- Popular quick-picks --}}
    <div class="mb-5">
        <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-2.5">Popular Services</p>
        <div class="flex flex-wrap gap-2" id="popular-chips">
            @php $popular = ['whatsapp','telegram','google','instagram','facebook','tiktok','twitter','discord','snapchat','microsoft','amazon','netflix']; @endphp
            @foreach($popular as $svc)
            <button onclick="quickSearch('{{ $svc }}')"
                class="popular-chip flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 border border-slate-700 hover:border-brand/50 rounded-full text-xs font-medium text-slate-300 hover:text-white transition-all">
                <span class="text-base leading-none">{{ ['whatsapp'=>'💬','telegram'=>'✈️','google'=>'🔍','instagram'=>'📸','facebook'=>'👤','tiktok'=>'🎵','twitter'=>'🐦','discord'=>'🎮','snapchat'=>'👻','microsoft'=>'🪟','amazon'=>'📦','netflix'=>'🎬'][$svc] ?? '⚡' }}</span>
                {{ ucfirst($svc) }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- Search + Filters row --}}
    <div class="flex flex-wrap gap-2.5 mb-5 items-center">

        {{-- Search --}}
        <div class="relative flex-1 min-w-[200px]">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input id="svc-search" type="text" placeholder="Search service (e.g. WhatsApp, Google…)" oninput="handleSearchInput()"
                style="font-size:16px"
                class="w-full pl-9 pr-9 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl text-sm focus:outline-none focus:border-brand placeholder-slate-500">
            <button id="svc-search-clear" onclick="clearSearch()" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Country dropdown --}}
        <div class="relative">
            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
            <select id="country-select" onchange="loadServices()"
                class="appearance-none bg-slate-800 border border-slate-700 text-white rounded-xl pl-9 pr-8 py-2.5 text-sm focus:outline-none focus:border-brand min-w-[160px]">
                <option value="">All Countries</option>
            </select>
            <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>

        {{-- Sort --}}
        <div class="relative">
            <select id="svc-sort" onchange="applyFilter()"
                class="appearance-none bg-slate-800 border border-slate-700 text-white rounded-xl px-4 py-2.5 pr-8 text-sm focus:outline-none focus:border-brand">
                <option value="name">A – Z</option>
                <option value="price_asc">Cheapest first</option>
                <option value="price_desc">Most expensive</option>
            </select>
            <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>

        <span id="svc-count" class="text-xs text-slate-500 whitespace-nowrap"></span>
    </div>

    {{-- State / Loading --}}
    <div id="svc-state" class="flex flex-col items-center justify-center py-20 rounded-2xl bg-slate-800/30 border border-slate-700/40">
        <div class="w-10 h-10 border-[3px] border-brand border-t-transparent rounded-full animate-spin mb-4"></div>
        <p class="text-slate-400 text-sm">Loading services…</p>
    </div>

    {{-- Services grid (populated by JS) --}}
    <div id="svc-grid" class="hidden space-y-8"></div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     TAB: ACTIVE RENTALS
════════════════════════════════════════════════════════════ --}}
<div id="pane-active" class="hidden">
    @if($activeOrders->isEmpty())
    <div class="flex flex-col items-center justify-center py-24 bg-slate-800/30 rounded-2xl border border-slate-700/40">
        <div class="w-14 h-14 rounded-2xl bg-slate-700 flex items-center justify-center mb-4">
            <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
        </div>
        <p class="text-white font-semibold mb-1">No active rentals</p>
        <p class="text-slate-400 text-sm mb-4">Order a number to start receiving SMS codes.</p>
        <button onclick="switchTab('browse')" class="px-5 py-2 bg-brand hover:bg-brand-dark text-white rounded-xl text-sm font-bold transition-colors">Browse Services</button>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" id="active-orders-list">
        @foreach($activeOrders as $order)
        <div id="active-card-{{ $order->id }}"
            class="bg-slate-800 border border-slate-700 rounded-2xl p-5 flex flex-col gap-3.5 relative overflow-hidden"
            data-received-at="{{ $order->sms_received_at ? $order->sms_received_at->toIso8601String() : '' }}"
            data-created-at="{{ $order->created_at->toIso8601String() }}">

            {{-- Pulse indicator --}}
            <div class="absolute top-4 right-4 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                <span class="text-xs text-green-400 font-semibold">Live</span>
            </div>

            {{-- Service name --}}
            <div>
                <p class="font-bold text-white text-base capitalize pr-14">{{ $order->service }}</p>
                @if($order->country)
                <p class="text-xs text-slate-400 mt-0.5 uppercase tracking-wide">{{ $order->country }}</p>
                @endif
            </div>

            {{-- Phone number --}}
            <div class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                <span class="font-mono text-sm text-brand flex-1 select-all truncate" id="phone-{{ $order->id }}">{{ $order->phone_number ?? 'Assigning…' }}</span>
                <button onclick="copyText('phone-{{ $order->id }}', this)" class="p-1 rounded-lg text-slate-400 hover:text-brand transition-colors shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </button>
            </div>

            {{-- SMS Code — Waiting state (hidden once code arrives) --}}
            <div id="sms-wait-{{ $order->id }}" class="{{ $order->sms_code ? 'hidden' : '' }} bg-slate-900/60 border border-slate-700/50 rounded-xl px-4 py-3.5 flex items-center gap-3">
                <div class="flex gap-1 shrink-0">
                    <span class="w-2 h-2 rounded-full bg-slate-600 animate-bounce" style="animation-delay:0s"></span>
                    <span class="w-2 h-2 rounded-full bg-slate-600 animate-bounce" style="animation-delay:0.15s"></span>
                    <span class="w-2 h-2 rounded-full bg-slate-600 animate-bounce" style="animation-delay:0.3s"></span>
                </div>
                <div>
                    <p class="text-[10px] text-slate-500 uppercase tracking-wider leading-none mb-0.5">SMS Code</p>
                    <p class="text-sm text-slate-500">Waiting for SMS…</p>
                </div>
            </div>

            {{-- SMS Code — Received state (hidden until code arrives) --}}
            <div id="sms-code-wrap-{{ $order->id }}" class="{{ $order->sms_code ? '' : 'hidden' }} bg-green-950/60 border-2 border-green-500/50 rounded-xl px-4 py-3.5 flex items-center gap-3">
                <svg class="w-5 h-5 text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="flex-1 min-w-0">
                    <p class="text-[10px] text-green-400 uppercase tracking-wider leading-none mb-1 font-semibold">Code Received</p>
                    <p id="sms-code-{{ $order->id }}" class="font-mono font-extrabold text-2xl text-green-300 tracking-[0.3em] leading-tight select-all">{{ $order->sms_code ?? '' }}</p>
                </div>
                <button onclick="copyText('sms-code-{{ $order->id }}', this)"
                    class="p-2 rounded-xl bg-green-500/10 hover:bg-green-500/25 text-green-400 transition-colors shrink-0" title="Copy code">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </button>
            </div>

            {{-- Status + cost --}}
            <div class="flex items-center justify-between text-xs">
                <p id="poll-status-{{ $order->id }}" class="{{ $order->status === 'received' ? 'text-green-400' : 'text-slate-500' }}">
                    {{ $order->status === 'received' ? '✓ Code received!' : 'Checking every 3 s…' }}
                </p>
                <span class="text-slate-600">₦{{ number_format($order->cost, 2) }} · {{ $order->created_at->diffForHumans() }}</span>
            </div>

            {{-- 3-minute countdown (shown only after code is received) --}}
            <div id="countdown-wrap-{{ $order->id }}" class="{{ $order->status === 'received' ? '' : 'hidden' }} bg-green-900/20 border border-green-700/30 rounded-xl px-4 py-2.5 flex items-center gap-2">
                <svg class="w-4 h-4 text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-xs text-green-300 flex-1">Moving to history in <span id="countdown-{{ $order->id }}" class="font-bold font-mono">3:00</span></p>
            </div>

            {{-- Actions --}}
            <div class="flex gap-2 pt-1 border-t border-slate-700/50">
                <button onclick="checkSmsOnce({{ $order->id }}, this)"
                    class="flex-1 flex items-center justify-center gap-1.5 py-2 bg-brand/10 hover:bg-brand/20 text-brand border border-brand/20 rounded-lg text-sm font-semibold transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Check Now
                </button>
                {{-- Cancel button: hidden until 2 minutes have elapsed since order creation --}}
                <button id="cancel-btn-{{ $order->id }}"
                    onclick="cancelOrder({{ $order->id }}, this)"
                    class="hidden flex-1 flex items-center justify-center gap-1.5 py-2 bg-red-900/10 hover:bg-red-900/30 text-red-400 border border-red-700/20 rounded-lg text-sm font-semibold transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Cancel
                </button>
            </div>
        </div>
        @endforeach
    </div>
    <p class="text-xs text-slate-600 text-center mt-4">Codes are checked automatically every 3 seconds. Cancel button appears after 2 minutes.</p>
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════════
     TAB: HISTORY
════════════════════════════════════════════════════════════ --}}
<div id="pane-history" class="hidden">
    @if($historyOrders->isEmpty())
    <div class="flex flex-col items-center justify-center py-24 bg-slate-800/30 rounded-2xl border border-slate-700/40">
        <svg class="w-10 h-10 text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-slate-400 text-sm">No rental history yet.</p>
    </div>
    @else
    <div class="bg-slate-800 border border-slate-700 rounded-2xl overflow-hidden">
        <div class="table-scroll">
        <table class="w-full text-sm min-w-[600px]">
            <thead>
                <tr class="border-b border-slate-700 bg-slate-800/80">
                    <th class="px-5 py-3.5 text-left text-xs text-slate-400 font-semibold uppercase tracking-wider">Service</th>
                    <th class="px-5 py-3.5 text-left text-xs text-slate-400 font-semibold uppercase tracking-wider">Number</th>
                    <th class="px-5 py-3.5 text-left text-xs text-slate-400 font-semibold uppercase tracking-wider">Code</th>
                    <th class="px-5 py-3.5 text-left text-xs text-slate-400 font-semibold uppercase tracking-wider">Cost</th>
                    <th class="px-5 py-3.5 text-left text-xs text-slate-400 font-semibold uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3.5 text-left text-xs text-slate-400 font-semibold uppercase tracking-wider">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/40">
                @foreach($historyOrders as $order)
                <tr class="hover:bg-slate-700/20 transition-colors">
                    <td class="px-5 py-3.5">
                        <p class="font-semibold text-white capitalize">{{ $order->service }}</p>
                        @if($order->country)<p class="text-xs text-slate-500 uppercase mt-0.5">{{ $order->country }}</p>@endif
                    </td>
                    <td class="px-5 py-3.5 font-mono text-sm text-slate-300">{{ $order->phone_number ?? '—' }}</td>
                    <td class="px-5 py-3.5">
                        @if($order->sms_code)
                        <span class="font-mono font-bold text-green-400 tracking-widest">{{ $order->sms_code }}</span>
                        @else
                        <span class="text-slate-600">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-white font-medium">₦{{ number_format($order->cost, 2) }}</td>
                    <td class="px-5 py-3.5">
                        @php $badge = match($order->status) {
                            'completed' => 'bg-green-500/10 text-green-400 border-green-500/20',
                            'cancelled' => 'bg-slate-700/50 text-slate-400 border-slate-600/50',
                            default     => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
                        }; @endphp
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold border {{ $badge }}">{{ ucfirst($order->status) }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-xs text-slate-400 whitespace-nowrap">{{ $order->created_at->format('M d, Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════════
     RENT CONFIRMATION MODAL
════════════════════════════════════════════════════════════ --}}
<div id="rent-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="closeModal()"></div>
    <div class="relative w-full max-w-md bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl z-10 overflow-hidden">

        {{-- Modal header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-700/60">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-brand/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                </div>
                <div>
                    <p class="font-bold text-white">Confirm Order</p>
                    <p class="text-xs text-slate-400">Review before proceeding</p>
                </div>
            </div>
            <button onclick="closeModal()" class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700/50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Modal body --}}
        <div class="p-6">
            {{-- Service info card --}}
            <div class="bg-slate-800 rounded-xl p-4 mb-4 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-400">Service</span>
                    <span id="modal-svc-name" class="text-sm font-bold text-white text-right max-w-[200px] leading-snug"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-400">Country</span>
                    <span id="modal-country" class="text-sm text-white"></span>
                </div>
                <div class="pt-3 mt-1 border-t border-slate-700 flex items-center justify-between">
                    <span class="text-sm text-slate-400">You pay</span>
                    <span id="modal-price" class="text-2xl font-extrabold text-white"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-400">Your balance</span>
                    <span id="modal-balance" class="text-sm font-bold text-green-400"></span>
                </div>
            </div>

            <p id="modal-warn" class="hidden text-xs text-red-400 bg-red-900/20 border border-red-700/30 rounded-xl px-4 py-3 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Insufficient balance. Please top up your wallet first.
            </p>

            <form method="POST" action="{{ route('dashboard.virtual-numbers.order') }}" id="rent-form">
                @csrf
                <input type="hidden" name="provider"     id="f-provider">
                <input type="hidden" name="server"       id="f-server">
                <input type="hidden" name="service_id"   id="f-service-id">
                <input type="hidden" name="country"      id="f-country">
                <input type="hidden" name="price"        id="f-price">
                <input type="hidden" name="service_name" id="f-svc-name">

                <button type="submit" id="rent-confirm-btn"
                    class="w-full py-3.5 rounded-xl font-bold text-white text-sm flex items-center justify-center gap-2 transition-all"
                    style="background: linear-gradient(135deg, #0ea5e9, #6366f1)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Rent Number Now
                </button>
            </form>

            <p class="text-xs text-slate-600 text-center mt-3">Valid ~20 minutes · One SMS code included</p>
        </div>
    </div>
</div>

@endif

<style>
.tab-btn { transition: all 0.15s ease; }
.service-card {
    transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
}
.service-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.35);
    border-color: rgba(14,165,233,0.35);
}
.rent-btn {
    background: linear-gradient(135deg, #0ea5e9, #6366f1);
    transition: opacity 0.15s, transform 0.1s;
}
.rent-btn:hover { opacity: 0.88; }
.rent-btn:active { transform: scale(0.97); }
.popular-chip { transition: all 0.15s; }
.popular-chip.active { background: rgba(14,165,233,0.15); border-color: rgba(14,165,233,0.5); color: #38bdf8; }
</style>

<script>
const COUNTRIES_URL     = '/dashboard/virtual-numbers/api/countries';
const SERVICES_URL      = '/dashboard/virtual-numbers/api/services';
const currentServer   = '1';
const currentProvider = 'herosms';
const USD_TO_NGN     = {{ $usdToNgn }};
let allServices      = [];
let walletBalance    = {{ $wallet->balance }};
let pollInterval     = null;
let countriesCache   = {};
const COMM_TYPE      = '{{ $commissionType }}';
const COMM_VALUE     = {{ $commissionValue }};

// Service icon map (emoji)
const SERVICE_ICONS = {
    'whatsapp':  '💬', 'telegram':  '✈️', 'google':    '🔍', 'instagram': '📸',
    'facebook':  '👤', 'tiktok':    '🎵', 'twitter':   '🐦', 'discord':   '🎮',
    'snapchat':  '👻', 'microsoft': '🪟', 'amazon':    '📦', 'netflix':   '🎬',
    'viber':     '📳', 'wechat':    '💚', 'linkedin':  '💼', 'uber':      '🚗',
    'yandex':    '🔡', 'vkontakte': '🅥',  'mail.ru':   '📧', 'signal':    '🔒',
    'spotify':   '🎧', 'steam':     '🎮', 'paypal':    '💳', 'binance':   '🟡',
    'coinbase':  '🔵', 'bybit':     '🔷', 'kucoin':    '🔶', 'tinder':    '🔥',
    'bumble':    '🐝', 'airbnb':    '🏠', 'booking':   '🏨', 'ebay':      '🛒',
    'aliexpress':'🛍️', 'shopee':    '🛒', 'youtube':   '▶️', 'chatgpt':   '🤖',
    'apple':     '🍎', 'reddit':    '🤖', 'pinterest': '📌', 'twitch':    '🎙️',
};

function getServiceIcon(name) {
    const n = (name || '').toLowerCase();
    for (const [key, icon] of Object.entries(SERVICE_ICONS)) {
        if (n.includes(key)) return icon;
    }
    return '📱';
}

const SOCIAL_MEDIA_KEYWORDS = [
    'whatsapp','telegram','tiktok','instagram','facebook','twitter','snapchat',
    'discord','viber','wechat','signal','youtube','linkedin','pinterest',
    'threads','reddit','twitch','skype','imo','line','zalo','clubhouse',
    'tumblr','kik','vk','weibo','hike','bigo','likee','kwai','shein',
    'x.com','messenger','fbmessenger'
];
function isSocialMedia(name) {
    const n = (name || '').toLowerCase();
    return SOCIAL_MEDIA_KEYWORDS.some(k => n.includes(k));
}
function isWhatsApp(name) {
    return (name || '').toLowerCase().includes('whatsapp');
}


// ── Tab switching ──────────────────────────────────────────────────────────────
function switchTab(tab) {
    ['browse','active','history'].forEach(t => {
        document.getElementById('pane-' + t)?.classList.add('hidden');
        const btn = document.getElementById('tab-' + t);
        if (btn) {
            btn.classList.remove('bg-brand','text-white');
            btn.classList.add('text-slate-400');
        }
    });
    document.getElementById('pane-' + tab)?.classList.remove('hidden');
    const active = document.getElementById('tab-' + tab);
    if (active) {
        active.classList.add('bg-brand','text-white');
        active.classList.remove('text-slate-400');
    }
    if (tab === 'active') startPolling();
    else stopPolling();
}

// ── Quick search (popular chip click) ─────────────────────────────────────────
function quickSearch(term) {
    const input = document.getElementById('svc-search');
    const clearBtn = document.getElementById('svc-search-clear');
    input.value = term;
    clearBtn.classList.remove('hidden');

    // Highlight active chip
    document.querySelectorAll('.popular-chip').forEach(c => {
        c.classList.toggle('active', c.textContent.trim().toLowerCase() === term.toLowerCase());
    });

    if (allServices.length === 0) {
        loadServices();
    } else {
        applyFilter();
    }
}

// ── Load countries ─────────────────────────────────────────────────────────────
async function loadCountries() {
    showState('loading');
    try {
        const url  = COUNTRIES_URL + '?server=' + currentServer + '&provider=' + currentProvider;
        const res  = await fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        if (!res.ok) { showState('error', 'API error (' + res.status + ').'); return; }
        const data = await res.json();

        if (data.success && data.data?.length) {
            const sel = document.getElementById('country-select');
            sel.innerHTML = '<option value="">— All Countries —</option>';
            data.data.forEach(c => {
                const code = String(c.code);
                countriesCache[code] = { name: c.name, iso: c.iso || '' };
                const opt = document.createElement('option');
                opt.value = code;
                opt.textContent = flagEmoji(c.iso) + ' ' + c.name;
                sel.appendChild(opt);
            });
            showState('empty', 'Select a country above or leave blank to browse all services.');
        } else {
            showState('empty', data.message || 'No countries returned.');
        }
    } catch(e) {
        showState('error', 'Could not load countries. Check your connection.');
    }
}

function flagEmoji(iso) {
    if (!iso || iso.length !== 2) return '🌍';
    return iso.toUpperCase().split('').map(c => String.fromCodePoint(c.charCodeAt(0) - 65 + 0x1F1E6)).join('');
}

// ── Search helpers ─────────────────────────────────────────────────────────────
let searchDebounceTimer = null;

function handleSearchInput() {
    const q = document.getElementById('svc-search').value;
    document.getElementById('svc-search-clear').classList.toggle('hidden', q.length === 0);

    // Clear chip highlights if search was manually typed
    document.querySelectorAll('.popular-chip').forEach(c => c.classList.remove('active'));

    if (allServices.length === 0 && q.length >= 2) {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => loadServices(), 400);
        return;
    }
    applyFilter();
}

function clearSearch() {
    const input = document.getElementById('svc-search');
    input.value = '';
    document.getElementById('svc-search-clear').classList.add('hidden');
    document.querySelectorAll('.popular-chip').forEach(c => c.classList.remove('active'));
    applyFilter();
    input.focus();
}

// ── Country helpers ────────────────────────────────────────────────────────────
function isUSA(name) { const n=(name||'').toLowerCase(); return n.includes('usa')||n.includes('united states')||n==='us'; }
function isCanada(name) { const n=(name||'').toLowerCase(); return n.includes('canada')||n==='ca'; }
function findCountryCodeByPredicate(pred) { return Object.entries(countriesCache).find(([,info]) => pred(info.name))?.[0] || null; }

// ── Load services ──────────────────────────────────────────────────────────────
async function loadServices() {
    showState('loading');
    const country      = document.getElementById('country-select').value;
    const selectedName = country ? (countriesCache[country]?.name || '') : '';
    const displayLabel = country ? selectedName : 'All Countries';
    const usaSelected  = country && isUSA(selectedName);

    async function fetchForCode(code) {
        let url = SERVICES_URL + '?server=' + currentServer + '&provider=' + currentProvider;
        if (code) url += '&country=' + encodeURIComponent(code);
        const res = await fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        if (!res.ok) {
            if (res.status === 429) throw new Error('rate_limited');
            return null;
        }
        return await res.json();
    }

    function mapServices(data, label, code) {
        return (data?.success && Array.isArray(data.data)) ? data.data.map(s => ({
            serviceId:   String(s.serviceId ?? ''),
            name:        s.name ?? '',
            apiPrice:    s.cost_ngn != null ? parseFloat(s.cost_ngn) : parseFloat((s.cost ?? 0) * USD_TO_NGN),
            count:       parseInt(s.count ?? 0),
            country:     s.country_name || label,
            countryCode: s.country_code || code,
            _provider:   currentProvider,
        })) : [];
    }

    try {
        let primaryData = await fetchForCode(country);
        if (!primaryData) { showState('error', 'API error.'); return; }
        let services = mapServices(primaryData, displayLabel, country || '');

        if (usaSelected) {
            // Remove the cheapest USA WhatsApp entry, then add Canada WhatsApp under USA label
            try {
                const canadaCode = findCountryCodeByPredicate(isCanada);
                if (canadaCode && canadaCode !== country) {
                    const canadaData = await fetchForCode(canadaCode);
                    const canadaWa   = mapServices(canadaData, displayLabel, canadaCode)
                                          .filter(s => isWhatsApp(s.name));

                    // Drop the single cheapest USA WhatsApp to make room for the Canada replacement
                    const usaWaList = services.filter(s => isWhatsApp(s.name));
                    if (usaWaList.length > 0) {
                        const lowestPrice = Math.min(...usaWaList.map(s => s.apiPrice));
                        let dropped = false;
                        services = services.filter(s => {
                            if (!dropped && isWhatsApp(s.name) && s.apiPrice === lowestPrice) {
                                dropped = true;
                                return false;
                            }
                            return true;
                        });
                    }

                    // Add Canada WhatsApp entries relabelled under USA.
                    // IMPORTANT: keep Canada's original countryCode so the backend orders from Canada.
                    // Only the display label (country) changes — the user sees "USA" but we order a Canadian number.
                    canadaWa.forEach(s => {
                        services.push({ ...s, country: displayLabel });
                    });
                }
            } catch(e) {}
        } else if (country && isCanada(selectedName)) {
            // Canada shows its own services normally — no changes needed
        }

        if (services.length) { allServices = services; applyFilter(); }
        else { allServices = []; showState('empty', primaryData.message || 'No services available.'); }
    } catch(e) {
        if (e?.message === 'rate_limited') {
            showState('error', 'Too many requests. Please wait a moment and try again.');
        } else {
            showState('error', 'Could not load services. Please try again.');
        }
    }
}

// ── Filter + render ────────────────────────────────────────────────────────────
function applyFilter() {
    const q       = document.getElementById('svc-search').value.toLowerCase().trim();
    const sort    = document.getElementById('svc-sort').value;
    const country = document.getElementById('country-select').value;

    let list = allServices.filter(s => {
        if (!q) return true;
        return (s.name ?? '').toLowerCase().includes(q) || (s.country ?? '').toLowerCase().includes(q);
    });

    if (sort === 'price_asc')  list.sort((a,b) => (a.apiPrice||0) - (b.apiPrice||0));
    if (sort === 'price_desc') list.sort((a,b) => (b.apiPrice||0) - (a.apiPrice||0));
    if (sort === 'name')       list.sort((a,b) => (a.name||'').localeCompare(b.name||''));

    renderServices(list);
}

function renderServices(list) {
    const grid  = document.getElementById('svc-grid');
    const state = document.getElementById('svc-state');
    const count = document.getElementById('svc-count');

    if (!list.length) {
        grid.classList.add('hidden');
        state.classList.remove('hidden');
        state.innerHTML = `
            <svg class="w-10 h-10 text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <p class="text-slate-400 text-sm font-medium mb-1">No services found</p>
            <p class="text-slate-600 text-xs">Try a different search term or select another country</p>`;
        count.textContent = '';
        return;
    }

    state.classList.add('hidden');
    grid.classList.remove('hidden');
    count.textContent = list.length + ' service' + (list.length !== 1 ? 's' : '');

    // Group by country
    const grouped = {};
    list.forEach(s => {
        const key = s.country || 'Unknown';
        if (!grouped[key]) grouped[key] = { countryCode: s.countryCode, services: [] };
        grouped[key].services.push(s);
    });

    grid.innerHTML = Object.entries(grouped).map(([country, g]) => {
        const info  = countriesCache[g.countryCode] || {};
        const emoji = info.iso ? flagEmoji(info.iso) : '🌍';
        const cards = g.services.map(s => buildCard(s, country, emoji)).join('');
        return `
        <div>
            <div class="flex items-center gap-2.5 mb-4 pb-3 border-b border-slate-700/40">
                <span class="text-2xl leading-none">${emoji}</span>
                <h3 class="font-bold text-white text-base">${escHtml(country)}</h3>
                <span class="text-[10px] bg-slate-700 text-slate-300 px-2 py-0.5 rounded-full font-semibold uppercase tracking-wide">${g.services.length} service${g.services.length !== 1 ? 's' : ''}</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                ${cards}
            </div>
        </div>`;
    }).join('');
}

function calcCommission(price) {
    if (COMM_VALUE <= 0) return 0;
    return COMM_TYPE === 'percent' ? Math.round(price * COMM_VALUE / 100 * 100) / 100 : COMM_VALUE;
}

function buildCard(s) {
    const id         = s.serviceId ?? '';
    const name       = s.name ?? id;
    const apiPrice   = parseFloat(s.apiPrice ?? 0);
    const commission = calcCommission(apiPrice);
    const total      = Math.round((apiPrice + commission) * 100) / 100;
    const country    = s.country ?? '';
    const code       = s.countryCode ?? '';
    const icon       = getServiceIcon(name);
    const count      = s.count ?? 0;

    const priceDisplay = total > 0
        ? '₦' + total.toLocaleString('en-NG', { minimumFractionDigits: 0, maximumFractionDigits: 2 })
        : '<span class="text-green-400">Free</span>';

    const commissionBreakdown = commission > 0
        ? `<span class="text-[10px] text-slate-500 mt-0.5 block">₦${apiPrice.toLocaleString('en-NG',{minimumFractionDigits:0,maximumFractionDigits:2})} API + ₦${commission.toLocaleString('en-NG',{minimumFractionDigits:2,maximumFractionDigits:2})} fee</span>`
        : '';

    const stockBadge = count > 100
        ? `<span class="text-[10px] text-green-400 bg-green-500/10 border border-green-500/20 px-2 py-0.5 rounded-full font-semibold">${count > 9999 ? '9999+' : count} left</span>`
        : count > 0
        ? `<span class="text-[10px] text-yellow-400 bg-yellow-500/10 border border-yellow-500/20 px-2 py-0.5 rounded-full font-semibold">${count} left</span>`
        : `<span class="text-[10px] text-slate-500 bg-slate-700/50 px-2 py-0.5 rounded-full font-semibold">Low stock</span>`;

    return `
    <div class="service-card bg-slate-800 border border-slate-700/60 rounded-2xl p-4 flex flex-col gap-3 cursor-default">
        {{-- Top row: icon + name --}}
        <div class="flex items-start gap-3">
            <div class="w-11 h-11 rounded-xl bg-slate-700/60 border border-slate-600/40 flex items-center justify-center text-2xl leading-none shrink-0">
                ${icon}
            </div>
            <div class="min-w-0 flex-1 pt-0.5">
                <p class="font-bold text-white text-sm leading-tight truncate">${escHtml(name)}</p>
                ${stockBadge}
            </div>
        </div>
        {{-- Price --}}
        <div class="flex items-center justify-between pt-1 border-t border-slate-700/40">
            <div>
                <span class="text-lg font-extrabold text-white">${priceDisplay}</span>
                ${commissionBreakdown}
            </div>
            <button onclick="openModalFromData(this)"
                data-id="${escHtml(id)}"
                data-name="${escHtml(name)}"
                data-price="${apiPrice}"
                data-country="${escHtml(country)}"
                data-code="${escHtml(code)}"
                class="rent-btn px-4 py-2 rounded-xl text-white font-bold text-xs">
                Get Number
            </button>
        </div>
    </div>`;
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── State placeholder ──────────────────────────────────────────────────────────
function showState(type, msg) {
    const grid  = document.getElementById('svc-grid');
    const state = document.getElementById('svc-state');
    grid.classList.add('hidden');
    state.classList.remove('hidden');
    document.getElementById('svc-count').textContent = '';

    if (type === 'loading') {
        state.innerHTML = `<div class="w-10 h-10 border-[3px] border-brand border-t-transparent rounded-full animate-spin mb-4"></div><p class="text-slate-400 text-sm">Loading services…</p>`;
    } else if (type === 'empty') {
        state.innerHTML = `
            <svg class="w-12 h-12 text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064"/></svg>
            <p class="text-white font-semibold mb-1">Select a country</p>
            <p class="text-slate-400 text-sm">${escHtml(msg || 'Choose a country above to see available services.')}</p>`;
    } else {
        state.innerHTML = `
            <svg class="w-10 h-10 text-red-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <p class="text-red-400 text-sm font-semibold mb-2">${escHtml(msg || 'Error loading services.')}</p>
            <button onclick="loadServices()" class="px-4 py-1.5 bg-brand/10 hover:bg-brand/20 text-brand border border-brand/20 rounded-lg text-xs font-semibold transition-colors">Retry</button>`;
    }
}

// ── Modal ──────────────────────────────────────────────────────────────────────
function openModalFromData(btn) {
    openModal(btn.dataset.id, btn.dataset.name, parseFloat(btn.dataset.price), btn.dataset.country, btn.dataset.code);
}

function openModal(serviceId, serviceName, price, country, countryCode) {
    const commission = calcCommission(price);
    const total      = Math.round((price + commission) * 100) / 100;

    document.getElementById('modal-svc-name').textContent = serviceName;
    document.getElementById('modal-country').textContent  = country;
    document.getElementById('modal-price').textContent    = total > 0 ? '₦' + total.toLocaleString('en-NG', { minimumFractionDigits: 2 }) : 'Free';
    document.getElementById('modal-balance').textContent  = '₦' + walletBalance.toLocaleString('en-NG', { minimumFractionDigits: 2 });

    const warn = document.getElementById('modal-warn');
    const btn  = document.getElementById('rent-confirm-btn');
    if (total > walletBalance) {
        warn.classList.remove('hidden');
        btn.disabled = true;
        btn.classList.add('opacity-50','cursor-not-allowed');
    } else {
        warn.classList.add('hidden');
        btn.disabled = false;
        btn.classList.remove('opacity-50','cursor-not-allowed');
    }

    document.getElementById('f-provider').value   = currentProvider;
    document.getElementById('f-server').value     = currentServer;
    document.getElementById('f-service-id').value = serviceId;
    document.getElementById('f-country').value    = countryCode;
    document.getElementById('f-price').value      = price;
    document.getElementById('f-svc-name').value   = serviceName;

    const modal = document.getElementById('rent-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('rent-modal').classList.add('hidden');
    document.getElementById('rent-modal').classList.remove('flex');
    document.body.style.overflow = '';
}

document.getElementById('rent-form')?.addEventListener('submit', function() {
    const btn = document.getElementById('rent-confirm-btn');
    btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg> Processing…';
    btn.disabled = true;
});

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

// ── SMS ping sound ─────────────────────────────────────────────────────────────
function playPing() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        // Two-tone chime: a high note then a slightly lower follow note
        [[880, 0, 0.15], [1108, 0.18, 0.15]].forEach(([freq, start, dur]) => {
            const osc  = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'sine';
            osc.frequency.value = freq;
            gain.gain.setValueAtTime(0, ctx.currentTime + start);
            gain.gain.linearRampToValueAtTime(0.35, ctx.currentTime + start + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + start + dur);
            osc.start(ctx.currentTime + start);
            osc.stop(ctx.currentTime + start + dur + 0.05);
        });
    } catch(e) {}
}

// ── SMS polling ─────────────────────────────────────────────────────────────────
let activeOrderIds = [{{ $activeOrders->pluck('id')->join(', ') }}];
const countdownTimers = {}; // orderId → setInterval handle

function startCountdown(orderId, receivedAt) {
    if (countdownTimers[orderId]) return; // already running

    const wrap      = document.getElementById('countdown-wrap-' + orderId);
    const countEl   = document.getElementById('countdown-' + orderId);
    const statusEl  = document.getElementById('poll-status-' + orderId);
    const card      = document.getElementById('active-card-' + orderId);

    if (wrap) wrap.classList.remove('hidden');
    if (statusEl) {
        statusEl.textContent  = '✓ Code received!';
        statusEl.className    = 'text-green-400';
    }

    const endsAt = new Date(receivedAt).getTime() + 3 * 60 * 1000;

    countdownTimers[orderId] = setInterval(() => {
        const remaining = Math.max(0, endsAt - Date.now());
        const mins      = Math.floor(remaining / 60000);
        const secs      = Math.floor((remaining % 60000) / 1000);
        if (countEl) countEl.textContent = `${mins}:${secs.toString().padStart(2, '0')}`;

        if (remaining <= 0) {
            clearInterval(countdownTimers[orderId]);
            delete countdownTimers[orderId];
            if (card) { card.classList.add('opacity-40'); setTimeout(() => { card.remove(); updateActiveBadge(); }, 1500); }
        }
    }, 1000);
}

function dismissCard(orderId) {
    const card = document.getElementById('active-card-' + orderId);
    if (card) { card.classList.add('opacity-40'); setTimeout(() => { card.remove(); updateActiveBadge(); }, 1500); }
    if (countdownTimers[orderId]) { clearInterval(countdownTimers[orderId]); delete countdownTimers[orderId]; }
}

async function cancelOrder(orderId, btn) {
    if (!confirm('Cancel this rental? Your wallet balance will be refunded if no SMS was received.')) return;

    const orig    = btn?.innerHTML;
    const spinner = '<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg> Cancelling…';
    if (btn) { btn.innerHTML = spinner; btn.disabled = true; }

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
            || document.querySelector('input[name="_token"]')?.value || '';
        const res  = await fetch(`/dashboard/virtual-numbers/${orderId}/cancel`, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
        });
        const data = await res.json();

        if (data.success) {
            // Stop polling this order and remove the card from the DOM
            activeOrderIds = activeOrderIds.filter(id => id !== orderId);
            dismissCard(orderId);
        } else {
            if (btn) { btn.innerHTML = orig; btn.disabled = false; }
            const statusEl = document.getElementById('poll-status-' + orderId);
            if (statusEl) {
                statusEl.textContent = data.message || 'Could not cancel. Please try again.';
                statusEl.className   = 'text-red-400';
                setTimeout(() => {
                    statusEl.textContent = 'Checking every 3 s…';
                    statusEl.className   = 'text-slate-500';
                }, 4000);
            }
        }
    } catch (e) {
        console.warn('cancelOrder error', e);
        if (btn) { btn.innerHTML = orig; btn.disabled = false; }
    }
}

async function checkSmsOnce(orderId, btn) {
    const orig = btn?.innerHTML;
    if (btn) { btn.innerHTML = '<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg> Checking…'; btn.disabled = true; }
    try {
        const res  = await fetch(`/dashboard/virtual-numbers/${orderId}/sms`, { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        if (data.success) {
            const codeEl     = document.getElementById('sms-code-' + orderId);
            const waitEl     = document.getElementById('sms-wait-' + orderId);
            const codeWrapEl = document.getElementById('sms-code-wrap-' + orderId);
            const statusEl   = document.getElementById('poll-status-' + orderId);

            if (data.sms_code && codeEl) {
                // Only play sound + animate when the code is newly arriving (element was empty before)
                const isNew = !codeEl.textContent.trim();

                // Populate the code text
                codeEl.textContent = data.sms_code;

                // Swap visible states
                if (waitEl)     waitEl.classList.add('hidden');
                if (codeWrapEl) {
                    codeWrapEl.classList.remove('hidden');
                    if (isNew) {
                        // Flash the box green to draw attention
                        codeWrapEl.style.transition = 'background-color 0.3s';
                        codeWrapEl.style.backgroundColor = 'rgba(34,197,94,0.25)';
                        setTimeout(() => { codeWrapEl.style.backgroundColor = ''; }, 1200);
                        playPing();
                    }
                }

                // Update status label
                if (statusEl) {
                    statusEl.textContent  = '✓ Code received!';
                    statusEl.className    = 'text-green-400';
                }
            }

            if (data.status === 'received' && data.sms_received_at) {
                startCountdown(orderId, data.sms_received_at);
            }
            if (data.status === 'completed' || data.status === 'cancelled') {
                activeOrderIds = activeOrderIds.filter(id => id !== orderId);
                dismissCard(orderId);
            }
        }
    } catch(e) { console.warn('checkSmsOnce error for order', orderId, e); }
    finally { if (btn) { setTimeout(() => { btn.innerHTML = orig; btn.disabled = false; }, 3000); } }
}

function startPolling() {
    if (pollInterval || !activeOrderIds.length) return;
    // Immediate first check, then every 3 s
    activeOrderIds.forEach(id => checkSmsOnce(id, null));
    pollInterval = setInterval(() => { activeOrderIds.forEach(id => checkSmsOnce(id, null)); }, 3000);
}
function stopPolling() { if (pollInterval) { clearInterval(pollInterval); pollInterval = null; } }

function copyText(elementId, btn) {
    const el = document.getElementById(elementId);
    if (!el) return;
    const text = el.textContent.trim();
    if (text === '—' || text === 'Assigning…' || !text) return;
    const orig = btn.innerHTML;
    const check = '<svg class="w-3.5 h-3.5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
    function done() { btn.innerHTML = check; setTimeout(() => { btn.innerHTML = orig; }, 2000); }
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(done).catch(() => fallback(text, done));
    } else fallback(text, done);
}
function fallback(text, cb) {
    const ta = document.createElement('textarea');
    ta.value = text; ta.style.cssText = 'position:fixed;top:0;left:0;opacity:0;pointer-events:none;';
    document.body.appendChild(ta); ta.focus(); ta.select();
    try { document.execCommand('copy'); if (cb) cb(); } catch(e) {}
    document.body.removeChild(ta);
}

function updateActiveBadge() {
    const remaining = document.querySelectorAll('#active-orders-list > [id^="active-card-"]').length;
    const badge = document.getElementById('active-badge');
    if (badge) badge.textContent = remaining;
}

// ── Init ──────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    loadCountries();
    @if($activeOrders->count())
    setTimeout(() => switchTab('active'), 300);
    @endif

    // Resume countdowns for any cards already in 'received' state on page load
    document.querySelectorAll('[data-received-at]').forEach(card => {
        const receivedAt = card.dataset.receivedAt;
        if (!receivedAt) return;
        const orderId = card.id.replace('active-card-', '');
        const endsAt  = new Date(receivedAt).getTime() + 3 * 60 * 1000;
        if (Date.now() >= endsAt) {
            // Already past 3 mins — server will clean it up on next load, just hide it
            card.classList.add('hidden');
        } else {
            startCountdown(orderId, receivedAt);
        }
    });

    // Show the cancel button 2 minutes after order creation.
    // If the order is already ≥2 min old when the page loads, reveal it immediately.
    const TWO_MIN = 2 * 60 * 1000;
    document.querySelectorAll('[data-created-at]').forEach(card => {
        const createdAt = card.dataset.createdAt;
        if (!createdAt) return;
        const orderId   = card.id.replace('active-card-', '');
        const elapsed   = Date.now() - new Date(createdAt).getTime();
        const delay     = Math.max(0, TWO_MIN - elapsed);
        setTimeout(() => {
            const cancelBtn = document.getElementById('cancel-btn-' + orderId);
            if (cancelBtn) cancelBtn.classList.remove('hidden');
        }, delay);
    });
});
</script>
@endsection
