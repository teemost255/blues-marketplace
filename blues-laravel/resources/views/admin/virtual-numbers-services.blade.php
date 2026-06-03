@extends('layouts.admin')
@section('title', 'Services & Pricing Catalog')
@section('page-title', 'Services & Pricing Catalog')
@section('content')

@if(!$configured)
<div class="flex flex-col items-center justify-center py-24 text-center">
    <div class="w-16 h-16 rounded-2xl bg-yellow-900/40 flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    </div>
    <h2 class="text-xl font-semibold text-white mb-2">API Key Not Configured</h2>
    <p class="text-slate-400 max-w-sm mb-6">Add your HeroSMS API key in Admin → Settings to start fetching live services and prices.</p>
    <a href="{{ route('admin.settings') }}" class="btn-primary">Go to Settings</a>
</div>
@else

{{-- ── Top bar ──────────────────────────────────────────────────────────────── --}}
<div class="rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 border border-slate-700/60 p-5 mb-6 flex flex-wrap items-center justify-between gap-4">
    <div class="flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-sky-500/20 border border-sky-500/30 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div>
            <h2 class="font-bold text-white text-base">HeroSMS Services & Pricing</h2>
            <p class="text-xs text-slate-400">Live data from HeroSMS API · Rate: <strong class="text-white">$1 = ₦{{ number_format($usdToNgn, 0) }}</strong> · Commission: <strong class="text-white">{{ $commissionType === 'percent' ? $commissionValue.'%' : '₦'.number_format($commissionValue,2) }}</strong></p>
        </div>
    </div>
    <div class="flex items-center gap-3 flex-wrap">
        <div id="balance-display" onclick="fetchBalance()" title="Click to refresh balance"
            class="cursor-pointer flex items-center gap-2 px-4 py-2 bg-slate-700/60 border border-slate-600 rounded-xl text-xs text-slate-400 hover:text-white hover:border-sky-500/50 transition-all">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            <span id="balance-val">HeroSMS Balance: Click to load</span>
        </div>
        <a href="{{ route('admin.virtual-numbers') }}" class="flex items-center gap-1.5 px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 hover:text-white rounded-xl text-xs font-medium transition-colors">
            ← Orders
        </a>
    </div>
</div>

{{-- ── Filters ─────────────────────────────────────────────────────────────── --}}
<div class="bg-slate-800 border border-slate-700 rounded-xl p-4 mb-5">
    <div class="flex flex-wrap gap-3 items-end">

        {{-- Search --}}
        <div class="relative flex-1 min-w-[200px]">
            <label class="block text-xs text-slate-400 mb-1.5">Search service</label>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input id="svc-search" type="text" placeholder="e.g. WhatsApp, Telegram, Google…"
                    oninput="applyFilter()" autocomplete="off"
                    class="w-full pl-9 pr-4 py-2.5 bg-slate-700 border border-slate-600 text-white rounded-lg text-sm focus:outline-none focus:border-sky-500 placeholder-slate-500">
            </div>
        </div>

        {{-- Country --}}
        <div class="min-w-[180px]">
            <label class="block text-xs text-slate-400 mb-1.5">Country</label>
            <div class="relative">
                <select id="country-select" onchange="loadServices()" class="w-full appearance-none bg-slate-700 border border-slate-600 text-white rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-sky-500 pr-8">
                    <option value="">— All Countries —</option>
                </select>
                <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
        </div>

        {{-- Sort --}}
        <div class="min-w-[160px]">
            <label class="block text-xs text-slate-400 mb-1.5">Sort by</label>
            <div class="relative">
                <select id="svc-sort" onchange="applyFilter()" class="w-full appearance-none bg-slate-700 border border-slate-600 text-white rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-sky-500 pr-8">
                    <option value="name">Name A–Z</option>
                    <option value="price_asc">Cheapest first</option>
                    <option value="price_desc">Most expensive</option>
                    <option value="stock_desc">Most in stock</option>
                </select>
                <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
        </div>

        {{-- View toggle --}}
        <div>
            <label class="block text-xs text-slate-400 mb-1.5">View</label>
            <div class="flex rounded-lg overflow-hidden border border-slate-600">
                <button id="view-table" onclick="setView('table')" title="Table view"
                    class="px-3 py-2.5 bg-sky-600 text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18"/></svg>
                </button>
                <button id="view-grid" onclick="setView('grid')" title="Grid view"
                    class="px-3 py-2.5 bg-slate-700 text-slate-400 hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                </button>
            </div>
        </div>

        {{-- Refresh --}}
        <div>
            <label class="block text-xs text-transparent mb-1.5">.</label>
            <button onclick="loadServices(true)" id="refresh-btn"
                class="flex items-center gap-1.5 px-4 py-2.5 bg-slate-700 hover:bg-slate-600 border border-slate-600 text-slate-300 hover:text-white rounded-lg text-sm font-medium transition-colors">
                <svg id="refresh-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Refresh
            </button>
        </div>

        {{-- Stats --}}
        <div class="ml-auto text-right">
            <p id="svc-count" class="text-xs text-slate-400"></p>
            <p id="cache-label" class="text-[10px] text-slate-600 mt-0.5"></p>
        </div>
    </div>
