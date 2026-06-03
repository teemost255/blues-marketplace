@extends('layouts.dashboard')
@section('title', 'Virtual Numbers')
@section('page-title', 'Virtual Numbers')
@section('content')

@if(!$enabled)
<div class="bg-yellow-900/40 border border-yellow-700 text-yellow-300 text-sm rounded-xl px-5 py-4 mb-6 flex items-start gap-3">
    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <div>
        <p class="font-medium">Virtual numbers are temporarily unavailable</p>
        <p class="text-yellow-400/70 text-xs mt-0.5">Our team is working on restoring this service. Please check back soon.</p>
    </div>
</div>
@endif

{{-- Stats strip --}}
<div class="grid grid-cols-3 gap-3 mb-6">
    <div class="bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-center">
        <p class="text-xl font-bold text-white">{{ $orders->total() }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Total Orders</p>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-center">
        <p class="text-xl font-bold text-green-400">{{ $orders->getCollection()->where('status','completed')->count() }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Completed</p>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-center">
        <p class="text-xl font-bold text-brand">₦{{ number_format($price, 0) }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Per Number</p>
    </div>
</div>

@if($enabled)
{{-- Order form --}}
<div class="bg-slate-800 border border-slate-700 rounded-xl p-5 mb-6" id="order-form-card">
    <div class="flex items-center gap-3 mb-4">
        <div class="w-9 h-9 rounded-lg bg-emerald-900/50 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
        </div>
        <div>
            <h2 class="font-semibold text-white">Get a Virtual Number</h2>
            <p class="text-xs text-slate-400">Receive an SMS verification code on a real phone number</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        {{-- Country --}}
        <div>
            <label class="block text-xs text-slate-400 mb-1.5">Country</label>
            <select id="country-select" class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-brand">
                <option value="">Loading countries…</option>
            </select>
        </div>

        {{-- Service --}}
        <div>
            <label class="block text-xs text-slate-400 mb-1.5">Service</label>
            <select id="service-select" class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-brand" disabled>
                <option value="">Select a country first</option>
            </select>
        </div>

        {{-- Cost --}}
        <div>
            <label class="block text-xs text-slate-400 mb-1.5">Cost</label>
            <div class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-white flex items-center gap-2">
                <span class="text-emerald-400 font-semibold">₦{{ number_format($price, 2) }}</span>
                <span class="text-slate-500 text-xs">from wallet (bal: <span id="wallet-bal">₦{{ number_format($wallet->balance, 2) }}</span>)</span>
            </div>
        </div>
    </div>

    <div id="order-error" class="hidden bg-red-900/40 border border-red-700 text-red-300 text-sm rounded-lg px-4 py-2.5 mb-4"></div>

    <button id="get-number-btn" onclick="orderNumber()"
        class="w-full md:w-auto px-6 py-2.5 bg-brand hover:bg-brand-dark text-white text-sm font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Get Number — ₦{{ number_format($price, 0) }}
    </button>
</div>
@endif

{{-- Active orders (waiting / received) --}}
@php $activeOrders = $orders->getCollection()->whereIn('status', ['waiting', 'received']); @endphp
@if($activeOrders->isNotEmpty())
<div class="mb-6 space-y-3" id="active-orders">
    <h3 class="text-sm font-semibold text-slate-300">Active Numbers</h3>
    @foreach($activeOrders as $order)
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-4" id="active-order-{{ $order->id }}">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-900/40 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                </div>
                <div>
                    <p class="text-white font-semibold font-mono tracking-wide text-lg">+{{ $order->phone_number }}</p>
                    <p class="text-xs text-slate-400">{{ $order->service_name }} · {{ $order->country_name ?: 'Country #'.$order->country }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($order->status === 'received')
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-900/50 text-blue-400 border border-blue-700/50">
                    SMS Received!
                </span>
                @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-yellow-900/50 text-yellow-400 border border-yellow-700/50 animate-pulse">
                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 inline-block"></span>Waiting for SMS
                </span>
                @endif
            </div>
        </div>

        {{-- SMS code display --}}
        <div id="sms-code-{{ $order->id }}" class="{{ $order->sms_code ? '' : 'hidden' }} mt-3 bg-emerald-900/30 border border-emerald-700/50 rounded-lg px-4 py-3 flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p class="text-xs text-emerald-400 mb-0.5">Verification Code</p>
                <p class="text-2xl font-bold text-white tracking-widest font-mono" id="code-text-{{ $order->id }}">{{ $order->sms_code ?? '' }}</p>
            </div>
            <button onclick="copyCode('{{ $order->sms_code ?? '' }}')" class="ml-auto text-slate-400 hover:text-white" title="Copy code">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            </button>
        </div>

        {{-- Expires --}}
        @if($order->expires_at && $order->status === 'waiting')
        <p class="text-xs text-slate-500 mt-2">Expires {{ $order->expires_at->diffForHumans() }}</p>
        @endif

        {{-- Action buttons --}}
        <div class="flex items-center gap-2 mt-3">
            @if($order->status === 'waiting')
            <button onclick="checkStatus({{ $order->id }})"
                class="px-4 py-2 text-xs font-semibold bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition-colors flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Check SMS
            </button>
            @endif
            @if($order->status === 'received')
            <button onclick="completeOrder({{ $order->id }})"
                class="px-4 py-2 text-xs font-semibold bg-green-700 hover:bg-green-600 text-white rounded-lg transition-colors">
                ✓ Mark Complete
            </button>
            @endif
            @if($order->status === 'waiting')
            <button onclick="cancelOrder({{ $order->id }})"
                class="px-4 py-2 text-xs font-semibold bg-slate-700 hover:bg-red-900/60 text-slate-300 hover:text-red-300 rounded-lg transition-colors">
                Cancel
            </button>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- History table --}}
<div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-700 flex items-center justify-between">
        <h2 class="font-semibold text-white">Order History</h2>
        <span class="text-xs text-slate-400">{{ $orders->total() }} total</span>
    </div>
    @if($orders->isEmpty())
    <div class="px-5 py-12 text-center">
        <svg class="w-10 h-10 text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
        <p class="text-slate-400 text-sm">No virtual number orders yet</p>
        <p class="text-slate-500 text-xs mt-1">Get your first virtual number above</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-700 text-slate-400 text-xs uppercase">
                    <th class="px-5 py-3 text-left">Phone Number</th>
                    <th class="px-5 py-3 text-left">Service</th>
                    <th class="px-5 py-3 text-left">SMS Code</th>
                    <th class="px-5 py-3 text-left">Cost</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left">Date</th>
                </tr>
            </thead>
            <tbody>
            @foreach($orders as $order)
                <tr class="border-b border-slate-700/50 hover:bg-slate-700/20 transition-colors">
                    <td class="px-5 py-3 font-mono text-white text-sm">+{{ $order->phone_number ?? '—' }}</td>
                    <td class="px-5 py-3">
                        <p class="text-white text-sm">{{ $order->service_name }}</p>
                        <p class="text-xs text-slate-500">{{ $order->country_name ?: 'Country #'.$order->country }}</p>
                    </td>
                    <td class="px-5 py-3">
                        @if($order->sms_code)
                        <span class="font-mono text-emerald-400 font-semibold tracking-widest">{{ $order->sms_code }}</span>
                        @else
                        <span class="text-slate-500">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-white font-semibold">₦{{ number_format($order->cost, 2) }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs border {{ $order->statusBadgeClass() }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-slate-400 whitespace-nowrap text-xs">{{ $order->created_at->format('M j, Y g:ia') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
    <div class="px-5 py-4 border-t border-slate-700">{{ $orders->links() }}</div>
    @endif
    @endif
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';
let countryMap = {};

async function loadCountries() {
    try {
        const resp = await fetch('{{ route("dashboard.virtual-numbers.countries") }}', {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await resp.json();
        const sel = document.getElementById('country-select');
        sel.innerHTML = '<option value="">— Select country —</option>';
        if (data.countries && data.countries.length) {
            data.countries.forEach(c => {
                countryMap[c.id] = c.name;
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.name;
                if (c.name === 'Nigeria' || c.name === 'Russia') opt.setAttribute('data-preferred','1');
                sel.appendChild(opt);
            });
        } else {
            sel.innerHTML = '<option value="">No countries available</option>';
        }
    } catch(e) {
        document.getElementById('country-select').innerHTML = '<option value="">Failed to load</option>';
    }
}

document.getElementById('country-select')?.addEventListener('change', async function() {
    const sel = document.getElementById('service-select');
    const countryId = this.value;
    if (!countryId) { sel.innerHTML = '<option value="">Select a country first</option>'; sel.disabled = true; return; }

    sel.innerHTML = '<option value="">Loading services…</option>';
    sel.disabled = true;

    try {
        const resp = await fetch(`{{ route("dashboard.virtual-numbers.services") }}?country=${countryId}`, {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await resp.json();
        if (data.services && data.services.length) {
            sel.innerHTML = '<option value="">— Select service —</option>';
            data.services.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.code;
                opt.textContent = `${s.name} (${s.count} available)`;
                sel.appendChild(opt);
            });
            sel.disabled = false;
        } else {
            sel.innerHTML = '<option value="">No services available for this country</option>';
        }
    } catch(e) {
        sel.innerHTML = '<option value="">Failed to load services</option>';
    }
});

async function orderNumber() {
    const country = document.getElementById('country-select').value;
    const service = document.getElementById('service-select').value;
    const errDiv  = document.getElementById('order-error');
    const btn     = document.getElementById('get-number-btn');

    errDiv.classList.add('hidden');

    if (!country) { showErr('Please select a country.'); return; }
    if (!service) { showErr('Please select a service.'); return; }

    btn.disabled = true;
    btn.innerHTML = `<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg> Getting number…`;

    try {
        const resp = await fetch('{{ route("dashboard.virtual-numbers.order") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ country: parseInt(country), country_name: countryMap[country] || '', service })
        });
        const data = await resp.json();

        if (!resp.ok || !data.success) {
            showErr(data.error || 'Failed to get number. Try again.');
            return;
        }

        window.location.reload();
    } catch(e) {
        showErr('Network error. Please try again.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Get Number — ₦{{ number_format($price, 0) }}`;
    }
}

async function checkStatus(orderId) {
    try {
        const resp = await fetch(`/dashboard/virtual-numbers/${orderId}/status`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await resp.json();

        if (data.status === 'received' && data.code) {
            document.getElementById(`code-text-${orderId}`).textContent = data.code;
            document.getElementById(`sms-code-${orderId}`).classList.remove('hidden');
            window.location.reload();
        } else if (data.status === 'cancelled' || data.status === 'expired') {
            window.location.reload();
        } else {
            showToast('No SMS yet. Try again in a moment.', 'info');
        }
    } catch(e) {
        showToast('Could not check status.', 'error');
    }
}

async function completeOrder(orderId) {
    if (!confirm('Mark this number as complete? This will close the activation.')) return;
    const resp = await fetch(`/dashboard/virtual-numbers/${orderId}/complete`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    });
    if (resp.ok) window.location.reload();
}

async function cancelOrder(orderId) {
    if (!confirm('Cancel this number? A partial refund may be issued.')) return;
    const resp = await fetch(`/dashboard/virtual-numbers/${orderId}/cancel`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    });
    const data = await resp.json();
    if (resp.ok && data.success) {
        if (data.refunded > 0) {
            showToast(`Cancelled. ₦${data.refunded.toFixed(2)} refunded to your wallet.`, 'success');
        }
        setTimeout(() => window.location.reload(), 1500);
    }
}

function copyCode(code) {
    navigator.clipboard.writeText(code).then(() => showToast('Code copied!', 'success'));
}

function showErr(msg) {
    const d = document.getElementById('order-error');
    d.textContent = msg;
    d.classList.remove('hidden');
}

function showToast(msg, type = 'info') {
    const colors = { success: 'bg-green-900/90 border-green-700 text-green-200', error: 'bg-red-900/90 border-red-700 text-red-200', info: 'bg-slate-700 border-slate-600 text-white' };
    const t = document.createElement('div');
    t.className = `fixed bottom-5 right-5 z-50 px-4 py-3 rounded-lg border text-sm shadow-xl ${colors[type] || colors.info}`;
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3500);
}

document.addEventListener('DOMContentLoaded', () => {
    @if($enabled) loadCountries(); @endif
});
</script>
@endsection
