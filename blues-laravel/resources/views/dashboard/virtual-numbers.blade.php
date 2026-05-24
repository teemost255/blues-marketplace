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
@elseif(!$configured)
<div class="flex flex-col items-center justify-center py-24 text-center">
    <div class="w-16 h-16 rounded-2xl bg-yellow-900/40 flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    </div>
    <h2 class="text-xl font-semibold text-white mb-2">Setup Required</h2>
    <p class="text-slate-400 max-w-sm">The virtual number API hasn't been configured yet. Please contact support.</p>
</div>
@else

@php
    $activeOrders    = $orders->filter(fn($o) => $o->status === 'active');
    $historyOrders   = $orders->filter(fn($o) => $o->status !== 'active');
@endphp

{{-- ── Top bar ────────────────────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-brand/20 flex items-center justify-center">
            <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
        </div>
        <div>
            <p class="font-bold text-white text-base">Virtual Numbers</p>
            <p class="text-xs text-slate-400">Receive SMS codes for any service worldwide</p>
        </div>
    </div>
    <div class="flex items-center gap-3 bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5">
        <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
        <span class="text-sm text-slate-400">Balance:</span>
        <span class="font-bold text-white" id="wallet-display">₦{{ number_format($wallet->balance, 2) }}</span>
        <a href="{{ route('dashboard.wallet') }}" class="text-xs text-brand hover:underline ml-1">Top up</a>
    </div>
</div>

{{-- ── Tabs ──────────────────────────────────────────────────────────────── --}}
<div class="flex items-center gap-1 border-b border-slate-700 mb-6">
    <button onclick="switchTab('browse')" id="tab-browse"
        class="tab-btn flex items-center gap-2 px-4 py-3 text-sm font-semibold border-b-2 border-brand text-brand transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
        Available Services
    </button>
    <button onclick="switchTab('active')" id="tab-active"
        class="tab-btn flex items-center gap-2 px-4 py-3 text-sm font-semibold border-b-2 border-transparent text-slate-400 hover:text-white transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
        Active Rentals
        @if($activeOrders->count())
        <span id="active-badge" class="bg-brand text-white text-xs rounded-full px-1.5 py-0.5 leading-none">{{ $activeOrders->count() }}</span>
        @endif
    </button>
    <button onclick="switchTab('history')" id="tab-history"
        class="tab-btn flex items-center gap-2 px-4 py-3 text-sm font-semibold border-b-2 border-transparent text-slate-400 hover:text-white transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Rental History
    </button>
</div>

{{-- ═══════════════════════════════════════════════════════════
     TAB: AVAILABLE SERVICES
════════════════════════════════════════════════════════════ --}}
<div id="pane-browse">

    {{-- Server selector + filters --}}
    <div class="flex flex-wrap gap-3 mb-5">
        {{-- Server tabs --}}
        <div class="flex gap-1 bg-slate-800 border border-slate-700 rounded-xl p-1">
            <button onclick="switchServer('server2')" id="stab-server2"
                class="stab px-3 py-1.5 rounded-lg text-xs font-semibold bg-brand text-white transition-colors">
                🌍 Global
            </button>
            <button onclick="switchServer('server1')" id="stab-server1"
                class="stab px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-400 hover:text-white transition-colors">
                🇷🇺 Server 1
            </button>
        </div>

        {{-- Search --}}
        <div class="relative flex-1 min-w-[180px]">
            <svg class="absolute left-3 top-2.5 w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input id="svc-search" type="text" placeholder="Search by service or country…" oninput="applyFilter()"
                class="w-full pl-9 pr-3 py-2 bg-slate-800 border border-slate-700 text-white rounded-xl text-sm focus:outline-none focus:border-brand placeholder-slate-500">
        </div>

        {{-- Country dropdown --}}
        <div class="relative">
            <select id="country-select" onchange="loadServices()"
                class="appearance-none bg-slate-800 border border-slate-700 text-white rounded-xl px-4 py-2 pr-8 text-sm focus:outline-none focus:border-brand">
                <option value="">All Countries</option>
            </select>
            <svg class="pointer-events-none absolute right-2.5 top-2.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>

        {{-- Sort --}}
        <div class="relative">
            <select id="svc-sort" onchange="applyFilter()"
                class="appearance-none bg-slate-800 border border-slate-700 text-white rounded-xl px-4 py-2 pr-8 text-sm focus:outline-none focus:border-brand">
                <option value="name">Sort: A–Z</option>
                <option value="price_asc">Price: Low–High</option>
                <option value="price_desc">Price: High–Low</option>
            </select>
            <svg class="pointer-events-none absolute right-2.5 top-2.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>

        <span id="svc-count" class="self-center text-xs text-slate-400 whitespace-nowrap"></span>
    </div>

    {{-- Services state --}}
    <div id="svc-state" class="flex flex-col items-center justify-center py-24 bg-slate-800/40 rounded-2xl border border-slate-700">
        <div class="w-10 h-10 border-4 border-brand border-t-transparent rounded-full animate-spin mb-4"></div>
        <p class="text-slate-400 text-sm">Loading services…</p>
    </div>

    {{-- Services grid (populated by JS) --}}
    <div id="svc-grid" class="hidden space-y-6"></div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     TAB: ACTIVE RENTALS
════════════════════════════════════════════════════════════ --}}
<div id="pane-active" class="hidden">
    @if($activeOrders->isEmpty())
    <div class="flex flex-col items-center justify-center py-24 bg-slate-800/40 rounded-2xl border border-slate-700">
        <svg class="w-10 h-10 text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
        <p class="text-white font-semibold mb-1">No active rentals</p>
        <p class="text-slate-400 text-sm">Order a number from Available Services to get started.</p>
        <button onclick="switchTab('browse')" class="mt-4 px-4 py-2 bg-brand text-white rounded-xl text-sm font-semibold">Browse Services</button>
    </div>
    @else
    <div class="space-y-3" id="active-orders-list">
        @foreach($activeOrders as $order)
        <div id="active-card-{{ $order->id }}"
            class="bg-slate-800 border border-slate-700 rounded-2xl p-5 flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
                    <p class="font-bold text-white capitalize truncate">{{ $order->service }}</p>
                    @if($order->country)
                    <span class="text-xs text-slate-400 uppercase">({{ $order->country }})</span>
                    @endif
                </div>
                <p class="font-mono text-lg text-brand select-all tracking-widest">{{ $order->phone_number ?? 'Assigning…' }}</p>
                <p class="text-xs text-slate-500 mt-1">Ordered {{ $order->created_at->diffForHumans() }} · ₦{{ number_format($order->cost, 2) }}</p>
            </div>

            {{-- SMS code display --}}
            <div class="flex flex-col items-center bg-slate-700/50 rounded-xl px-5 py-3 min-w-[140px]">
                <p class="text-xs text-slate-400 mb-1">SMS Code</p>
                <p id="sms-code-{{ $order->id }}" class="font-mono font-bold text-xl text-green-400 tracking-widest">
                    {{ $order->sms_code ?? '—' }}
                </p>
                <p id="poll-status-{{ $order->id }}" class="text-xs text-slate-500 mt-1">Auto-checking…</p>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col gap-2">
                <button onclick="checkSmsOnce({{ $order->id }}, this)"
                    class="flex items-center gap-1.5 px-4 py-2 bg-brand/10 hover:bg-brand/20 text-brand border border-brand/30 rounded-xl text-sm font-semibold transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Check Now
                </button>
                <form method="POST" action="{{ route('dashboard.virtual-numbers.cancel', $order->id) }}"
                    onsubmit="return confirm('Cancel this rental?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full flex items-center justify-center gap-1.5 px-4 py-2 bg-red-900/20 hover:bg-red-900/40 text-red-400 border border-red-700/30 rounded-xl text-sm font-semibold transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Cancel
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    <p class="text-xs text-slate-500 text-center mt-4">SMS codes are checked automatically every 10 seconds.</p>
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════════
     TAB: RENTAL HISTORY
