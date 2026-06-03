@extends('layouts.dashboard')
@section('title', 'Wallet')
@section('page-title', 'My Wallet')

@section('content')

{{-- Flash messages --}}
@if(session('success'))
<div class="mb-6 bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 flex items-center gap-2 text-sm">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-6 bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 text-sm">{{ session('error') }}</div>
@endif
@if(session('info'))
<div class="mb-6 bg-brand/10 border border-brand/30 text-brand rounded-xl px-4 py-3 text-sm">{{ session('info') }}</div>
@endif

{{-- Summary stats --}}
<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 overflow-hidden">
        <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Total Funded</p>
        <p class="text-base sm:text-xl font-bold text-green-400 truncate">₦{{ number_format($summary['total_deposited'], 2) }}</p>
        <p class="text-xs text-slate-500 mt-0.5">All-time deposits</p>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 overflow-hidden">
        <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Referral Earned</p>
        <p class="text-base sm:text-xl font-bold text-purple-400 truncate">₦{{ number_format($summary['referral_earned'], 2) }}</p>
        <p class="text-xs text-slate-500 mt-0.5">Bonus credited</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    {{-- Balance card --}}
    <div class="lg:col-span-1 bg-gradient-to-br from-brand to-brand-dark rounded-2xl p-6 text-white relative overflow-hidden">
        <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/5"></div>
        <div class="absolute -right-4 bottom-4 w-20 h-20 rounded-full bg-white/5"></div>
        <div class="relative">
            <div class="flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-sky-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                <p class="text-sky-100 text-sm font-medium">Available Balance</p>
            </div>
            <p class="text-2xl sm:text-4xl font-extrabold tracking-tight break-all">₦{{ number_format($wallet->balance, 2) }}</p>
            <p class="text-sky-200 text-xs mt-3">Funds ready to use in the marketplace</p>
        </div>
    </div>

    {{-- Paystack Top-up --}}
    <div class="lg:col-span-2 bg-slate-800 border border-slate-700 rounded-2xl p-6">
        <div class="flex items-center gap-2 mb-5">
            <div class="w-8 h-8 rounded-lg bg-brand/10 flex items-center justify-center">
                <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </div>
            <h2 class="font-semibold text-white">Fund Wallet via Paystack</h2>
        </div>

        @if($paystackPublicKey)
        <form method="POST" action="{{ route('dashboard.wallet.initiate') }}" id="topup-form">
            @csrf
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <label class="block text-xs text-slate-400 mb-1.5">Amount (₦)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-medium text-sm">₦</span>
                        <input type="number" name="amount" id="amount-input"
                            min="{{ $minDeposit }}" max="{{ $maxDeposit }}" step="1"
                            placeholder="{{ number_format($minDeposit, 0) }}" required
                            class="w-full bg-slate-900 border border-slate-600 rounded-lg pl-8 pr-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-brand text-sm @error('amount') border-red-500 @enderror">
                    </div>
                    @error('amount')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-slate-500 text-xs mt-1">Min: ₦{{ number_format($minDeposit, 0) }} · Max: ₦{{ number_format($maxDeposit, 0) }}</p>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-brand hover:bg-brand-dark text-white font-semibold px-6 py-3 rounded-lg text-sm transition-colors whitespace-nowrap flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Pay with Paystack
                    </button>
                </div>
            </div>
            {{-- Quick amounts --}}
            <div class="flex flex-wrap gap-2 mt-3">
                @foreach([500, 1000, 2000, 5000, 10000] as $preset)
                <button type="button" onclick="document.getElementById('amount-input').value='{{ $preset }}'"
                    class="bg-slate-700 hover:bg-brand/20 hover:border-brand/40 border border-transparent text-slate-300 hover:text-brand text-xs font-medium px-3 py-1.5 rounded-lg transition-all">
                    ₦{{ number_format($preset, 0) }}
                </button>
                @endforeach
            </div>
        </form>

        <div class="mt-4 pt-4 border-t border-slate-700 flex items-center gap-2 text-xs text-slate-500">
            <svg class="w-3.5 h-3.5 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            Secured by Paystack · Card, Bank Transfer, USSD accepted · Balance credited instantly
        </div>
        @else
        <div class="bg-yellow-500/10 border border-yellow-500/30 text-yellow-400 rounded-xl px-4 py-4 text-sm flex items-start gap-2">
            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Payment gateway is not configured yet. Please contact support.</span>
        </div>
        @endif
    </div>
</div>