</div>

{{-- ── State / Loading ──────────────────────────────────────────────────────── --}}
<div id="svc-state" class="flex flex-col items-center justify-center py-24 bg-slate-800/40 border border-slate-700/40 rounded-xl">
    <div class="w-10 h-10 border-[3px] border-sky-400 border-t-transparent rounded-full animate-spin mb-4"></div>
    <p class="text-slate-400 text-sm">Loading countries…</p>
</div>

{{-- ── Table view ───────────────────────────────────────────────────────────── --}}
<div id="view-table-wrap" class="hidden">
    <div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="svc-table">
                <thead>
                    <tr class="border-b border-slate-700 bg-slate-800/80 text-xs text-slate-400 uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Service</th>
                        <th class="px-5 py-3 text-left">Code</th>
                        <th class="px-5 py-3 text-right">USD Cost</th>
                        <th class="px-5 py-3 text-right">NGN Cost</th>
                        <th class="px-5 py-3 text-right">Price (w/ fee)</th>
                        <th class="px-5 py-3 text-right">In Stock</th>
                        <th class="px-5 py-3 text-left">Availability</th>
                    </tr>
                </thead>
                <tbody id="svc-table-body"></tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── Grid view ────────────────────────────────────────────────────────────── --}}
<div id="view-grid-wrap" class="hidden">
    <div id="svc-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"></div>
</div>

@endif

<script>
const CATALOG_SERVICES_URL  = '{{ route('admin.virtual-numbers.services-catalog.data') }}';
const CATALOG_COUNTRIES_URL = '{{ route('admin.virtual-numbers.services-catalog.countries') }}';
const BALANCE_URL           = '{{ route('admin.virtual-numbers.herosms-balance') }}';
const USD_TO_NGN            = {{ $usdToNgn }};
const COMM_TYPE             = '{{ $commissionType }}';
const COMM_VALUE            = {{ $commissionValue }};

let allServices   = [];
let currentView   = 'table';

