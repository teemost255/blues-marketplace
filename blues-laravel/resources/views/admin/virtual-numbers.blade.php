@extends('layouts.admin')
@section('title', 'Virtual Number Orders')
@section('page-title', 'Virtual Number Orders')
@section('content')

{{-- Hero-SMS Diagnostic Tool --}}
<div class="bg-slate-800 border border-slate-700 rounded-xl p-5 mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-sm font-semibold text-white">Hero-SMS Diagnostic</h2>
            <p class="text-xs text-slate-400 mt-0.5">Check what numbers are actually available for a country + service. Bypasses the UI cache.</p>
        </div>
        <span id="diag-balance" class="text-xs text-slate-400 cursor-pointer hover:text-sky-400" onclick="fetchBalance()">Check balance ↗</span>
    </div>
    <div class="flex flex-wrap gap-3 mb-4">
        <div class="flex-1 min-w-40">
            <label class="block text-xs text-slate-400 mb-1">Country ID <span class="text-slate-500">(numeric, from Hero-SMS)</span></label>
            <input type="text" id="diag-country" placeholder="e.g. 2 for Canada, 187 for USA" class="w-full text-sm">
        </div>
        <div class="w-48">
            <label class="block text-xs text-slate-400 mb-1">Service code</label>
            <input type="text" id="diag-service" placeholder="e.g. wa, tg, fb" class="w-full text-sm">
        </div>
        <div class="flex items-end">
            <button onclick="runDiag()" class="btn-primary text-sm px-5">Run Test</button>
        </div>
    </div>
    <div id="diag-result" class="hidden">
        <div class="border border-slate-600 rounded-lg overflow-hidden">
            <div id="diag-summary" class="px-4 py-3 bg-slate-700/50 text-sm"></div>
            <div id="diag-table-wrap" class="overflow-x-auto max-h-64"></div>
        </div>
    </div>
</div>

<script>
async function fetchBalance() {
    const el = document.getElementById('diag-balance');
    el.textContent = 'Loading…';
    const r = await fetch('{{ route('admin.virtual-numbers.herosms-balance') }}');
    const d = await r.json();
    el.textContent = d.success ? 'Balance: $' + parseFloat(d.balance).toFixed(4) : (d.message || 'Error');
}

