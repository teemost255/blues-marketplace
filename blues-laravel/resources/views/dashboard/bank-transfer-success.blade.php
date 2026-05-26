@extends('layouts.dashboard')
@section('title','Payment Confirmed')
@section('page-title','Payment Confirmed')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl p-8 text-center">
        {{-- Success Icon --}}
        <div class="w-20 h-20 rounded-full bg-green-500/20 flex items-center justify-center mx-auto mb-5">
            <svg class="w-10 h-10 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <h2 class="text-2xl font-bold text-white mb-2">Payment Confirmed!</h2>
        <p class="text-slate-400 text-sm mb-1">Reference: <span class="font-mono text-slate-300">{{ $btp->reference }}</span></p>
        <p class="text-slate-400 text-sm mb-6">Amount: <span class="font-bold text-white">₦{{ number_format($btp->amount, 2) }}</span></p>

        @if($btp->type === 'wallet_topup')
            <div class="bg-green-500/10 border border-green-500/30 rounded-xl px-4 py-4 text-green-300 text-sm mb-6">
                <p class="font-semibold mb-1">₦{{ number_format($btp->amount, 2) }} has been credited to your wallet.</p>
                <p class="text-green-400/80">Your wallet balance has been updated and is ready to use.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('dashboard.wallet') }}"
                   class="bg-brand hover:bg-brand-dark text-white font-semibold px-6 py-3 rounded-xl transition-colors text-sm">
                    View Wallet
                </a>
                <a href="{{ route('dashboard.marketplace') }}"
                   class="bg-slate-700 hover:bg-slate-600 text-white font-semibold px-6 py-3 rounded-xl transition-colors text-sm">
                    Go to Marketplace
                </a>
            </div>
        @else
            <div class="bg-green-500/10 border border-green-500/30 rounded-xl px-4 py-4 text-green-300 text-sm mb-6">
                <p class="font-semibold mb-1">Your purchase has been completed.</p>
                <p class="text-green-400/80">Check My Orders to view your login details.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('dashboard.orders') }}"
                   class="bg-brand hover:bg-brand-dark text-white font-semibold px-6 py-3 rounded-xl transition-colors text-sm">
                    View My Orders
                </a>
                <a href="{{ route('dashboard.marketplace') }}"
                   class="bg-slate-700 hover:bg-slate-600 text-white font-semibold px-6 py-3 rounded-xl transition-colors text-sm">
                    Continue Shopping
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
