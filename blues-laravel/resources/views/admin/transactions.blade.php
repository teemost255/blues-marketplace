@extends('layouts.admin')
@section('title','Transactions')
@section('page-title','Transactions')
@section('content')
<div class="bg-slate-800 border border-slate-700 rounded-xl">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-700 text-slate-400 text-xs uppercase">
                <th class="px-6 py-3 text-left">User</th>
                <th class="px-6 py-3 text-left">Type</th>
                <th class="px-6 py-3 text-left">Amount</th>
                <th class="px-6 py-3 text-left">Description</th>
                <th class="px-6 py-3 text-left">Date</th>
            </tr></thead>
            <tbody>
            @forelse($transactions as $t)
                <tr class="border-b border-slate-700/50 hover:bg-slate-700/30">
                    <td class="px-6 py-3 text-slate-300">{{ $t->user->email ?? '—' }}</td>
                    <td class="px-6 py-3"><span class="px-2 py-0.5 rounded-full text-xs
                        {{ $t->type === 'deposit' ? 'bg-green-900/50 text-green-400' : ($t->type === 'purchase' ? 'bg-blue-900/50 text-blue-400' : 'bg-slate-700 text-slate-300') }}">
                        {{ ucfirst($t->type) }}</span></td>
                    <td class="px-6 py-3 {{ $t->type === 'deposit' ? 'text-green-400' : 'text-red-400' }} font-medium">
                        {{ $t->type === 'deposit' ? '+' : '-' }}₦{{ number_format(abs($t->amount), 2) }}</td>
                    <td class="px-6 py-3 text-slate-400">{{ $t->description ?? '—' }}</td>
                    <td class="px-6 py-3 text-slate-400">{{ $t->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-6 py-8 text-center text-slate-500">No transactions yet</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-slate-700">{{ $transactions->links() }}</div>
</div>
@endsection