// ── Helpers ────────────────────────────────────────────────────────────────────
function calcCommission(price) {
    if (COMM_VALUE <= 0) return 0;
    return COMM_TYPE === 'percent'
        ? Math.round(price * COMM_VALUE / 100 * 100) / 100
        : COMM_VALUE;
}
function fmtNgn(v) {
    return '₦' + parseFloat(v || 0).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function fmtUsd(v) {
    return '$' + parseFloat(v || 0).toFixed(4);
}
function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function stockClass(count) {
    if (count > 100) return 'text-green-400';
    if (count > 10)  return 'text-yellow-400';
    if (count > 0)   return 'text-orange-400';
    return 'text-red-400';
}
function stockLabel(count) {
    if (count > 9999) return '9,999+';
    return count.toLocaleString();
}
function availBar(count) {
    const pct = Math.min(count / 100 * 100, 100);
    const color = count > 100 ? '#22c55e' : count > 10 ? '#facc15' : count > 0 ? '#f97316' : '#ef4444';
    return `<div class="w-24 bg-slate-700 rounded-full h-1.5 overflow-hidden">
        <div class="h-1.5 rounded-full" style="width:${Math.max(pct,2)}%;background:${color}"></div>
    </div>`;
}
function getServiceIcon(name) {
    const icons = {
        'whatsapp':'💬','telegram':'✈️','google':'🔍','instagram':'📸','facebook':'👤',
        'tiktok':'🎵','twitter':'🐦','discord':'🎮','snapchat':'👻','microsoft':'🪟',
        'amazon':'📦','netflix':'🎬','viber':'📳','wechat':'💚','linkedin':'💼',
        'uber':'🚗','signal':'🔒','spotify':'🎧','steam':'🎮','paypal':'💳',
        'binance':'🟡','coinbase':'🔵','bybit':'🔷','tinder':'🔥','airbnb':'🏠',
        'youtube':'▶️','chatgpt':'🤖','apple':'🍎','reddit':'🤖','pinterest':'📌',
        'twitch':'🎙️','skype':'📞','line':'💚','yahoo':'💜','outlook':'📧',
        'ebay':'🛒','aliexpress':'🛍️','shopee':'🛒',
    };
    const n = (name || '').toLowerCase();
    for (const [k, icon] of Object.entries(icons)) {
        if (n.includes(k)) return icon;
    }
    return '📱';
}

// ── Balance ────────────────────────────────────────────────────────────────────
async function fetchBalance() {
    const el = document.getElementById('balance-val');
    el.textContent = 'Loading…';
    try {
        const r = await fetch(BALANCE_URL);
        const d = await r.json();
        el.textContent = d.success
            ? 'HeroSMS Balance: $' + parseFloat(d.balance).toFixed(4)
            : 'Error: ' + (d.message || 'Unknown');
    } catch(e) {
        el.textContent = 'Failed to load balance';
    }
}

// ── Countries ──────────────────────────────────────────────────────────────────
async function loadCountries() {
    try {
        const r = await fetch(CATALOG_COUNTRIES_URL);
        const d = await r.json();
        if (d.success && d.data?.length) {
            const sel = document.getElementById('country-select');
            sel.innerHTML = '<option value="">— All Countries —</option>';
            d.data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.code;
                const iso = (c.iso || '').toUpperCase();
                const flag = iso.length === 2
                    ? iso.split('').map(ch => String.fromCodePoint(ch.charCodeAt(0) - 65 + 0x1F1E6)).join('')
                    : '🌍';
                opt.textContent = flag + ' ' + c.name + ' (' + c.code + ')';
                sel.appendChild(opt);
            });
        }
        loadServices();
    } catch(e) {
        showState('error', 'Could not load countries. Check your API key in Settings.');
    }
}

// ── Services ───────────────────────────────────────────────────────────────────
async function loadServices(bust = false) {
    const country = document.getElementById('country-select').value;
    const btn     = document.getElementById('refresh-btn');
    const icon    = document.getElementById('refresh-icon');

    showState('loading', '');
    if (btn) btn.disabled = true;
    if (icon) icon.classList.add('animate-spin');

    try {
        let url = CATALOG_SERVICES_URL + '?country=' + encodeURIComponent(country);
        if (bust) url += '&bust=1';

        const r = await fetch(url);
        const d = await r.json();

        if (d.success && d.data?.length) {
            allServices = d.data;
            const cacheEl = document.getElementById('cache-label');
            if (cacheEl) cacheEl.textContent = d.cached ? '⚡ Served from cache (5 min)' : '🔄 Live from HeroSMS';
            applyFilter();
        } else {
            showState('error', d.message || 'No services returned.');
        }
    } catch(e) {
        showState('error', 'Network error. Please try again.');
    } finally {
        if (btn) btn.disabled = false;
        if (icon) icon.classList.remove('animate-spin');
    }
}

