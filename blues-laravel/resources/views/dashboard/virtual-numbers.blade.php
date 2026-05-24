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

{{-- Top bar: wallet + balance --}}
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-lg font-semibold text-white">Get a Virtual Number</h2>
        <p class="text-sm text-slate-400 mt-0.5">Receive SMS codes for any service, charged from your wallet</p>
    </div>
    <div class="flex items-center gap-3 bg-slate-800 border border-slate-700 rounded-xl px-5 py-3">
        <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
        <div>
            <p class="text-xs text-slate-400">Wallet Balance</p>
            <p class="text-white font-bold" id="wallet-balance">₦{{ number_format($wallet->balance, 2) }}</p>
        </div>
        <a href="{{ route('dashboard.wallet') }}" class="ml-3 text-xs text-brand hover:text-sky-300">Top up →</a>
    </div>
</div>

{{-- Main grid --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    {{-- Left: Service Picker --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Server tabs --}}
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-1 flex gap-1">
            <button onclick="switchServer('server2')" id="tab-server2"
                class="server-tab flex-1 py-2 px-4 rounded-lg text-sm font-semibold transition-colors bg-brand text-white">
                🌍 Global (Server 2)
            </button>
            <button onclick="switchServer('server1')" id="tab-server1"
                class="server-tab flex-1 py-2 px-4 rounded-lg text-sm font-semibold transition-colors text-slate-400 hover:text-white">
                🇷🇺 Server 1
            </button>
        </div>

        {{-- Country selector (hidden for server1) --}}
        <div id="country-wrap" class="bg-slate-800 border border-slate-700 rounded-xl p-4">
            <label class="block text-xs text-slate-400 mb-2 font-semibold uppercase tracking-wider">Country</label>
            <div class="relative">
                <select id="country-select"
                    class="w-full bg-slate-700 border border-slate-600 text-white rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-brand appearance-none pr-8"
                    onchange="loadServices()">
                    <option value="">— Loading countries… —</option>
                </select>
                <svg class="pointer-events-none absolute right-2.5 top-3 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
        </div>

        {{-- Search + filter bar --}}
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-4">
            <div class="flex flex-wrap gap-3">
                <div class="relative flex-1 min-w-[180px]">
                    <svg class="absolute left-3 top-2.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" id="service-search" placeholder="Search services…"
                        oninput="filterServices()"
                        class="w-full pl-9 pr-3 py-2 bg-slate-700 border border-slate-600 text-white rounded-lg text-sm focus:outline-none focus:border-brand placeholder-slate-500">
                </div>
                <select id="price-filter" onchange="filterServices()"
                    class="bg-slate-700 border border-slate-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand">
                    <option value="">All prices</option>
                    <option value="50">Under ₦50</option>
                    <option value="100">Under ₦100</option>
                    <option value="250">Under ₦250</option>
                    <option value="500">Under ₦500</option>
                    <option value="1000">Under ₦1,000</option>
                </select>
                <select id="sort-filter" onchange="filterServices()"
                    class="bg-slate-700 border border-slate-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand">
                    <option value="name">Sort: A–Z</option>
                    <option value="price_asc">Price: Low–High</option>
                    <option value="price_desc">Price: High–Low</option>
                </select>
            </div>
            <p id="result-count" class="text-xs text-slate-500 mt-2"></p>
        </div>

        {{-- Service cards grid --}}
        <div id="services-state" class="flex flex-col items-center justify-center py-16 bg-slate-800 border border-slate-700 rounded-xl">
            <div class="w-10 h-10 border-4 border-brand border-t-transparent rounded-full animate-spin mb-4"></div>
            <p class="text-slate-400 text-sm">Loading services…</p>
        </div>

        <div id="services-grid" class="grid grid-cols-1 sm:grid-cols-2 gap-3 hidden">
            {{-- Populated by JS --}}
        </div>

    </div>

    {{-- Right: Order panel + How it works --}}
    <div class="space-y-4">

        {{-- Order panel --}}
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 sticky top-4">
            <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                Order Summary
            </h3>

            <div id="no-selection" class="text-center py-6">
                <svg class="w-8 h-8 text-slate-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <p class="text-slate-400 text-sm">Select a service from the list to order</p>
            </div>

            <div id="selection-panel" class="hidden">
                <div class="bg-slate-700/50 rounded-lg p-4 mb-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Service</span>
                        <span id="sel-name" class="text-white font-medium text-right max-w-[150px] truncate"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Country</span>
                        <span id="sel-country" class="text-white font-medium"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Cost</span>
                        <span id="sel-price" class="text-white font-bold text-base"></span>
                    </div>
                </div>

                <form method="POST" action="{{ route('dashboard.virtual-numbers.order') }}" id="order-form">
                    @csrf
                    <input type="hidden" name="server"       id="f-server"   value="server2">
                    <input type="hidden" name="service_id"   id="f-service"  value="">
                    <input type="hidden" name="country"      id="f-country"  value="">
                    <input type="hidden" name="price"        id="f-price"    value="0">
                    <input type="hidden" name="service_name" id="f-svcname"  value="">

                    <button type="submit" id="order-btn"
                        class="w-full py-2.5 bg-brand hover:bg-brand-dark text-white font-semibold rounded-lg text-sm transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        Get Virtual Number
                    </button>
                </form>

                <p class="text-xs text-slate-500 text-center mt-3">Charged from your wallet. Valid for ~20 min.</p>
            </div>
        </div>

        {{-- How it works --}}
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
            <h3 class="font-semibold text-white mb-4">How it works</h3>
            <ol class="space-y-4">
                @foreach([
                    ['1', 'Select service', 'Pick the app and country you need a number for.'],
                    ['2', 'Get number', 'A virtual number is instantly assigned from your wallet.'],
                    ['3', 'Receive SMS', 'Enter the number in the app, then click "Check SMS".'],
                    ['4', 'Done', 'Cancel unused numbers within 2 mins for a refund.'],
                ] as [$n, $t, $d])
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-brand text-white text-xs font-bold flex items-center justify-center">{{ $n }}</span>
                    <div>
                        <p class="text-sm font-medium text-white">{{ $t }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $d }}</p>
                    </div>
                </li>
                @endforeach
            </ol>
        </div>
    </div>
</div>

{{-- Orders Table --}}
<div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
        <h3 class="font-semibold text-white">My Orders</h3>
        <span class="text-xs text-slate-400">{{ $orders->total() }} total</span>
    </div>

    @if($orders->isEmpty())
    <div class="text-center py-16">
        <svg class="w-10 h-10 text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
        <p class="text-slate-400 text-sm">No orders yet. Get your first virtual number above.</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-700 text-left">
                    <th class="px-6 py-3 text-xs text-slate-400 font-medium">#</th>
                    <th class="px-6 py-3 text-xs text-slate-400 font-medium">Service</th>
                    <th class="px-6 py-3 text-xs text-slate-400 font-medium">Number</th>
                    <th class="px-6 py-3 text-xs text-slate-400 font-medium">SMS Code</th>
                    <th class="px-6 py-3 text-xs text-slate-400 font-medium">Cost</th>
                    <th class="px-6 py-3 text-xs text-slate-400 font-medium">Status</th>
                    <th class="px-6 py-3 text-xs text-slate-400 font-medium">Date</th>
                    <th class="px-6 py-3 text-xs text-slate-400 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/50">
                @foreach($orders as $order)
                <tr class="hover:bg-slate-700/30 transition-colors" id="order-row-{{ $order->id }}">
                    <td class="px-6 py-4 text-slate-400 font-mono text-xs">#{{ $order->id }}</td>
                    <td class="px-6 py-4">
                        <span class="font-medium text-white capitalize">{{ $order->service }}</span>
                        @if($order->country)
                            <span class="text-slate-400 text-xs ml-1 uppercase">({{ $order->country }})</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($order->phone_number)
                            <span class="font-mono text-white select-all">{{ $order->phone_number }}</span>
                        @else
                            <span class="text-slate-500">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span id="sms-{{ $order->id }}" class="font-mono font-bold text-green-400">
                            {{ $order->sms_code ?? '—' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-white">₦{{ number_format($order->cost, 2) }}</td>
                    <td class="px-6 py-4">
                        @php
                            $badge = match($order->status) {
                                'active'    => 'bg-blue-900/50 text-blue-300 border-blue-700/50',
                                'completed' => 'bg-green-900/50 text-green-300 border-green-700/50',
                                'cancelled' => 'bg-slate-700/50 text-slate-400 border-slate-600/50',
                                'failed'    => 'bg-red-900/50 text-red-300 border-red-700/50',
                                default     => 'bg-yellow-900/50 text-yellow-300 border-yellow-700/50',
                            };
                        @endphp
                        <span id="status-{{ $order->id }}" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs border {{ $badge }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-slate-400 text-xs">{{ $order->created_at->format('M d, H:i') }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2" id="actions-{{ $order->id }}">
                            @if($order->status === 'active')
                                <button onclick="checkSms({{ $order->id }}, this)"
                                    class="text-xs px-2.5 py-1 bg-brand/10 hover:bg-brand/20 text-brand border border-brand/30 rounded-lg transition-colors">
                                    Check SMS
                                </button>
                                <form method="POST" action="{{ route('dashboard.virtual-numbers.cancel', $order->id) }}"
                                    onsubmit="return confirm('Cancel this order? Refunds are only issued if no SMS was received within 2 minutes.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-xs px-2.5 py-1 bg-red-900/20 hover:bg-red-900/40 text-red-400 border border-red-700/30 rounded-lg transition-colors">
                                        Cancel
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
    <div class="px-6 py-4 border-t border-slate-700">
        {{ $orders->links() }}
    </div>
    @endif
    @endif
</div>

@endif

<script>
const COUNTRIES_URL = '{{ route("dashboard.virtual-numbers.countries") }}';
const SERVICES_URL  = '{{ route("dashboard.virtual-numbers.services") }}';

let currentServer   = 'server2';
let allServices     = [];
let selectedService = null;

function fmtNGN(v) {
    return '₦' + parseFloat(v || 0).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// ── Server tab switch ────────────────────────────────────────────────────────
function switchServer(s) {
    currentServer = s;

    document.querySelectorAll('.server-tab').forEach(t => {
        t.classList.remove('bg-brand', 'text-white');
        t.classList.add('text-slate-400');
    });
    const active = document.getElementById('tab-' + s);
    active.classList.add('bg-brand', 'text-white');
    active.classList.remove('text-slate-400');

    document.getElementById('country-wrap').style.display = (s === 'server2') ? '' : 'none';

    if (s === 'server1') {
        loadServices();
    } else {
        loadCountries();
    }
}

// ── Load countries ───────────────────────────────────────────────────────────
async function loadCountries() {
    const sel = document.getElementById('country-select');
    sel.innerHTML = '<option value="">Loading…</option>';
    showServiceState('loading');
    resetSelection();

    try {
        const res  = await fetch(COUNTRIES_URL + '?server=' + currentServer, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();

        if (data.success && data.data.length) {
            sel.innerHTML = data.data.map(c => {
                const id   = c.id ?? c.code ?? '';
                const name = c.name ?? id;
                return `<option value="${id}">${name}</option>`;
            }).join('');
            loadServices();
        } else {
            sel.innerHTML = '<option value="">No countries available</option>';
            showServiceState('empty', data.message || 'No countries found.');
        }
    } catch (e) {
        showServiceState('error', 'Failed to load countries. Check your connection.');
    }
}

// ── Load services ─────────────────────────────────────────────────────────────
async function loadServices() {
    showServiceState('loading');
    resetSelection();
    document.getElementById('service-search').value = '';
    document.getElementById('price-filter').value    = '';

    const country = document.getElementById('country-select')?.value ?? '';
    const url     = SERVICES_URL + '?server=' + currentServer + (country ? '&country=' + encodeURIComponent(country) : '');

    try {
        const res  = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();

        if (data.success && data.data.length) {
            allServices = data.data;
            renderServices(allServices);
        } else {
            allServices = [];
            showServiceState('empty', data.message || 'No services available for this selection.');
        }
    } catch (e) {
        showServiceState('error', 'Failed to load services. Please try again.');
    }
}

// ── Filter services ───────────────────────────────────────────────────────────
function filterServices() {
    const q      = document.getElementById('service-search').value.toLowerCase().trim();
    const maxP   = parseFloat(document.getElementById('price-filter').value) || Infinity;
    const sort   = document.getElementById('sort-filter').value;

    let filtered = allServices.filter(s => {
        const name  = (s.name ?? s.serviceId ?? '').toLowerCase();
        const price = parseFloat(s.apiPrice ?? 0);
        return (!q || name.includes(q)) && price <= maxP;
    });

    if (sort === 'price_asc')  filtered.sort((a, b) => (a.apiPrice ?? 0) - (b.apiPrice ?? 0));
    if (sort === 'price_desc') filtered.sort((a, b) => (b.apiPrice ?? 0) - (a.apiPrice ?? 0));
    if (sort === 'name')       filtered.sort((a, b) => (a.name ?? '').localeCompare(b.name ?? ''));

    renderServices(filtered);
}

// ── Render service cards ──────────────────────────────────────────────────────
function renderServices(list) {
    const grid  = document.getElementById('services-grid');
    const state = document.getElementById('services-state');
    const count = document.getElementById('result-count');

    if (!list.length) {
        state.innerHTML = `
            <svg class="w-10 h-10 text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <p class="text-slate-400 text-sm">No services match your search.</p>`;
        state.classList.remove('hidden');
        grid.classList.add('hidden');
        count.textContent = '';
        return;
    }

    count.textContent = list.length + ' service' + (list.length !== 1 ? 's' : '') + ' found';
    state.classList.add('hidden');
    grid.classList.remove('hidden');

    grid.innerHTML = list.map(s => {
        const id    = s.serviceId ?? s.id ?? '';
        const name  = s.name ?? id;
        const price = parseFloat(s.apiPrice ?? 0);
        const isSelected = selectedService && selectedService.id === id;

        return `<button type="button" onclick="selectService('${id}', ${JSON.stringify(name).replace(/'/g, "\\'")} , ${price})"
            id="card-${id}"
            class="service-card text-left p-4 rounded-xl border transition-all ${isSelected
                ? 'border-brand bg-brand/10 ring-2 ring-brand/40'
                : 'border-slate-700 bg-slate-800 hover:border-slate-500 hover:bg-slate-700/50'}">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="font-semibold text-white text-sm truncate">${escHtml(name)}</p>
                    <p class="text-xs text-slate-500 mt-0.5 font-mono uppercase">${escHtml(id)}</p>
                </div>
                <span class="flex-shrink-0 text-sm font-bold ${price > 0 ? 'text-brand' : 'text-green-400'} whitespace-nowrap">
                    ${price > 0 ? fmtNGN(price) : 'Free'}
                </span>
            </div>
        </button>`;
    }).join('');
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Select a service ──────────────────────────────────────────────────────────
function selectService(id, name, price) {
    selectedService = { id, name, price };

    document.querySelectorAll('.service-card').forEach(c => {
        if (c.id === 'card-' + id) {
            c.classList.add('border-brand', 'bg-brand/10', 'ring-2', 'ring-brand/40');
            c.classList.remove('border-slate-700', 'bg-slate-800', 'hover:border-slate-500', 'hover:bg-slate-700/50');
        } else {
            c.classList.remove('border-brand', 'bg-brand/10', 'ring-2', 'ring-brand/40');
            c.classList.add('border-slate-700', 'bg-slate-800', 'hover:border-slate-500', 'hover:bg-slate-700/50');
        }
    });

    const country    = document.getElementById('country-select')?.value ?? '';
    const countryText = document.getElementById('country-select')?.options[document.getElementById('country-select').selectedIndex]?.text ?? 'Any';

    document.getElementById('sel-name').textContent    = name;
    document.getElementById('sel-country').textContent = currentServer === 'server1' ? 'Russia' : countryText;
    document.getElementById('sel-price').textContent   = price > 0 ? fmtNGN(price) : 'Free';

    document.getElementById('f-server').value  = currentServer;
    document.getElementById('f-service').value = id;
    document.getElementById('f-country').value = country;
    document.getElementById('f-price').value   = price;
    document.getElementById('f-svcname').value = name;

    document.getElementById('no-selection').classList.add('hidden');
    document.getElementById('selection-panel').classList.remove('hidden');

    document.getElementById('selection-panel').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function resetSelection() {
    selectedService = null;
    document.getElementById('no-selection').classList.remove('hidden');
    document.getElementById('selection-panel').classList.add('hidden');
}

// ── Show state placeholder ─────────────────────────────────────────────────
function showServiceState(type, msg) {
    const grid  = document.getElementById('services-grid');
    const state = document.getElementById('services-state');
    grid.classList.add('hidden');
    state.classList.remove('hidden');
    document.getElementById('result-count').textContent = '';

    if (type === 'loading') {
        state.innerHTML = `<div class="w-10 h-10 border-4 border-brand border-t-transparent rounded-full animate-spin mb-4"></div><p class="text-slate-400 text-sm">Loading services…</p>`;
    } else if (type === 'empty') {
        state.innerHTML = `<svg class="w-10 h-10 text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><p class="text-slate-400 text-sm">${escHtml(msg || 'No services available.')}</p>`;
    } else {
        state.innerHTML = `<svg class="w-10 h-10 text-red-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg><p class="text-red-400 text-sm">${escHtml(msg || 'Error loading services.')}</p><button onclick="loadServices()" class="mt-3 text-xs text-brand hover:underline">Retry</button>`;
    }
}

// ── Order submit spinner ──────────────────────────────────────────────────────
document.getElementById('order-btn')?.addEventListener('click', function () {
    if (!selectedService) return;
    this.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg> Ordering…';
    this.disabled = true;
});

// ── Check SMS ─────────────────────────────────────────────────────────────────
async function checkSms(orderId, btn) {
    const orig = btn.textContent;
    btn.textContent = 'Checking…';
    btn.disabled = true;
    try {
        const res  = await fetch(`/dashboard/virtual-numbers/${orderId}/sms`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        if (data.success) {
            const codeEl   = document.getElementById('sms-' + orderId);
            const statusEl = document.getElementById('status-' + orderId);
            if (data.sms_code) {
                codeEl.textContent = data.sms_code;
                codeEl.classList.add('animate-pulse');
                setTimeout(() => codeEl.classList.remove('animate-pulse'), 2000);
            }
            if (data.status === 'completed') {
                statusEl.textContent = 'Completed';
                statusEl.className   = 'inline-flex items-center px-2 py-0.5 rounded-full text-xs border bg-green-900/50 text-green-300 border-green-700/50';
                document.getElementById('actions-' + orderId).innerHTML = '';
            } else {
                btn.textContent = data.sms_code ? orig : 'No SMS yet';
                btn.disabled = false;
                if (!data.sms_code) setTimeout(() => { btn.textContent = orig; }, 3000);
            }
        } else {
            alert(data.message || 'Could not check SMS.');
            btn.textContent = orig;
            btn.disabled = false;
        }
    } catch (e) {
        alert('Network error. Please try again.');
        btn.textContent = orig;
        btn.disabled = false;
    }
}

// ── Init ───────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    loadCountries();
});
</script>
@endsection