════════════════════════════════════════════════════════════ --}}
<div id="pane-history" class="hidden">
    @if($historyOrders->isEmpty())
    <div class="flex flex-col items-center justify-center py-24 bg-slate-800/40 rounded-2xl border border-slate-700">
        <svg class="w-10 h-10 text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-slate-400 text-sm">No rental history yet.</p>
    </div>
    @else
    <div class="bg-slate-800 border border-slate-700 rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-700 text-left">
                    <th class="px-5 py-3 text-xs text-slate-400 font-medium">Service</th>
                    <th class="px-5 py-3 text-xs text-slate-400 font-medium">Number</th>
                    <th class="px-5 py-3 text-xs text-slate-400 font-medium">SMS Code</th>
                    <th class="px-5 py-3 text-xs text-slate-400 font-medium">Cost</th>
                    <th class="px-5 py-3 text-xs text-slate-400 font-medium">Status</th>
                    <th class="px-5 py-3 text-xs text-slate-400 font-medium">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/50">
                @foreach($historyOrders as $order)
                <tr class="hover:bg-slate-700/30 transition-colors">
                    <td class="px-5 py-3">
                        <p class="font-medium text-white capitalize">{{ $order->service }}</p>
                        @if($order->country)<p class="text-xs text-slate-500 uppercase">{{ $order->country }}</p>@endif
                    </td>
                    <td class="px-5 py-3 font-mono text-sm text-slate-300">{{ $order->phone_number ?? '—' }}</td>
                    <td class="px-5 py-3 font-mono font-bold text-green-400">{{ $order->sms_code ?? '—' }}</td>
                    <td class="px-5 py-3 text-white">₦{{ number_format($order->cost, 2) }}</td>
                    <td class="px-5 py-3">
                        @php $badge = match($order->status) {
                            'completed' => 'bg-green-900/50 text-green-300 border-green-700/50',
                            'cancelled' => 'bg-slate-700/50 text-slate-400 border-slate-600',
                            default     => 'bg-yellow-900/50 text-yellow-300 border-yellow-700/50',
                        }; @endphp
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs border {{ $badge }}">{{ ucfirst($order->status) }}</span>
                    </td>
                    <td class="px-5 py-3 text-xs text-slate-400">{{ $order->created_at->format('M d, H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════════
     CONFIRMATION MODAL
════════════════════════════════════════════════════════════ --}}
<div id="rent-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeModal()"></div>
    <div class="relative w-full max-w-sm bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl p-6 z-10">
        <button onclick="closeModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-xl bg-brand/20 flex items-center justify-center">
                <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            </div>
            <div>
                <p class="font-bold text-white text-base">Rent a Number</p>
                <p class="text-xs text-slate-400">Confirm your order below</p>
            </div>
        </div>

        <div class="bg-slate-800 rounded-xl p-4 mb-5 space-y-2.5">
            <div class="flex justify-between items-center">
                <span class="text-sm text-slate-400">Service</span>
                <span id="modal-svc-name" class="text-sm font-semibold text-white text-right max-w-[180px]"></span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-slate-400">Country</span>
                <span id="modal-country" class="text-sm text-white"></span>
            </div>
            <div class="border-t border-slate-700 pt-2.5 flex justify-between items-center">
                <span class="text-sm text-slate-400">Cost</span>
                <span id="modal-price" class="text-lg font-bold text-white"></span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-slate-400">Your balance</span>
                <span id="modal-balance" class="text-sm font-semibold text-green-400"></span>
            </div>
        </div>

        <p id="modal-warn" class="hidden text-xs text-red-400 bg-red-900/20 border border-red-700/30 rounded-lg p-2 mb-4">
            ⚠️ Insufficient balance. Please top up your wallet first.
        </p>

        <form method="POST" action="{{ route('dashboard.virtual-numbers.order') }}" id="rent-form">
            @csrf
            <input type="hidden" name="server"       id="f-server">
            <input type="hidden" name="service_id"   id="f-service-id">
            <input type="hidden" name="country"      id="f-country">
            <input type="hidden" name="price"        id="f-price">
            <input type="hidden" name="service_name" id="f-svc-name">

            <button type="submit" id="rent-confirm-btn"
                class="w-full py-3 rounded-xl font-bold text-white text-sm flex items-center justify-center gap-2 transition-all"
                style="background: linear-gradient(135deg, #3b82f6, #8b5cf6)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Rent Number
            </button>
        </form>

        <p class="text-xs text-slate-500 text-center mt-3">Valid for ~20 min to receive one SMS code</p>
    </div>
</div>

@endif

<style>
.service-card { transition: transform 0.15s, box-shadow 0.15s; }
.service-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.3); }
.rent-btn {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    transition: opacity 0.15s, transform 0.1s;
}
.rent-btn:hover { opacity: 0.9; transform: scale(1.02); }
.rent-btn:active { transform: scale(0.98); }
</style>

