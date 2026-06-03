@extends('layouts.admin')
@section('title', 'Virtual Numbers')
@section('page-title', 'Virtual Numbers')
@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-slate-800 border border-slate-700 rounded-xl px-5 py-4">
        <p class="text-2xl font-bold text-white">{{ number_format($stats['total']) }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Total Orders</p>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl px-5 py-4">
        <p class="text-2xl font-bold text-yellow-400">{{ number_format($stats['waiting']) }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Active / Waiting</p>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl px-5 py-4">
        <p class="text-2xl font-bold text-green-400">{{ number_format($stats['completed']) }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Completed</p>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl px-5 py-4">
        <p class="text-2xl font-bold text-white">₦{{ number_format($stats['revenue'], 0) }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Revenue</p>
    </div>
</div>

@if($balance !== null)
<div class="bg-slate-800 border border-slate-700 rounded-xl px-5 py-4 mb-6 flex items-center gap-3">
    <div class="w-9 h-9 rounded-lg bg-brand/20 flex items-center justify-center flex-shrink-0">
        <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
    </div>
    <div>
        <p class="text-sm text-white font-semibold">HeroSMS Account Balance: <span class="text-brand">${{ number_format($balance, 4) }}</span></p>
        <p class="text-xs text-slate-400">This is your HeroSMS provider balance. Top it up at hero-sms.com to keep numbers flowing.</p>
    </div>
</div>
@endif

{{-- Filters --}}
<form method="GET" class="flex flex-wrap gap-3 mb-5">
    <select name="status" onchange="this.form.submit()" class="bg-slate-800 border border-slate-700 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-brand">
        <option value="all" {{ request('status','all')==='all'?'selected':'' }}>All Statuses</option>
        <option value="waiting"   {{ request('status')==='waiting'  ?'selected':'' }}>Waiting</option>
        <option value="received"  {{ request('status')==='received' ?'selected':'' }}>Received</option>
        <option value="completed" {{ request('status')==='completed'?'selected':'' }}>Completed</option>
        <option value="cancelled" {{ request('status')==='cancelled'?'selected':'' }}>Cancelled</option>
    </select>
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by user email…"
        class="bg-slate-800 border border-slate-700 text-white text-sm rounded-lg px-3 py-2 w-56 focus:outline-none focus:border-brand">
    <button class="px-4 py-2 bg-brand text-white text-sm rounded-lg hover:bg-brand-dark">Search</button>
</form>

{{-- Table --}}
<div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-700 text-slate-400 text-xs uppercase">
                    <th class="px-5 py-3 text-left">User</th>
                    <th class="px-5 py-3 text-left">Phone Number</th>
                    <th class="px-5 py-3 text-left">Service / Country</th>
                    <th class="px-5 py-3 text-left">Code</th>
                    <th class="px-5 py-3 text-left">Cost</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left">Date</th>
                </tr>
            </thead>
            <tbody>
            @forelse($orders as $order)
                <tr class="border-b border-slate-700/50 hover:bg-slate-700/20 transition-colors">
                    <td class="px-5 py-3">
                        <p class="text-white text-sm">{{ $order->user?->name }}</p>
                        <p class="text-xs text-slate-500">{{ $order->user?->email }}</p>
                    </td>
                    <td class="px-5 py-3 font-mono text-white text-sm">+{{ $order->phone_number ?? '—' }}</td>
                    <td class="px-5 py-3">
                        <p class="text-white text-sm">{{ $order->service_name }}</p>
                        <p class="text-xs text-slate-500">{{ $order->country_name ?: 'Country #'.$order->country }}</p>
                    </td>
                    <td class="px-5 py-3">
                        @if($order->sms_code)
                        <span class="font-mono text-emerald-400 font-semibold tracking-widest">{{ $order->sms_code }}</span>
                        @else
                        <span class="text-slate-500">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-white font-semibold">₦{{ number_format($order->cost, 2) }}</td>
                    <td class="px-5 py-3">
                        @php
                            $badge = match($order->status) {
                                'waiting'   => 'bg-yellow-900/50 text-yellow-400 border-yellow-700/50',
                                'received'  => 'bg-blue-900/50 text-blue-400 border-blue-700/50',
                                'completed' => 'bg-green-900/50 text-green-400 border-green-700/50',
                                'cancelled' => 'bg-slate-700 text-slate-400 border-slate-600',
                                default     => 'bg-slate-700 text-slate-400 border-slate-600',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs border {{ $badge }}">{{ ucfirst($order->status) }}</span>
                    </td>
                    <td class="px-5 py-3 text-slate-400 whitespace-nowrap text-xs">{{ $order->created_at->format('M j, Y g:ia') }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-5 py-12 text-center text-slate-500">No virtual number orders found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
    <div class="px-5 py-4 border-t border-slate-700">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
