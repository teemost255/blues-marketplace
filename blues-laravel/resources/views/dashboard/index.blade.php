@extends('layouts.dashboard')
@section('title', 'Dashboard')
@section('page-title', 'Overview')

@section('content')
{{-- Stats row --}}
<div class="grid grid-cols-2 gap-4 mb-8">
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 sm:p-5 overflow-hidden">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Wallet Balance</p>
        <p class="text-lg sm:text-2xl font-bold text-white mt-1 truncate">₦{{ number_format($wallet->balance, 2) }}</p>
        <a href="{{ route('dashboard.wallet') }}" class="text-xs text-brand hover:underline mt-1 inline-block">Top up →</a>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 sm:p-5 overflow-hidden">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Total Orders</p>
        <p class="text-lg sm:text-2xl font-bold text-white mt-1 truncate">{{ $orderCount }}</p>
    </div>
</div>

{{-- Quick actions --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8">
    <a href="{{ route('dashboard.marketplace') }}" class="bg-slate-800 border border-slate-700 hover:border-brand/50 rounded-xl p-4 flex items-center gap-3 transition-all group">
        <div class="w-9 h-9 bg-brand/10 rounded-lg flex items-center justify-center group-hover:bg-brand/20 transition-colors">
            <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <span class="text-sm font-medium text-white">Marketplace</span>
    </a>
    <a href="{{ route('dashboard.orders') }}" class="bg-slate-800 border border-slate-700 hover:border-brand/50 rounded-xl p-4 flex items-center gap-3 transition-all group">
        <div class="w-9 h-9 bg-purple-500/10 rounded-lg flex items-center justify-center group-hover:bg-purple-500/20 transition-colors">
            <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        </div>
        <span class="text-sm font-medium text-white">My Orders</span>
    </a>
    <a href="{{ route('dashboard.wallet') }}" class="bg-slate-800 border border-slate-700 hover:border-brand/50 rounded-xl p-4 flex items-center gap-3 transition-all group">
        <div class="w-9 h-9 bg-yellow-500/10 rounded-lg flex items-center justify-center group-hover:bg-yellow-500/20 transition-colors">
            <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        </div>
        <span class="text-sm font-medium text-white">Top Up</span>
    </a>
</div>

{{-- Referral banner (only if user has a referral code) --}}
@php $dashProfile = Auth::user()->profile; @endphp
@if($dashProfile && $dashProfile->referral_code)
<div class="bg-gradient-to-r from-brand/10 to-purple-500/10 border border-brand/20 rounded-xl p-5 mb-8 flex flex-col sm:flex-row sm:items-center gap-4">
    <div class="flex items-center gap-3 flex-1 min-w-0">
        <div class="w-10 h-10 rounded-xl bg-brand/15 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
        <div class="min-w-0">
            <p class="text-white text-sm font-semibold">Refer friends &amp; earn bonuses</p>
            <p class="text-slate-400 text-xs mt-0.5 truncate">Your code: <span class="text-brand font-mono font-bold">{{ $dashProfile->referral_code }}</span></p>
        </div>
    </div>
    <div class="flex items-center gap-2 flex-shrink-0">
        <input id="dash-ref-link" type="text" readonly value="{{ url('/r/' . $dashProfile->referral_code) }}"
            class="hidden-input w-1 h-1 opacity-0 absolute" tabindex="-1">
        <button onclick="copyDashReferral()" id="dash-copy-btn"
            class="bg-brand hover:bg-brand-dark text-white text-xs font-bold px-4 py-2 rounded-lg transition-colors flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
            Copy Link
        </button>
        <a href="{{ route('dashboard.profile') }}" class="border border-slate-600 hover:border-brand text-slate-300 hover:text-white text-xs font-semibold px-4 py-2 rounded-lg transition-colors">Details</a>
    </div>
</div>
@endif

{{-- Recent Orders --}}
<div class="bg-slate-800 border border-slate-700 rounded-xl">
    <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
        <h2 class="font-semibold text-white">Recent Orders</h2>
        <a href="{{ route('dashboard.orders') }}" class="text-xs text-brand hover:underline">View all →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-700 text-slate-400 text-xs uppercase">
                <th class="px-6 py-3 text-left">Item</th>
                <th class="px-6 py-3 text-left">Amount</th>
                <th class="px-6 py-3 text-left">Status</th>
                <th class="px-6 py-3 text-left">Date</th>
            </tr></thead>
            <tbody>
            @forelse($recentOrders as $order)
                <tr class="border-b border-slate-700/50">
                    <td class="px-6 py-3 text-slate-300">{{ $order->listing?->title ?? 'Deleted listing' }}</td>
                    <td class="px-6 py-3 text-white font-medium">₦{{ number_format($order->amount, 2) }}</td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $order->status === 'completed' ? 'bg-green-900/50 text-green-400' : 'bg-yellow-900/50 text-yellow-400' }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-slate-400">{{ $order->created_at->diffForHumans() }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-6 py-10 text-center text-slate-500">No orders yet — <a href="{{ route('dashboard.marketplace') }}" class="text-brand hover:underline">browse social media logs</a></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyDashReferral() {
    const val = document.getElementById('dash-ref-link').value;
    const btn = document.getElementById('dash-copy-btn');
    function markDone() {
        btn.textContent = '✓ Copied!';
        btn.classList.add('bg-green-600');
        btn.classList.remove('bg-brand', 'hover:bg-brand-dark');
        setTimeout(() => {
            btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg> Copy Link';
            btn.classList.remove('bg-green-600');
            btn.classList.add('bg-brand', 'hover:bg-brand-dark');
        }, 2200);
    }
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(val).then(markDone).catch(() => _fbCopy(val, markDone));
    } else {
        _fbCopy(val, markDone);
    }
}
function _fbCopy(text, cb) {
    const ta = document.createElement('textarea');
    ta.value = text; ta.style.cssText = 'position:fixed;top:0;left:0;opacity:0;pointer-events:none;';
    document.body.appendChild(ta); ta.focus(); ta.select();
    try { document.execCommand('copy'); if (cb) cb(); } catch(e) {}
    document.body.removeChild(ta);
}
</script>
@endpush