// ── Filter & Render ────────────────────────────────────────────────────────────
function applyFilter() {
    const q    = (document.getElementById('svc-search').value || '').toLowerCase().trim();
    const sort = document.getElementById('svc-sort').value;

    let list = allServices.filter(s => {
        if (!q) return true;
        return (s.name || '').toLowerCase().includes(q)
            || (s.serviceId || '').toLowerCase().includes(q);
    });

    if (sort === 'price_asc')   list.sort((a,b) => (a.cost||0) - (b.cost||0));
    else if (sort === 'price_desc') list.sort((a,b) => (b.cost||0) - (a.cost||0));
    else if (sort === 'stock_desc') list.sort((a,b) => (b.count||0) - (a.count||0));
    else list.sort((a,b) => (a.name||'').localeCompare(b.name||''));

    const countEl = document.getElementById('svc-count');
    if (countEl) countEl.textContent = list.length.toLocaleString() + ' service' + (list.length !== 1 ? 's' : '');

    hideState();
    if (currentView === 'table') renderTable(list);
    else renderGrid(list);
}

// ── Table ──────────────────────────────────────────────────────────────────────
function renderTable(list) {
    document.getElementById('view-table-wrap').classList.remove('hidden');
    document.getElementById('view-grid-wrap').classList.add('hidden');

    if (!list.length) {
        document.getElementById('svc-table-body').innerHTML =
            '<tr><td colspan="7" class="px-6 py-12 text-center text-slate-500">No services match your filters.</td></tr>';
        return;
    }

    const rows = list.map(s => {
        const cost    = parseFloat(s.cost || 0);
        const costNgn = parseFloat(s.cost_ngn || cost * USD_TO_NGN);
        const comm    = calcCommission(costNgn);
        const total   = Math.round((costNgn + comm) * 100) / 100;
        const count   = parseInt(s.count || 0);

        return `<tr class="border-b border-slate-700/40 hover:bg-slate-700/20 transition-colors">
            <td class="px-5 py-3">
                <div class="flex items-center gap-2.5">
                    <span class="text-xl leading-none">${getServiceIcon(s.name)}</span>
                    <span class="font-semibold text-white">${escHtml(s.name || s.serviceId)}</span>
                </div>
            </td>
            <td class="px-5 py-3">
                <span class="font-mono text-xs bg-slate-700 text-sky-300 px-2 py-1 rounded">${escHtml(s.serviceId)}</span>
            </td>
            <td class="px-5 py-3 text-right text-slate-300 font-mono text-xs">${fmtUsd(cost)}</td>
            <td class="px-5 py-3 text-right text-white font-semibold">${fmtNgn(costNgn)}</td>
            <td class="px-5 py-3 text-right">
                <span class="font-bold text-white">${fmtNgn(total)}</span>
                ${comm > 0 ? '<span class="block text-[10px] text-slate-500 font-normal">incl. ₦'+comm.toFixed(2)+' fee</span>' : ''}
            </td>
            <td class="px-5 py-3 text-right">
                <span class="font-bold ${stockClass(count)}">${stockLabel(count)}</span>
            </td>
            <td class="px-5 py-3">
                <div class="flex items-center gap-2">
                    ${availBar(count)}
                    <span class="text-[10px] ${stockClass(count)} font-semibold">
                        ${count > 100 ? 'High' : count > 10 ? 'Medium' : count > 0 ? 'Low' : 'Out'}
                    </span>
                </div>
            </td>
        </tr>`;
    });

    document.getElementById('svc-table-body').innerHTML = rows.join('');
}

