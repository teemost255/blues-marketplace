@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('content')
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Total Users</p>
        <p class="text-3xl font-bold text-white mt-1">{{ number_format($stats['total_users']) }}</p>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Listings</p>
        <p class="text-3xl font-bold text-white mt-1">{{ number_format($stats['total_listings']) }}</p>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Total Purchases</p>
        <p class="text-3xl font-bold text-white mt-1">{{ number_format($stats['total_purchases']) }}</p>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Revenue</p>
        <p class="text-3xl font-bold text-white mt-1">${{ number_format($stats['total_revenue'], 2) }}</p>
    </div>
</div>
<div class="grid grid-cols-2 gap-4 mb-8">
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Open Tickets</p>
        <p class="text-3xl font-bold text-yellow-400 mt-1">{{ $stats['open_tickets'] }}</p>
        <a href="{{ route('admin.tickets') }}" class="text-xs text-sky-400 hover:underline mt-2 inline-block">View tickets →</a>
    </div>
</div>

<div class="bg-slate-800 border border-slate-700 rounded-xl">
    <div class="px-6 py-4 border-b border-slate-700"><h2 class="font-semibold text-white">Recent Purchases</h2></div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-700 text-slate-400 text-xs uppercase">
                <th class="px-6 py-3 text-left">User</th>
                <th class="px-6 py-3 text-left">Listing</th>
                <th class="px-6 py-3 text-left">Amount</th>
                <th class="px-6 py-3 text-left">Status</th>
                <th class="px-6 py-3 text-left">Date</th>
            </tr></thead>
            <tbody>
            @forelse($stats['recent_purchases'] as $p)
                <tr class="border-b border-slate-700/50 hover:bg-slate-700/30">
                    <td class="px-6 py-3 text-slate-300">{{ $p->user->email ?? '—' }}</td>
                    <td class="px-6 py-3 text-slate-300">{{ $p->listing->title ?? '—' }}</td>
                    <td class="px-6 py-3 text-white font-medium">${{ number_format($p->amount, 2) }}</td>
                    <td class="px-6 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $p->status === 'completed' ? 'bg-green-900/50 text-green-400' : ($p->status === 'pending' ? 'bg-yellow-900/50 text-yellow-400' : 'bg-red-900/50 text-red-400') }}">
                        {{ ucfirst($p->status) }}</span></td>
                    <td class="px-6 py-3 text-slate-400">{{ $p->created_at->diffForHumans() }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-6 py-8 text-center text-slate-500">No purchases yet</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