<script>
// ── State ─────────────────────────────────────────────────────────────────────
const COUNTRIES_URL  = '/dashboard/virtual-numbers/api/countries';
const SERVICES_URL   = '/dashboard/virtual-numbers/api/services';
let currentServer    = 'server2';
let allServices      = [];
let walletBalance    = {{ $wallet->balance }};
let pollInterval     = null;
let countriesCache   = {};  // code → { name, flag }
const COMM_TYPE      = '{{ $commissionType }}';
const COMM_VALUE     = {{ $commissionValue }};

// ── Tab switching ─────────────────────────────────────────────────────────────
function switchTab(tab) {
    ['browse','active','history'].forEach(t => {
        document.getElementById('pane-' + t)?.classList.add('hidden');
        const btn = document.getElementById('tab-' + t);
        if (btn) {
            btn.classList.remove('border-brand','text-brand');
            btn.classList.add('border-transparent','text-slate-400');
        }
    });
    document.getElementById('pane-' + tab)?.classList.remove('hidden');
    const active = document.getElementById('tab-' + tab);
    if (active) {
        active.classList.add('border-brand','text-brand');
        active.classList.remove('border-transparent','text-slate-400');
    }

    if (tab === 'active') startPolling();
    else stopPolling();
}

// ── Server tab switch ─────────────────────────────────────────────────────────
function switchServer(s) {
    currentServer = s;
    document.querySelectorAll('.stab').forEach(b => {
        b.classList.remove('bg-brand','text-white');
        b.classList.add('text-slate-400');
    });
    const active = document.getElementById('stab-' + s);
    active.classList.add('bg-brand','text-white');
    active.classList.remove('text-slate-400');

    const cWrap = document.getElementById('country-select');
    cWrap.innerHTML = '<option value="">All Countries</option>';

    if (s === 'server2') loadCountries();
    else loadServices();
}