async function runDiag() {
    const country = document.getElementById('diag-country').value.trim();
    const service = document.getElementById('diag-service').value.trim();
    const resultEl = document.getElementById('diag-result');
    const summaryEl = document.getElementById('diag-summary');
    const tableEl = document.getElementById('diag-table-wrap');

    summaryEl.innerHTML = '<span class="text-slate-400">Querying Hero-SMS…</span>';
    tableEl.innerHTML = '';
    resultEl.classList.remove('hidden');

    const params = new URLSearchParams();
    if (country) params.set('country', country);
    if (service) params.set('service', service);

    const r = await fetch('{{ route('admin.virtual-numbers.herosms-diagnose') }}?' + params.toString());
    const d = await r.json();

    if (!d.success) {
        summaryEl.innerHTML = '<span class="text-red-400">' + (d.message || 'Error') + '</span>';
        return;
    }

    const target = d.target_service;
    let summaryHtml = '<div class="flex flex-wrap gap-4">';
    summaryHtml += '<span class="text-slate-300">Country: <strong class="text-white">' + escHtml(d.country_queried) + '</strong></span>';
    summaryHtml += '<span class="text-slate-300">Service filter: <strong class="text-white">' + escHtml(d.service_queried) + '</strong></span>';
    summaryHtml += '<span class="text-slate-300">Services found: <strong class="text-white">' + d.services_count + '</strong></span>';
    if (d.service_queried !== '(all)') {
        if (target) {
            summaryHtml += '<span class="text-green-400 font-semibold">✓ &ldquo;' + escHtml(service) + '&rdquo; found — count: ' + target.count + ', cost: $' + target.cost + '</span>';
        } else {
            summaryHtml += '<span class="text-red-400 font-semibold">✗ &ldquo;' + escHtml(service) + '&rdquo; NOT in services list for this country</span>';
        }
    }
    if (!d.services_success) {
        summaryHtml += '<span class="text-red-400">API error: ' + escHtml(d.services_message || '') + '</span>';
    }
    summaryHtml += '</div>';
    summaryEl.innerHTML = summaryHtml;

    if (d.services_list && d.services_list.length > 0) {
        let rows = d.services_list.map(s => {
            const hi = (service && s.serviceId === service) ? ' bg-green-900/30' : '';
            return `<tr class="border-b border-slate-700/40 hover:bg-slate-700/20${hi}">
                <td class="px-4 py-2 font-mono text-sky-300 text-xs">${escHtml(s.serviceId)}</td>
                <td class="px-4 py-2 text-white text-xs">${escHtml(s.name)}</td>
                <td class="px-4 py-2 text-slate-300 text-xs">${s.count}</td>
                <td class="px-4 py-2 text-slate-300 text-xs">$${s.cost}</td>
            </tr>`;
        }).join('');
        tableEl.innerHTML = `<table class="w-full text-sm">
            <thead><tr class="bg-slate-800 text-xs text-slate-400 uppercase">
                <th class="px-4 py-2 text-left">Code</th>
                <th class="px-4 py-2 text-left">Name</th>
                <th class="px-4 py-2 text-left">Count</th>
                <th class="px-4 py-2 text-left">Cost (USD)</th>
            </tr></thead><tbody>${rows}</tbody></table>`;
    } else if (d.services_success) {
        tableEl.innerHTML = '<p class="px-4 py-3 text-slate-500 text-sm">No services with count &gt; 0 returned for this country.</p>';
    }
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    @foreach([
        ['label' => 'Total Orders', 'value' => $stats['total'],     'color' => 'text-white',        'bg' => 'bg-slate-700/50'],
        ['label' => 'Active',       'value' => $stats['active'],    'color' => 'text-blue-400',     'bg' => 'bg-blue-900/20'],
        ['label' => 'Completed',    'value' => $stats['completed'], 'color' => 'text-green-400',    'bg' => 'bg-green-900/20'],
        ['label' => 'Cancelled',    'value' => $stats['cancelled'], 'color' => 'text-slate-400',    'bg' => 'bg-slate-700/30'],
        ['label' => 'Revenue',      'value' => '₦'.number_format($stats['revenue'],2), 'color' => 'text-brand', 'bg' => 'bg-sky-900/20'],
    ] as $s)
    <div class="bg-slate-800 border border-slate-700 rounded-xl px-5 py-4">
        <p class="text-xs text-slate-400 mb-1">{{ $s['label'] }}</p>
        <p class="text-xl font-bold {{ $s['color'] }}">{{ $s['value'] }}</p>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<form method="GET" class="flex flex-wrap gap-2 mb-5">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search user, number, service…" class="flex-1 min-w-48">
    <select name="status" class="w-36">
        <option value="">All statuses</option>
        @foreach(['pending','active','completed','cancelled','failed'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <input type="text" name="service" value="{{ request('service') }}" placeholder="Service (whatsapp…)" class="w-40">
    <button class="btn-primary">Filter</button>
    @if(request()->hasAny(['search','status','service']))
        <a href="{{ route('admin.virtual-numbers') }}" class="btn-primary" style="background:#475569;">Clear</a>
    @endif
</form>

<div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-700 text-slate-400 text-xs uppercase bg-slate-800/80">
                    <th class="px-5 py-3 text-left">#</th>
                    <th class="px-5 py-3 text-left">User</th>
                    <th class="px-5 py-3 text-left">Provider</th>
                    <th class="px-5 py-3 text-left">Service</th>
                    <th class="px-5 py-3 text-left">Phone Number</th>
                    <th class="px-5 py-3 text-left">SMS Code</th>
                    <th class="px-5 py-3 text-left">Cost</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left">Date</th>
                    <th class="px-5 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($orders as $order)
                <tr class="border-b border-slate-700/50 hover:bg-slate-700/20">
                    <td class="px-5 py-3 text-slate-400 font-mono text-xs">#{{ $order->id }}</td>
                    <td class="px-5 py-3">
                        <p class="text-white font-medium">{{ $order->user?->name ?? '—' }}</p>
                        <p class="text-xs text-slate-400">{{ $order->user?->email ?? '' }}</p>
                    </td>
                    <td class="px-5 py-3">
                        @php
                            $providerBadge = 'bg-cyan-900/50 text-cyan-300 border-cyan-700/50';
                            $providerLabel = 'Hero-SMS';
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs border {{ $providerBadge }}">
                            {{ $providerLabel }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <span class="font-medium text-white capitalize">{{ $order->service }}</span>
                        <span class="text-xs text-slate-400 ml-1 uppercase">({{ $order->country }})</span>
                    </td>
                    <td class="px-5 py-3 font-mono text-slate-300 text-xs">{{ $order->phone_number ?? '—' }}</td>
                    <td class="px-5 py-3 font-mono font-bold text-green-400 text-xs">{{ $order->sms_code ?? '—' }}</td>
                    <td class="px-5 py-3 text-white">₦{{ number_format($order->cost, 2) }}</td>
                    <td class="px-5 py-3">
                        @php
                            $badge = match($order->status) {
                                'active'    => 'bg-blue-900/50 text-blue-300 border-blue-700/50',
                                'completed' => 'bg-green-900/50 text-green-300 border-green-700/50',
                                'cancelled' => 'bg-slate-700/50 text-slate-400 border-slate-600/50',
                                'failed'    => 'bg-red-900/50 text-red-300 border-red-700/50',
                                default     => 'bg-yellow-900/50 text-yellow-300 border-yellow-700/50',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs border {{ $badge }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-slate-400 text-xs whitespace-nowrap">{{ $order->created_at->format('M d, Y H:i') }}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-1">
                            <button onclick="openModal('modal-status-{{ $order->id }}')"
                                class="p-1.5 rounded text-slate-400 hover:text-sky-400 hover:bg-slate-700 transition-colors" title="Change status">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button onclick="openModal('modal-delete-vn-{{ $order->id }}')"
                                class="p-1.5 rounded text-slate-400 hover:text-red-400 hover:bg-slate-700 transition-colors" title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>

                {{-- Change Status Modal --}}
                <div id="modal-status-{{ $order->id }}" class="modal-overlay" style="display:none;">
                    <div class="modal-box">
                        <h3 class="font-semibold text-white mb-4">Update Order Status</h3>
                        <p class="text-sm text-slate-400 mb-1">Order #{{ $order->id }} — {{ $order->service }} ({{ $order->user?->name }})</p>
                        <p class="text-xs text-slate-500 mb-4">Number: {{ $order->phone_number ?? 'N/A' }}</p>
                        <form method="POST" action="{{ route('admin.virtual-numbers.status', $order) }}" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs text-slate-400 mb-1.5">New Status</label>
                                <select name="status">
                                    @foreach(['pending','active','completed','cancelled','failed'] as $s)
                                        <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex gap-3">
                                <button type="submit" class="btn-primary">Update</button>
                                <button type="button" onclick="closeModal('modal-status-{{ $order->id }}')" class="btn-primary" style="background:#475569;">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Delete Modal --}}
                <div id="modal-delete-vn-{{ $order->id }}" class="modal-overlay" style="display:none;">
                    <div class="modal-box">
                        <h3 class="font-semibold text-white mb-3">Delete Order</h3>
                        <p class="text-sm text-slate-300 mb-5">Delete order #{{ $order->id }} for <strong>{{ $order->user?->name }}</strong>? This cannot be undone.</p>
                        <div class="flex gap-3">
                            <form method="POST" action="{{ route('admin.virtual-numbers.destroy', $order) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger">Delete</button>
                            </form>
                            <button onclick="closeModal('modal-delete-vn-{{ $order->id }}')" class="btn-primary" style="background:#475569;">Cancel</button>
                        </div>
                    </div>
                </div>

            @empty
                <tr><td colspan="10" class="px-6 py-12 text-center text-slate-500">No virtual number orders yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
        <div class="px-6 py-4 border-t border-slate-700">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