// ── Grid ───────────────────────────────────────────────────────────────────────
function renderGrid(list) {
    document.getElementById('view-grid-wrap').classList.remove('hidden');
    document.getElementById('view-table-wrap').classList.add('hidden');

    if (!list.length) {
        document.getElementById('svc-grid').innerHTML =
            '<div class="col-span-full text-center py-12 text-slate-500">No services match your filters.</div>';
        return;
    }

    const cards = list.map(s => {
        const cost    = parseFloat(s.cost || 0);
        const costNgn = parseFloat(s.cost_ngn || cost * USD_TO_NGN);
        const comm    = calcCommission(costNgn);
        const total   = Math.round((costNgn + comm) * 100) / 100;
        const count   = parseInt(s.count || 0);

        return `<div class="bg-slate-800 border border-slate-700/60 rounded-xl p-4 flex flex-col gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-700 flex items-center justify-center text-xl leading-none shrink-0">
                    ${getServiceIcon(s.name)}
                </div>
                <div class="min-w-0">
                    <p class="font-bold text-white text-sm truncate">${escHtml(s.name || s.serviceId)}</p>
                    <span class="font-mono text-[10px] text-sky-400">${escHtml(s.serviceId)}</span>
                </div>
            </div>
            <div class="pt-2 border-t border-slate-700/40 space-y-1.5">
                <div class="flex justify-between text-xs">
                    <span class="text-slate-400">API Cost</span>
                    <span class="text-slate-300 font-mono">${fmtUsd(cost)} / ${fmtNgn(costNgn)}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-slate-400">User Pays</span>
                    <span class="font-bold text-white">${fmtNgn(total)}</span>
                </div>
                <div class="flex justify-between text-xs items-center">
                    <span class="text-slate-400">In Stock</span>
                    <span class="font-bold ${stockClass(count)}">${stockLabel(count)}</span>
                </div>
                ${availBar(count)}
            </div>
        </div>`;
    });

    document.getElementById('svc-grid').innerHTML = cards.join('');
}

// ── View toggle ────────────────────────────────────────────────────────────────
function setView(v) {
    currentView = v;
    document.getElementById('view-table').classList.toggle('bg-sky-600', v === 'table');
    document.getElementById('view-table').classList.toggle('bg-slate-700', v !== 'table');
    document.getElementById('view-table').classList.toggle('text-slate-400', v !== 'table');
    document.getElementById('view-grid').classList.toggle('bg-sky-600', v === 'grid');
    document.getElementById('view-grid').classList.toggle('bg-slate-700', v !== 'grid');
    document.getElementById('view-grid').classList.toggle('text-slate-400', v !== 'grid');
    applyFilter();
}

// ── State helpers ──────────────────────────────────────────────────────────────
function showState(type, msg) {
    document.getElementById('svc-state').classList.remove('hidden');
    document.getElementById('view-table-wrap').classList.add('hidden');
    document.getElementById('view-grid-wrap').classList.add('hidden');
    const el = document.getElementById('svc-state');
    if (type === 'loading') {
        el.innerHTML = '<div class="w-10 h-10 border-[3px] border-sky-400 border-t-transparent rounded-full animate-spin mb-4"></div><p class="text-slate-400 text-sm">Fetching from HeroSMS…</p>';
    } else {
        el.innerHTML = `
            <svg class="w-10 h-10 text-red-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <p class="text-red-400 font-semibold text-sm mb-2">${escHtml(msg || 'Error loading services.')}</p>
            <button onclick="loadServices(true)" class="mt-2 px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-lg text-xs font-semibold transition-colors">Retry (Bypass Cache)</button>`;
    }
}
function hideState() {
    document.getElementById('svc-state').classList.add('hidden');
}

// ── Init ───────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    loadCountries();
    fetchBalance();
});
</script>
@endsection