// ── Load countries ────────────────────────────────────────────────────────────
async function loadCountries() {
    showState('loading');
    try {
        const res  = await fetch(COUNTRIES_URL + '?server=' + currentServer, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!res.ok) { showState('error', 'API error (' + res.status + ').'); return; }
        const data = await res.json();

        if (data.success && data.data?.length) {
            const sel = document.getElementById('country-select');
            sel.innerHTML = '<option value="">— Select a country —</option>';
            data.data.forEach(c => {
                const code = c.code;   // numeric e.g. 1
                const iso  = flagFromUrl(c.flag);
                countriesCache[code] = { name: c.name, iso };
                const opt  = document.createElement('option');
                opt.value  = code;
                opt.textContent = c.name;
                sel.appendChild(opt);
            });
            showState('empty', 'Select a country above to see available services.');
        } else {
            showState('empty', data.message || 'No countries returned.');
        }
    } catch(e) {
        console.error('Countries error:', e);
        showState('error', 'Could not load countries. Check your connection.');
    }
}

// Extract 2-letter ISO code from flagcdn URL e.g. "https://flagcdn.com/w80/ao.png" → "ao"
function flagFromUrl(url) {
    if (!url) return '';
    const m = url.match(/\/([a-z]{2})\.png$/);
    return m ? m[1] : '';
}

// Generate flag emoji from 2-letter ISO code
function flagEmoji(iso) {
    if (!iso || iso.length !== 2) return '🌍';
    return iso.toUpperCase().split('').map(c =>
        String.fromCodePoint(c.charCodeAt(0) - 65 + 0x1F1E6)
    ).join('');
}

