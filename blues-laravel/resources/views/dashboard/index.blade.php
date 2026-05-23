@extends('layouts.dashboard')
@section('title', 'Dashboard')
@section('page-title', 'Overview')

@section('content')
{{-- Stats row --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Wallet Balance</p>
        <p class="text-2xl font-bold text-white mt-1">${{ number_format($wallet->balance, 2) }}</p>
        <a href="{{ route('dashboard.wallet') }}" class="text-xs text-brand hover:underline mt-1 inline-block">Top up →</a>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Total Orders</p>
        <p class="text-2xl font-bold text-white mt-1">{{ $orderCount }}</p>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Total Spent</p>
        <p class="text-2xl font-bold text-white mt-1">${{ number_format($totalSpent, 2) }}</p>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Unread Notifications</p>
        <p class="text-2xl font-bold text-brand mt-1">{{ $unreadCount }}</p>
        <a href="{{ route('dashboard.notifications') }}" class="text-xs text-brand hover:underline mt-1 inline-block">View all →</a>
    </div>
</div>

{{-- Quick actions --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8">
    <a href="{{ route('marketplace') }}" class="bg-slate-800 border border-slate-700 hover:border-brand/50 rounded-xl p-4 flex items-center gap-3 transition-all group">
        <div class="w-9 h-9 bg-brand/10 rounded-lg flex items-center justify-center group-hover:bg-brand/20 transition-colors">
            <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
        <span class="text-sm font-medium text-white">Shop Now</span>
    </a>
    <a href="{{ route('dashboard.wallet') }}" class="bg-slate-800 border border-slate-700 hover:border-brand/50 rounded-xl p-4 flex items-center gap-3 transition-all group">
        <div class="w-9 h-9 bg-green-500/10 rounded-lg flex items-center justify-center group-hover:bg-green-500/20 transition-colors">
            <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        </div>
        <span class="text-sm font-medium text-white">Top Up</span>
    </a>
    <a href="{{ route('dashboard.orders') }}" class="bg-slate-800 border border-slate-700 hover:border-brand/50 rounded-xl p-4 flex items-center gap-3 transition-all group">
        <div class="w-9 h-9 bg-purple-500/10 rounded-lg flex items-center justify-center group-hover:bg-purple-500/20 transition-colors">
            <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        </div>
        <span class="text-sm font-medium text-white">My Orders</span>
    </a>
    <a href="{{ route('dashboard.support') }}" class="bg-slate-800 border border-slate-700 hover:border-brand/50 rounded-xl p-4 flex items-center gap-3 transition-all group">
        <div class="w-9 h-9 bg-yellow-500/10 rounded-lg flex items-center justify-center group-hover:bg-yellow-500/20 transition-colors">
            <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
        </div>
        <span class="text-sm font-medium text-white">Support</span>
    </a>
</div>

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
                    <td class="px-6 py-3 text-white font-medium">${{ number_format($order->amount, 2) }}</td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $order->status === 'completed' ? 'bg-green-900/50 text-green-400' : 'bg-yellow-900/50 text-yellow-400' }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-slate-400">{{ $order->created_at->diffForHumans() }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-6 py-10 text-center text-slate-500">No orders yet — <a href="{{ route('marketplace') }}" class="text-brand hover:underline">browse the marketplace</a></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
