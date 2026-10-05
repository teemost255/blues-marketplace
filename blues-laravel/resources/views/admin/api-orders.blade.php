@extends('layouts.admin')
@section('title', 'API Orders & Accounts')
@section('page-title', 'Sameeha Social Hub — API Orders')

@section('content')
@if(!$configured)
<div class="bg-yellow-900/30 border border-yellow-700/50 rounded-xl px-5 py-4 flex items-center gap-3 mb-6">
    <svg class="w-5 h-5 text-yellow-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    <p class="text-yellow-300 text-sm">Sameeha API key is not configured. <a href="{{ route('admin.settings') }}" class="underline font-semibold">Go to Settings</a> to add it.</p>
</div>
@endif

@if($configured && $providerBalance)
<div class="bg-slate-800 border border-slate-700 rounded-xl px-5 py-4 mb-6">
    <p class="text-xs text-slate-400 mb-1">Sameeha supplier wallet balance</p>
    <p class="text-xl font-bold text-green-400">{{ $providerBalance['currency'] }} {{ number_format($providerBalance['balance'], 2) }}</p>
</div>
@endif

{{-- Stats Row --}}
@if($stats)
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
    @php
        $statCards = [
            ['label' => 'Total Accounts',  'value' => number_format($stats['total_accounts'] ?? 0),   'color' => 'text-sky-400'],
            ['label' => 'Sold',            'value' => number_format($stats['sold_accounts'] ?? 0),    'color' => 'text-green-400'],
            ['label' => 'Unsold',          'value' => number_format($stats['unsold_accounts'] ?? 0),  'color' => 'text-yellow-400'],
            ['label' => 'Total Orders',    'value' => number_format($stats['total_orders'] ?? 0),     'color' => 'text-purple-400'],
            ['label' => 'Total Spent',     'value' => '₦' . number_format($stats['total_spent'] ?? 0, 2), 'color' => 'text-red-400'],
        ];
    @endphp
    @foreach($statCards as $card)
    <div class="bg-slate-800 border border-slate-700 rounded-xl px-5 py-4">
        <p class="text-xs text-slate-400 mb-1">{{ $card['label'] }}</p>
        <p class="text-xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</p>
    </div>
    @endforeach
</div>
@endif

{{-- Tabs --}}
<div class="flex gap-1 mb-4" id="api-tabs">
    <button onclick="switchTab('orders')" id="tab-orders"
        class="tab-btn px-4 py-2 rounded-lg text-sm font-semibold transition-colors bg-sky-600 text-white">
        Orders
    </button>
    <button onclick="switchTab('accounts')" id="tab-accounts"
        class="tab-btn px-4 py-2 rounded-lg text-sm font-semibold transition-colors bg-slate-700 text-slate-300 hover:bg-slate-600">
        Delivered Accounts
    </button>
</div>

