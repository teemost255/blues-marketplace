@extends('layouts.dashboard')
@section('title', 'Wallet')
@section('page-title', 'My Wallet')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    {{-- Balance card --}}
    <div class="lg:col-span-1 bg-gradient-to-br from-brand to-brand-dark rounded-2xl p-6 text-white">
        <p class="text-sky-100 text-sm font-medium mb-1">Available Balance</p>
        <p class="text-4xl font-extrabold">${{ number_format($wallet->balance, 2) }}</p>
        <p class="text-sky-200 text-xs mt-3">Funds ready to use in the marketplace</p>
    </div>

    {{-- Top up form --}}
    <div class="lg:col-span-2 bg-slate-800 border border-slate-700 rounded-2xl p-6">
        <h2 class="font-semibold text-white mb-4">Top Up Wallet</h2>
        <form method="POST" action="{{ route('dashboard.wallet.deposit') }}" class="flex flex-col sm:flex-row gap-3">
            @csrf
            <div class="flex-1">
                <label class="block text-xs text-slate-400 mb-1.5">Amount (USD)</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-medium">$</span>
                    <input type="number" name="amount" min="1" max="10000" step="0.01" placeholder="0.00" required
                        class="w-full bg-slate-900 border border-slate-600 rounded-lg pl-7 pr-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-brand text-sm @error('amount') border-red-500 @enderror">
                </div>
                @error('amount')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-brand hover:bg-brand-dark text-white font-semibold px-6 py-3 rounded-lg text-sm transition-colors whitespace-nowrap">Add Funds</button>
            </div>
        </form>
        <div class="flex gap-2 mt-3">
            @foreach([10, 25, 50, 100] as $preset)
            <button type="button" onclick="document.querySelector('[name=amount]').value='{{ $preset }}'" class="bg-slate-700 hover:bg-slate-600 text-slate-300 text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">${{ $preset }}</button>
            @endforeach
        </div>
    </div>
</div>

{{-- Transaction history --}}
<div class="bg-slate-800 border border-slate-700 rounded-xl">
    <div class="px-6 py-4 border-b border-slate-700">
        <h2 class="font-semibold text-white">Transaction History</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-700 text-slate-400 text-xs uppercase">
                <th class="px-6 py-3 text-left">Description</th>
                <th class="px-6 py-3 text-left">Type</th>
                <th class="px-6 py-3 text-left">Amount</th>
                <th class="px-6 py-3 text-left">Date</th>
            </tr></thead>
            <tbody>
            @forelse($transactions as $tx)
                <tr class="border-b border-slate-700/50">
                    <td class="px-6 py-3 text-slate-300">{{ $tx->description ?? ($tx->reference ?? '—') }}</td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $tx->type === 'deposit' ? 'bg-green-900/50 text-green-400' : ($tx->type === 'purchase' ? 'bg-red-900/50 text-red-400' : 'bg-slate-700 text-slate-300') }}">
                            {{ ucfirst($tx->type) }}
                        </span>
                    </td>
                    <td class="px-6 py-3 font-semibold {{ $tx->amount >= 0 ? 'text-green-400' : 'text-red-400' }}">
                        {{ $tx->amount >= 0 ? '+' : '' }}${{ number_format(abs($tx->amount), 2) }}
                    </td>
                    <td class="px-6 py-3 text-slate-400">{{ $tx->created_at->format('M j, Y g:ia') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-6 py-10 text-center text-slate-500">No transactions yet</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())
        <div class="px-6 py-4 border-t border-slate-700">{{ $transactions->links() }}</div>
    @endif
</div>
@endsection