// ── Load services ─────────────────────────────────────────────────────────────
async function loadServices() {
    showState('loading');
    document.getElementById('svc-search').value = '';

    const country = document.getElementById('country-select').value;
    const url = SERVICES_URL + '?server=' + currentServer + (country ? '&country=' + encodeURIComponent(country) : '');

    try {
        const res  = await fetch(url, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!res.ok) { showState('error', 'API error (' + res.status + ').'); return; }
        const data = await res.json();

        if (data.success && Array.isArray(data.data) && data.data.length) {
            allServices = data.data;
            applyFilter();
        } else {
            allServices = [];
            showState('empty', data.message || 'No services available for this country.');
        }
    } catch(e) {
        console.error('Services error:', e);
        showState('error', 'Could not load services. Please try again.');
    }
}

// ── Filter + render ───────────────────────────────────────────────────────────
function applyFilter() {
    const q    = document.getElementById('svc-search').value.toLowerCase().trim();
    const sort = document.getElementById('svc-sort').value;

    let list = allServices.filter(s => {
        if (!q) return true;
        const name    = (s.name ?? '').toLowerCase();
        const country = (s.country ?? '').toLowerCase();
        return name.includes(q) || country.includes(q);
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
            <p class="text-slate-400 text-sm">No services match your search.</p>`;
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
        const code  = g.countryCode;
        const info  = countriesCache[code] || {};
        const emoji = info.iso ? flagEmoji(info.iso) : '🌍';
        const cards = g.services.map(s => buildCard(s, country, emoji)).join('');
        return `
        <div>
            <div class="flex items-center gap-3 mb-3">
                <span class="text-xl">${emoji}</span>
                <h3 class="font-bold text-white text-base">${escHtml(country)}</h3>
                <span class="text-xs bg-brand/20 text-brand px-2 py-0.5 rounded-full font-semibold">${g.services.length} Service${g.services.length !== 1 ? 's' : ''}</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                ${cards}
            </div>
        </div>`;
    }).join('');
}

function buildCard(s, countryName, emoji) {
    const id      = s.serviceId ?? '';
    const name    = s.name ?? id;
    const price   = parseFloat(s.apiPrice ?? 0);
    const country = s.country ?? countryName;
    const code    = s.countryCode ?? '';

    // Deterministic popularity from service name hash
    const pop = ((id.split('').reduce((a,c) => a + c.charCodeAt(0), 0)) % 180) + 10;

    return `
    <div class="service-card bg-[#131929] border border-slate-700/60 rounded-2xl p-4 flex flex-col gap-3">
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
                <p class="font-bold text-[#7b8cde] text-base truncate">${escHtml(name)}</p>
                <div class="flex items-center gap-1 mt-0.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    <span class="text-xs text-slate-400">${pop}</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-1.5 text-xs text-slate-400">
            <span>${emoji}</span>
            <span>${escHtml(country)}</span>
        </div>
        <div>
            <span class="inline-block bg-brand/20 text-brand font-bold text-sm px-3 py-1 rounded-lg">
                NGN ${price > 0 ? price.toLocaleString('en-NG', {minimumFractionDigits: 0, maximumFractionDigits: 2}) : 'Free'}
            </span>
        </div>
        <button onclick="openModalFromData(this)"
            data-id="${escHtml(id)}"
            data-name="${escHtml(name)}"
            data-price="${price}"
            data-country="${escHtml(country)}"
            data-code="${escHtml(code)}"
            class="rent-btn w-full py-2.5 rounded-xl text-white font-bold text-sm flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Rent Number
        </button>
    </div>`;
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── State placeholder ─────────────────────────────────────────────────────────
function showState(type, msg) {
    const grid  = document.getElementById('svc-grid');
    const state = document.getElementById('svc-state');
    grid.classList.add('hidden');
    state.classList.remove('hidden');
    document.getElementById('svc-count').textContent = '';

    if (type === 'loading') {
        state.innerHTML = `<div class="w-10 h-10 border-4 border-brand border-t-transparent rounded-full animate-spin mb-4"></div><p class="text-slate-400 text-sm">Loading services…</p>`;
    } else if (type === 'empty') {
        state.innerHTML = `<svg class="w-10 h-10 text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><p class="text-slate-400 text-sm">${escHtml(msg||'No services available.')}</p><button onclick="loadServices()" class="mt-3 text-xs text-brand hover:underline">Retry</button>`;
    } else {
        state.innerHTML = `<svg class="w-10 h-10 text-red-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg><p class="text-red-400 text-sm">${escHtml(msg||'Error loading services.')}</p><button onclick="loadServices()" class="mt-3 text-xs text-brand hover:underline">Retry</button>`;
    }
}

// ── Confirmation modal ────────────────────────────────────────────────────────
function openModalFromData(btn) {
    openModal(btn.dataset.id, btn.dataset.name, parseFloat(btn.dataset.price), btn.dataset.country, btn.dataset.code);
}

function calcCommission(price) {
    if (COMM_VALUE <= 0) return 0;
    return COMM_TYPE === 'percent' ? Math.round(price * COMM_VALUE / 100 * 100) / 100 : COMM_VALUE;
}

function openModal(serviceId, serviceName, price, country, countryCode) {
    const commission = calcCommission(price);
    const total      = Math.round((price + commission) * 100) / 100;

    document.getElementById('modal-svc-name').textContent = serviceName;
    document.getElementById('modal-country').textContent  = country;

    const priceEl = document.getElementById('modal-price');
    priceEl.textContent = total > 0 ? '₦' + total.toLocaleString('en-NG', { minimumFractionDigits: 2 }) : 'Free';

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
    const modal = document.getElementById('rent-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
}

document.getElementById('rent-form')?.addEventListener('submit', function () {
    const btn = document.getElementById('rent-confirm-btn');
    btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg> Processing…';
    btn.disabled = true;
});

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

// ── SMS polling ───────────────────────────────────────────────────────────────
const activeOrderIds = [{{ $activeOrders->pluck('id')->join(', ') }}];

async function checkSmsOnce(orderId, btn) {
    const orig = btn?.innerHTML;
    if (btn) { btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg> Checking…'; btn.disabled = true; }
    try {
        const res  = await fetch(`/dashboard/virtual-numbers/${orderId}/sms`, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        if (data.success) {
            const codeEl   = document.getElementById('sms-code-' + orderId);
            const statusEl = document.getElementById('poll-status-' + orderId);
            if (data.sms_code && codeEl) {
                codeEl.textContent = data.sms_code;
                codeEl.classList.add('animate-pulse');
                setTimeout(() => codeEl.classList.remove('animate-pulse'), 3000);
            }
            if (statusEl) {
                statusEl.textContent = data.sms_code ? '✓ Code received!' : 'Waiting for SMS…';
            }
            if (data.status === 'completed' || data.status === 'cancelled') {
                const card = document.getElementById('active-card-' + orderId);
                if (card) {
                    card.classList.add('opacity-50');
                    setTimeout(() => { card.remove(); updateActiveBadge(); }, 2000);
                }
            }
        }
    } catch(e) { console.error('SMS check error:', e); }
    finally {
        if (btn) { setTimeout(() => { btn.innerHTML = orig; btn.disabled = false; }, 3000); }
    }
}

function startPolling() {
    if (pollInterval || !activeOrderIds.length) return;
    // Run once immediately then every 10s
    activeOrderIds.forEach(id => checkSmsOnce(id, null));
    pollInterval = setInterval(() => {
        activeOrderIds.forEach(id => checkSmsOnce(id, null));
    }, 10000);
}

function stopPolling() {
    if (pollInterval) { clearInterval(pollInterval); pollInterval = null; }
}

function updateActiveBadge() {
    const remaining = document.querySelectorAll('#active-orders-list > [id^="active-card-"]').length;
    const badge = document.getElementById('active-badge');
    if (badge) badge.textContent = remaining;
}

// ── Init ──────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    loadCountries();

    // Auto-go to active tab if there are active orders and we just ordered
    @if(session('success') && $activeOrders->count())
    setTimeout(() => switchTab('active'), 300);
    @endif
});
</script>
@endsection