{{-- Orders Table --}}
<div id="panel-orders">
@php $orderData = $orders['data'] ?? []; $orderPag = $orders['pagination'] ?? null; @endphp
<div class="bg-slate-800 border border-slate-700 rounded-xl">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-700 text-slate-400 text-xs uppercase">
                <th class="px-5 py-3 text-left">Order ID</th>
                <th class="px-5 py-3 text-left">Product</th>
                <th class="px-5 py-3 text-left">Source</th>
                <th class="px-5 py-3 text-left">Fulfillment</th>
                <th class="px-5 py-3 text-left">Qty</th>
                <th class="px-5 py-3 text-left">Unit Price</th>
                <th class="px-5 py-3 text-left">Total</th>
                <th class="px-5 py-3 text-left">Date</th>
                <th class="px-5 py-3 text-left">Accounts</th>
            </tr></thead>
            <tbody>
            @forelse($orderData as $order)
                <tr class="border-b border-slate-700/50 hover:bg-slate-700/30">
                    <td class="px-5 py-3 text-slate-300 font-mono text-xs">#{{ $order['id'] }}</td>
                    <td class="px-5 py-3 text-white font-medium max-w-[200px] truncate">{{ $order['product_name'] ?? '—' }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ ($order['source'] ?? '') === 'catalog' ? 'bg-sky-900/50 text-sky-400' : 'bg-purple-900/50 text-purple-400' }}">
                            {{ ucfirst($order['source'] ?? '—') }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        @php
                            $fulfillmentClass = match($order['status'] ?? '') {
                                'completed' => 'bg-green-900/50 text-green-400',
                                'pending' => 'bg-yellow-900/50 text-yellow-300',
                                'refunded' => 'bg-blue-900/50 text-blue-300',
                                default => 'bg-slate-700 text-slate-300',
                            };
                            $fulfillmentLabel = match($order['status'] ?? '') {
                                'completed' => 'Delivered',
                                'pending' => 'Needs review',
                                'refunded' => 'Refunded',
                                default => ucfirst($order['status'] ?? 'Unknown'),
                            };
                        @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $fulfillmentClass }}">{{ $fulfillmentLabel }}</span>
                    </td>
                    <td class="px-5 py-3 text-slate-300">{{ $order['quantity'] ?? '—' }}</td>
                    <td class="px-5 py-3 text-slate-300">₦{{ number_format($order['unit_price'] ?? 0, 2) }}</td>
                    <td class="px-5 py-3 text-green-400 font-semibold">₦{{ number_format($order['total_price'] ?? 0, 2) }}</td>
                    <td class="px-5 py-3 text-slate-400 text-xs">{{ isset($order['created_at']) ? \Carbon\Carbon::parse($order['created_at'])->format('M j, Y H:i') : '—' }}</td>
                    <td class="px-5 py-3">
                        <button onclick="viewOrderAccounts({{ $order['id'] }})"
                            class="text-xs px-2 py-1 rounded bg-slate-700 hover:bg-slate-600 text-slate-300 hover:text-white transition-colors">
                            View
                        </button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="px-5 py-10 text-center text-slate-500">
                    @if(!$configured) API key not configured. @else No marketplace API purchases found. @endif
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($orderPag)
    <div class="px-5 py-3 border-t border-slate-700 flex items-center justify-between text-xs text-slate-400">
        <span>{{ number_format($orderPag['total'] ?? 0) }} total &bull; page {{ $orderPag['current_page'] ?? 1 }} of {{ $orderPag['last_page'] ?? 1 }}</span>
        <div class="flex gap-2">
            @if(($orderPag['current_page'] ?? 1) > 1)
                <a href="{{ request()->fullUrlWithQuery(['page' => ($orderPag['current_page'] - 1)]) }}" class="px-3 py-1 rounded bg-slate-700 hover:bg-slate-600 text-slate-300">← Prev</a>
            @endif
            @if($orderPag['has_more'] ?? false)
                <a href="{{ request()->fullUrlWithQuery(['page' => ($orderPag['current_page'] + 1)]) }}" class="px-3 py-1 rounded bg-slate-700 hover:bg-slate-600 text-slate-300">Next →</a>
            @endif
        </div>
    </div>
    @endif
</div>
</div>