@php $btEnabled = \App\Models\Setting::get('bank_transfer_enabled','0') === '1'; @endphp
@if($btEnabled)
<div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 mb-8">
    <div class="flex items-center gap-2 mb-5">
        <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center">
            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
        </div>
        <h2 class="font-semibold text-white">Fund Wallet via Bank Transfer</h2>
    </div>
    <form method="POST" action="{{ route('dashboard.wallet.bank-transfer') }}">
        @csrf
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <label class="block text-xs text-slate-400 mb-1.5">Amount to Transfer (₦)</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-medium text-sm">₦</span>
                    <input type="number" name="amount" min="{{ $minDeposit }}" step="1"
                        placeholder="{{ number_format($minDeposit, 0) }}" required
                        class="w-full bg-slate-900 border border-slate-600 rounded-lg pl-8 pr-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 text-sm">
                </div>
                <p class="text-slate-500 text-xs mt-1">Min: ₦{{ number_format($minDeposit, 0) }} · Admin confirms manually</p>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-semibold px-6 py-3 rounded-lg text-sm transition-colors whitespace-nowrap flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    Get Bank Details
                </button>
            </div>
        </div>
    </form>
    <p class="text-xs text-slate-500 mt-4">You will receive our bank account details and a reference code. Transfer the exact amount and click "I Have Paid". Your wallet will be credited after admin verification.</p>
</div>
@endif

{{-- Transaction history --}}
<div class="bg-slate-800 border border-slate-700 rounded-xl">
    <div class="px-6 py-4 border-b border-slate-700 flex flex-col sm:flex-row sm:items-center gap-3 justify-between">
        <h2 class="font-semibold text-white">Transaction History <span class="text-slate-500 font-normal text-sm">({{ $transactions->total() }})</span></h2>
        <div class="flex items-center gap-1 flex-wrap">
            @foreach([
                ['key' => 'all',           'label' => 'All'],
                ['key' => 'deposit',       'label' => 'Deposits'],
                ['key' => 'purchase',      'label' => 'Purchases'],
                ['key' => 'withdrawal',    'label' => 'Withdrawals'],
                ['key' => 'referral_bonus','label' => 'Referral'],
                ['key' => 'refund',        'label' => 'Refunds'],
            ] as $tab)
            <a href="{{ route('dashboard.wallet', ['type' => $tab['key']]) }}"
                class="px-3 py-1 rounded-lg text-xs font-medium transition-colors
                {{ ($activeType ?? 'all') === $tab['key'] ? 'bg-brand text-white' : 'bg-slate-700 text-slate-400 hover:text-white hover:bg-slate-600' }}">
                {{ $tab['label'] }}
            </a>
            @endforeach
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-700 text-slate-400 text-xs uppercase">
                <th class="px-6 py-3 text-left">Description</th>
                <th class="px-6 py-3 text-left">Reference</th>
                <th class="px-6 py-3 text-left">Type</th>
                <th class="px-6 py-3 text-left">Amount</th>
                <th class="px-6 py-3 text-left">Date</th>
            </tr></thead>
            <tbody>
            @forelse($transactions as $tx)
                <tr class="border-b border-slate-700/50 hover:bg-slate-700/20 transition-colors">
                    <td class="px-6 py-3 text-slate-300 max-w-[200px] truncate">{{ $tx->description ?? '—' }}</td>
                    <td class="px-6 py-3 text-slate-500 font-mono text-xs">{{ $tx->reference ?? '—' }}</td>
                    <td class="px-6 py-3">
                        @php
                            $txColors = [
                                'deposit'       => 'bg-green-900/50 text-green-400',
                                'purchase'      => 'bg-red-900/50 text-red-400',
                                'withdrawal'    => 'bg-red-900/50 text-red-400',
                                'refund'        => 'bg-blue-900/50 text-blue-400',
                                'referral_bonus'=> 'bg-purple-900/50 text-purple-400',
                                'admin_credit'  => 'bg-yellow-900/50 text-yellow-400',
                            ];
                        @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $txColors[$tx->type] ?? 'bg-slate-700 text-slate-300' }}">
                            {{ ucfirst(str_replace('_', ' ', $tx->type)) }}
                        </span>
                    </td>
                    @php
                        $isCredit = in_array($tx->type, ['deposit','refund','referral_bonus','admin_credit']);
                    @endphp
                    <td class="px-6 py-3 font-semibold {{ $isCredit ? 'text-green-400' : 'text-red-400' }}">
                        {{ $isCredit ? '+' : '-' }}₦{{ number_format(abs($tx->amount), 2) }}
                    </td>
                    <td class="px-6 py-3 text-slate-400">{{ $tx->created_at->format('M j, Y g:ia') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <svg class="w-10 h-10 text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <p class="text-slate-500 text-sm">No transactions yet</p>
                        <p class="text-slate-600 text-xs mt-1">Fund your wallet to get started</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())
        <div class="px-6 py-4 border-t border-slate-700">{{ $transactions->links() }}</div>
    @endif
</div>
@endsection
