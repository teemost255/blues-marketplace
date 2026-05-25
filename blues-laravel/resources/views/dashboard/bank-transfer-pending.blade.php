@extends('layouts.dashboard')
@section('title','Bank Transfer — Pending')
@section('page-title','Bank Transfer')

@section('content')

@if(session('success'))
<div class="mb-6 bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 flex items-center gap-2 text-sm">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    {{ session('success') }}
</div>
@endif

<div class="max-w-lg mx-auto">
    {{-- Status Card --}}
    <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 mb-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-xl bg-yellow-500/20 flex items-center justify-center">
                <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h2 class="font-bold text-white text-lg">
                    @if($btp->status === 'confirmed') Payment Confirmed
                    @elseif($btp->status === 'rejected') Payment Rejected
                    @else Awaiting Payment Confirmation
                    @endif
                </h2>
                <p class="text-xs text-slate-400">Reference: <span class="font-mono text-slate-300">{{ $btp->reference }}</span></p>
            </div>
        </div>

        @if($btp->status === 'confirmed')
            <div class="bg-green-500/10 border border-green-500/30 rounded-xl px-4 py-3 text-green-400 text-sm">
                Your payment has been confirmed by the admin. Check your orders or wallet for the result.
            </div>
        @elseif($btp->status === 'rejected')
            <div class="bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 text-red-400 text-sm">
                Your payment was rejected. {{ $btp->admin_note ? 'Reason: '.$btp->admin_note : '' }} Please contact support.
            </div>
        @else
            {{-- Bank Details --}}
            <div class="space-y-3 mb-5">
                <h3 class="text-sm font-semibold text-slate-300 mb-2">Transfer to this account:</h3>
                @php
                    $bankName  = \App\Models\Setting::get('bank_name', '—');
                    $accNumber = \App\Models\Setting::get('bank_account_number', '—');
                    $accName   = \App\Models\Setting::get('bank_account_name', '—');
                @endphp
                <div class="bg-slate-900 border border-slate-700 rounded-xl p-4 space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 text-sm">Bank</span>
                        <span class="text-white font-semibold">{{ $bankName }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 text-sm">Account Number</span>
                        <div class="flex items-center gap-2">
                            <span class="text-white font-mono font-semibold" id="acc-num">{{ $accNumber }}</span>
                            <button onclick="copyText('{{ $accNumber }}', this)" class="text-slate-500 hover:text-brand transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 text-sm">Account Name</span>
                        <span class="text-white font-semibold">{{ $accName }}</span>
                    </div>
                    <div class="border-t border-slate-700 pt-3 flex justify-between items-center">
                        <span class="text-slate-400 text-sm">Amount to Send</span>
                        <span class="text-2xl font-extrabold text-white">₦{{ number_format($btp->amount, 2) }}</span>
                    </div>
                </div>

                <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl px-4 py-3 text-yellow-300 text-xs">
                    <strong>Important:</strong> Use reference <span class="font-mono font-bold">{{ $btp->reference }}</span> as your payment description/narration so we can identify your transfer quickly.
                </div>
            </div>

            {{-- I Have Paid button --}}
            <form method="POST" action="{{ route('dashboard.bank-transfer.paid', $btp->id) }}">
                @csrf
                <button type="submit" class="w-full bg-brand hover:bg-brand-dark text-white font-bold py-3.5 rounded-xl text-base transition-colors">
                    I Have Paid — Notify Admin
                </button>
            </form>
            <p class="text-xs text-slate-500 text-center mt-2">Click after making the transfer. Admin will confirm within a short time.</p>
        @endif
    </div>

    <div class="text-center">
        @if($btp->type === 'wallet_topup')
            <a href="{{ route('dashboard.wallet') }}" class="text-sm text-brand hover:underline">← Back to Wallet</a>
        @else
            <a href="{{ route('dashboard.marketplace') }}" class="text-sm text-brand hover:underline">← Continue Shopping</a>
        @endif
    </div>
</div>

<script>
function copyText(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = '<svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
        setTimeout(() => btn.innerHTML = orig, 1500);
    });
}
</script>
@endsection
