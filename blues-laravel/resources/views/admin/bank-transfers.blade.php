@extends('layouts.admin')
@section('title','Bank Transfers')
@section('page-title','Bank Transfers')
@section('content')

@if(session('success'))
<div class="mb-4 bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 text-sm">{{ session('error') }}</div>
@endif

{{-- Pending --}}
<div class="mb-8">
    <h2 class="text-lg font-semibold text-white mb-3 flex items-center gap-2">
        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-yellow-500/20 text-yellow-400 text-xs font-bold">{{ $pending->count() }}</span>
        Pending Confirmations
    </h2>
    <div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-slate-700 text-slate-400 text-xs uppercase">
                    <th class="px-5 py-3 text-left">User</th>
                    <th class="px-5 py-3 text-left">Type</th>
                    <th class="px-5 py-3 text-left">Item / Details</th>
                    <th class="px-5 py-3 text-left">Amount</th>
                    <th class="px-5 py-3 text-left">Reference</th>
                    <th class="px-5 py-3 text-left">Date</th>
                    <th class="px-5 py-3 text-left">Actions</th>
                </tr></thead>
                <tbody>
                @forelse($pending as $btp)
                    <tr class="border-b border-slate-700/50 hover:bg-slate-700/30">
                        <td class="px-5 py-3">
                            <p class="text-slate-200 font-medium">{{ $btp->user->name ?? '—' }}</p>
                            <p class="text-slate-500 text-xs">{{ $btp->user->email ?? '' }}</p>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $btp->type === 'wallet_topup' ? 'bg-blue-900/50 text-blue-400' : 'bg-purple-900/50 text-purple-400' }}">
                                {{ $btp->type === 'wallet_topup' ? 'Wallet Top-up' : 'Marketplace' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-slate-400">
                            {{ $btp->listing?->title ?? ($btp->type === 'wallet_topup' ? 'Wallet credit' : '—') }}
                        </td>
                        <td class="px-5 py-3 text-green-400 font-semibold">₦{{ number_format($btp->amount, 2) }}</td>
                        <td class="px-5 py-3 font-mono text-xs text-slate-300">{{ $btp->reference }}</td>
                        <td class="px-5 py-3 text-slate-400 text-xs">{{ $btp->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('admin.bank-transfers.confirm', $btp->id) }}">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Confirm this payment and process it?')"
                                        class="px-3 py-1.5 bg-green-600 hover:bg-green-500 text-white text-xs font-semibold rounded-lg transition-colors">
                                        Confirm
                                    </button>
                                </form>
                                <button type="button" onclick="openReject({{ $btp->id }})"
                                    class="px-3 py-1.5 bg-red-900/50 hover:bg-red-600 text-red-400 hover:text-white text-xs font-semibold rounded-lg transition-colors border border-red-700/50">
                                    Reject
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-8 text-center text-slate-500">No pending bank transfers</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- History --}}
<div>
    <h2 class="text-lg font-semibold text-white mb-3">Recent Processed Transfers</h2>
    <div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-slate-700 text-slate-400 text-xs uppercase">
                    <th class="px-5 py-3 text-left">User</th>
                    <th class="px-5 py-3 text-left">Type</th>
                    <th class="px-5 py-3 text-left">Amount</th>
                    <th class="px-5 py-3 text-left">Reference</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left">Date</th>
                </tr></thead>
                <tbody>
                @forelse($processed as $btp)
                    <tr class="border-b border-slate-700/50 hover:bg-slate-700/30">
                        <td class="px-5 py-3">
                            <p class="text-slate-200">{{ $btp->user->name ?? '—' }}</p>
                            <p class="text-slate-500 text-xs">{{ $btp->user->email ?? '' }}</p>
                        </td>
                        <td class="px-5 py-3 text-slate-400 text-xs">{{ $btp->type === 'wallet_topup' ? 'Wallet Top-up' : 'Marketplace' }}</td>
                        <td class="px-5 py-3 text-slate-200 font-medium">₦{{ number_format($btp->amount, 2) }}</td>
                        <td class="px-5 py-3 font-mono text-xs text-slate-400">{{ $btp->reference }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $btp->status === 'confirmed' ? 'bg-green-900/50 text-green-400' : 'bg-red-900/50 text-red-400' }}">
                                {{ ucfirst($btp->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-slate-400 text-xs">{{ $btp->updated_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-slate-500">No processed transfers yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div id="reject-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-bold text-white mb-3">Reject Transfer</h3>
        <p class="text-sm text-slate-400 mb-4">Optionally add a note explaining why this transfer is being rejected. The user will be notified.</p>
        <form id="reject-form" method="POST" action="">
            @csrf
            <div class="mb-4">
                <label class="block text-xs text-slate-400 mb-1.5">Admin Note (optional)</label>
                <textarea name="note" rows="3" placeholder="e.g. Payment not received, wrong reference..."
                    class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2.5 text-white text-sm placeholder-slate-500 focus:outline-none focus:border-red-500 resize-none"></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-red-600 hover:bg-red-500 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors">Reject</button>
                <button type="button" onclick="closeReject()" class="flex-1 border border-slate-600 text-slate-400 hover:text-white font-semibold py-2.5 rounded-lg text-sm transition-colors">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openReject(id) {
    document.getElementById('reject-form').action = '/admin/bank-transfers/' + id + '/reject';
    const modal = document.getElementById('reject-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeReject() {
    const modal = document.getElementById('reject-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endsection