{{-- Accounts Table --}}
<div id="panel-accounts" style="display:none">
@php $accountData = $accounts['data'] ?? []; $accountPag = $accounts['pagination'] ?? null; @endphp
<div class="bg-slate-800 border border-slate-700 rounded-xl">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-700 text-slate-400 text-xs uppercase">
                <th class="px-5 py-3 text-left">ID</th>
                <th class="px-5 py-3 text-left">Product</th>
                <th class="px-5 py-3 text-left">Order ID</th>
                <th class="px-5 py-3 text-left">Status</th>
                <th class="px-5 py-3 text-left">Total Paid</th>
                <th class="px-5 py-3 text-left">Date</th>
                <th class="px-5 py-3 text-left">Credentials</th>
            </tr></thead>
            <tbody>
            @forelse($accountData as $acc)
                <tr class="border-b border-slate-700/50 hover:bg-slate-700/30">
                    <td class="px-5 py-3 text-slate-400 font-mono text-xs">#{{ $acc['id'] }}</td>
                    <td class="px-5 py-3 text-white max-w-[180px] truncate">{{ $acc['product_label'] ?? '—' }}</td>
                    <td class="px-5 py-3 text-slate-300 font-mono text-xs">#{{ $acc['order_id'] ?? '—' }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ ($acc['is_sold'] ?? false) ? 'bg-green-900/50 text-green-400' : (($acc['status'] ?? '') === 'pending' ? 'bg-yellow-900/50 text-yellow-300' : 'bg-slate-700 text-slate-400') }}">
                            {{ ($acc['is_sold'] ?? false) ? 'Delivered' : (($acc['status'] ?? '') === 'pending' ? 'Needs review' : ucfirst($acc['status'] ?? 'Unsold')) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-slate-300">₦{{ number_format($acc['order_total'] ?? 0, 2) }}</td>
                    <td class="px-5 py-3 text-slate-400 text-xs">{{ isset($acc['created_at']) ? \Carbon\Carbon::parse($acc['created_at'])->format('M j, Y H:i') : '—' }}</td>
                    <td class="px-5 py-3">
                        @if(!empty($acc['credentials']))
                            <button onclick="viewCredentials({{ $acc['id'] }}, {{ json_encode($acc['credentials']) }})"
                                class="text-xs px-2 py-1 rounded bg-slate-700 hover:bg-slate-600 text-slate-300 hover:text-white transition-colors">
                                View
                            </button>
                        @elseif(($acc['status'] ?? '') === 'pending')
                            <span class="text-xs text-yellow-300">Awaiting review</span>
                        @else
                            <span class="text-xs text-slate-500">No credentials</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-5 py-10 text-center text-slate-500">
                    @if(!$configured) API key not configured. @else No delivered keys found. @endif
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($accountPag)
    <div class="px-5 py-3 border-t border-slate-700 flex items-center justify-between text-xs text-slate-400">
        <span>{{ number_format($accountPag['total'] ?? 0) }} total &bull; page {{ $accountPag['current_page'] ?? 1 }} of {{ $accountPag['last_page'] ?? 1 }}</span>
        <div class="flex gap-2">
            @if(($accountPag['current_page'] ?? 1) > 1)
                <a href="{{ request()->fullUrlWithQuery(['page' => ($accountPag['current_page'] - 1), 'tab' => 'accounts']) }}" class="px-3 py-1 rounded bg-slate-700 hover:bg-slate-600 text-slate-300">← Prev</a>
            @endif
            @if($accountPag['has_more'] ?? false)
                <a href="{{ request()->fullUrlWithQuery(['page' => ($accountPag['current_page'] + 1), 'tab' => 'accounts']) }}" class="px-3 py-1 rounded bg-slate-700 hover:bg-slate-600 text-slate-300">Next →</a>
            @endif
        </div>
    </div>
    @endif
</div>
</div>

{{-- Credentials Modal --}}
<div id="creds-modal" class="fixed inset-0 z-50 items-center justify-center p-4" style="display:none; background:rgba(0,0,0,0.75)">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl w-full max-w-md shadow-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-700">
            <p class="text-white font-semibold text-sm" id="creds-modal-title">Account Credentials</p>
            <button onclick="document.getElementById('creds-modal').style.display='none'" class="text-slate-400 hover:text-white text-2xl leading-none">&times;</button>
        </div>
        <div class="px-6 py-4">
            <div class="bg-slate-900 border border-slate-700 rounded-xl p-4 relative">
                <pre id="creds-modal-body" class="text-green-300 text-xs font-mono whitespace-pre-wrap break-all leading-relaxed"></pre>
                <button onclick="copyModalCreds()"
                    class="absolute top-3 right-3 flex items-center gap-1 text-xs text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 border border-slate-600 rounded px-2 py-1 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    Copy
                </button>
            </div>
        </div>
        <div class="px-6 pb-5 flex justify-end">
            <button onclick="document.getElementById('creds-modal').style.display='none'"
                class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-sm rounded-lg transition-colors">Close</button>
        </div>
    </div>
</div>

<script>
// Tab switching
function switchTab(name) {
    ['orders','accounts'].forEach(t => {
        document.getElementById('panel-' + t).style.display   = t === name ? '' : 'none';
        const btn = document.getElementById('tab-' + t);
        btn.className = t === name
            ? 'tab-btn px-4 py-2 rounded-lg text-sm font-semibold transition-colors bg-sky-600 text-white'
            : 'tab-btn px-4 py-2 rounded-lg text-sm font-semibold transition-colors bg-slate-700 text-slate-300 hover:bg-slate-600';
    });
}
// Restore tab from URL
if (new URLSearchParams(location.search).get('tab') === 'accounts') switchTab('accounts');

// Show credentials in modal
function viewCredentials(id, creds) {
    document.getElementById('creds-modal-title').textContent = 'Account #' + id + ' — Credentials';
    document.getElementById('creds-modal-body').textContent  = creds || '(no credentials)';
    document.getElementById('creds-modal').style.display     = 'flex';
}

// Fetch accounts for an order
function viewOrderAccounts(orderId) {
    document.getElementById('creds-modal-title').textContent = 'Order #' + orderId + ' — Accounts';
    document.getElementById('creds-modal-body').textContent  = 'Loading…';
    document.getElementById('creds-modal').style.display     = 'flex';

    fetch('{{ route("admin.api-orders.order-accounts", ["orderId" => "__OID__"]) }}'.replace('__OID__', orderId))
        .then(r => r.json())
        .then(data => {
            if (!data || (Array.isArray(data) && data.length === 0)) {
                document.getElementById('creds-modal-body').textContent = '(no accounts found for this order)';
                return;
            }
            const accounts = Array.isArray(data) ? data : [data];
            document.getElementById('creds-modal-body').textContent =
                accounts.map((a, i) => `Account ${i + 1} (ID #${a.id}):\n${a.credentials || '—'}`).join('\n\n---\n\n');
        })
        .catch(() => {
            document.getElementById('creds-modal-body').textContent = 'Failed to load accounts.';
        });
}

function copyModalCreds() {
    navigator.clipboard.writeText(document.getElementById('creds-modal-body').textContent);
}
</script>
@endsection
