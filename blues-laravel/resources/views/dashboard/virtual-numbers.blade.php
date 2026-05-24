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

{{-- Balance bar --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-lg font-semibold text-white">Get a Virtual Number</h2>
        <p class="text-sm text-slate-400 mt-0.5">Receive SMS codes for any service, charged from your wallet</p>
    </div>
    <div class="flex items-center gap-3 bg-slate-800 border border-slate-700 rounded-xl px-5 py-3">
        <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
        <div>
            <p class="text-xs text-slate-400">Wallet Balance</p>
            <p class="text-white font-bold">₦{{ number_format($wallet->balance, 2) }}</p>
        </div>
        <a href="{{ route('dashboard.wallet') }}" class="ml-3 text-xs text-brand hover:text-sky-300">Top up →</a>
    </div>
</div>

@if($apiError)
<div class="mb-5 p-4 bg-yellow-900/30 border border-yellow-700/50 rounded-lg text-yellow-300 text-sm flex items-center gap-2">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    API: {{ $apiError }}
</div>
@endif

{{-- Order Form --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2 bg-slate-800 border border-slate-700 rounded-xl p-6">
        <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01"/></svg>
            Order a Number
        </h3>
        <form method="POST" action="{{ route('dashboard.virtual-numbers.order') }}" id="order-form">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Country</label>
                    <select name="country" id="country-select"
                        class="w-full bg-slate-700 border border-slate-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand"
                        onchange="this.form.submit()">
                        @forelse($countries as $c)
                            @php
                                $code = $c['code'] ?? $c['iso'] ?? $c['id'] ?? '';
                                $name = $c['name'] ?? $c['title'] ?? $code;
                            @endphp
                            <option value="{{ $code }}" {{ request('country', 'ng') === $code ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @empty
                            <option value="ng">Nigeria</option>
                            <option value="us">United States</option>
                            <option value="gb">United Kingdom</option>
                            <option value="in">India</option>
                        @endforelse
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Service / App</label>
                    <select name="service" id="service-select"
                        class="w-full bg-slate-700 border border-slate-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand">
                        @forelse($services as $s)
                            @php
                                $id   = $s['id'] ?? $s['code'] ?? $s['slug'] ?? '';
                                $name = $s['name'] ?? $s['title'] ?? $id;
                                $price = isset($s['price']) ? '₦' . number_format($s['price'], 2) : '';
                            @endphp
                            <option value="{{ $id }}">{{ $name }}{{ $price ? ' — ' . $price : '' }}</option>
                        @empty
                            <option value="whatsapp">WhatsApp</option>
                            <option value="telegram">Telegram</option>
                            <option value="facebook">Facebook</option>
                            <option value="instagram">Instagram</option>
                            <option value="tiktok">TikTok</option>
                            <option value="twitter">Twitter / X</option>
                            <option value="google">Google</option>
                        @endforelse
                    </select>
                </div>
            </div>

            <div class="p-3 bg-slate-700/50 border border-slate-600/50 rounded-lg text-sm text-slate-300 mb-4">
                <svg class="w-4 h-4 inline text-brand mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                The cost will be deducted from your wallet. Numbers are valid for a limited time to receive one SMS code.
            </div>

            <button type="submit" id="order-btn"
                class="w-full py-2.5 bg-brand hover:bg-brand-dark text-white font-semibold rounded-lg text-sm transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                Get Virtual Number
            </button>
        </form>
    </div>

    {{-- Tips --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
        <h3 class="font-semibold text-white mb-4">How it works</h3>
        <ol class="space-y-4">
            @foreach([
                ['icon' => '1', 'title' => 'Select service', 'desc' => 'Choose the app you need the number for and your preferred country.'],
                ['icon' => '2', 'title' => 'Get number', 'desc' => 'A virtual number is assigned and the cost is deducted from your wallet.'],
                ['icon' => '3', 'title' => 'Receive SMS', 'desc' => 'Enter the number in the app, then click "Check SMS" to see the code.'],
                ['icon' => '4', 'title' => 'Done', 'desc' => 'Use the code to verify. Cancel unused numbers for a full refund.'],
            ] as $step)
            <li class="flex gap-3">
                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-brand text-white text-xs font-bold flex items-center justify-center">{{ $step['icon'] }}</span>
                <div>
                    <p class="text-sm font-medium text-white">{{ $step['title'] }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $step['desc'] }}</p>
                </div>
            </li>
            @endforeach
        </ol>
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
                        <span class="text-slate-400 text-xs ml-1 uppercase">({{ $order->country }})</span>
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
                        <div class="flex items-center gap-2">
                            @if($order->status === 'active')
                                <button onclick="checkSms({{ $order->id }})"
                                    class="text-xs px-2.5 py-1 bg-brand/10 hover:bg-brand/20 text-brand border border-brand/30 rounded-lg transition-colors">
                                    Check SMS
                                </button>
                                <form method="POST" action="{{ route('dashboard.virtual-numbers.cancel', $order->id) }}"
                                    onsubmit="return confirm('Cancel this order? You will be refunded.')">
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
async function checkSms(orderId) {
    const btn = event.target;
    btn.textContent = 'Checking…';
    btn.disabled = true;

    try {
        const res = await fetch(`/dashboard/virtual-numbers/${orderId}/sms`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();

        if (data.success) {
            const codeEl = document.getElementById('sms-' + orderId);
            const statusEl = document.getElementById('status-' + orderId);

            if (data.sms_code) {
                codeEl.textContent = data.sms_code;
                codeEl.classList.add('animate-pulse');
                setTimeout(() => codeEl.classList.remove('animate-pulse'), 2000);
            }

            if (data.status === 'completed') {
                statusEl.textContent = 'Completed';
                statusEl.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-xs border bg-green-900/50 text-green-300 border-green-700/50';
                btn.closest('.flex').innerHTML = '';
            } else if (!data.sms_code) {
                btn.textContent = 'No SMS yet';
                btn.disabled = false;
                setTimeout(() => { btn.textContent = 'Check SMS'; }, 3000);
            }
        } else {
            alert(data.message || 'Could not check SMS.');
            btn.textContent = 'Check SMS';
            btn.disabled = false;
        }
    } catch (e) {
        alert('Network error. Please try again.');
        btn.textContent = 'Check SMS';
        btn.disabled = false;
    }
}

document.getElementById('order-btn')?.addEventListener('click', function() {
    this.textContent = 'Ordering…';
    this.disabled = true;
});
</script>
@endsection
