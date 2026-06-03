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
        <p class="text-2xl font-bold text-blue-400">{{ number_format($stats['waiting']) }}</p>
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

{{-- Provider balance & settings link --}}
<div class="flex flex-wrap items-center gap-3 mb-6">
    @if($balance !== null)
    <div class="bg-slate-800 border border-slate-700 rounded-xl px-5 py-3 flex items-center gap-3 flex-1 min-w-0">
        <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"
             style="background:rgba(59,130,246,.15)">
            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
        </div>
        <div class="min-w-0">
            <p class="text-sm text-white font-semibold">HeroSMS Balance: <span class="text-sky-400">${{ number_format($balance, 4) }}</span></p>
            <p class="text-xs text-slate-400 truncate">Top up at hero-sms.com to keep numbers flowing</p>
        </div>
        <a href="{{ route('admin.virtual-number-settings') }}" class="ml-auto shrink-0 flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg text-white transition-all hover:opacity-90" style="background:#3b82f6;">
            Settings
        </a>
    </div>
    @else
    <a href="{{ route('admin.virtual-number-settings') }}"
       class="flex items-center gap-2 text-sm font-semibold px-4 py-2.5 rounded-lg text-white transition-all hover:opacity-90"
       style="background:#3b82f6;">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        Configure Virtual Numbers
    </a>
    @endif
</div>

{{-- Filters --}}
<form method="GET" class="flex flex-wrap gap-3 mb-5">
    <div class="relative">
        <select name="status" onchange="this.form.submit()"
                class="bg-slate-800 border border-slate-700 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500 pr-8 appearance-none">
            <option value="all" {{ request('status','all')==='all'?'selected':'' }}>All Statuses</option>
            <option value="waiting"   {{ request('status')==='waiting'  ?'selected':'' }}>⏳ Waiting</option>
            <option value="received"  {{ request('status')==='received' ?'selected':'' }}>📨 Received</option>
            <option value="completed" {{ request('status')==='completed'?'selected':'' }}>✓ Completed</option>
            <option value="cancelled" {{ request('status')==='cancelled'?'selected':'' }}>✕ Cancelled</option>
            <option value="expired"   {{ request('status')==='expired'  ?'selected':'' }}>⌛ Expired</option>
        </select>
        <svg class="w-4 h-4 absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>
    <div class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search by user or number…"
               class="bg-slate-800 border border-slate-700 text-white text-sm rounded-lg px-3 py-2 w-56 focus:outline-none focus:border-blue-500">
        <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg transition-colors">Search</button>
        @if(request('search') || request('status', 'all') !== 'all')
        <a href="{{ route('admin.virtual-numbers') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm rounded-lg transition-colors">Clear</a>
        @endif
    </div>
</form>

{{-- Table --}}
<div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-700 text-slate-400 text-xs uppercase">
                    <th class="px-5 py-3 text-left font-medium">User</th>
                    <th class="px-5 py-3 text-left font-medium">Phone Number</th>
                    <th class="px-5 py-3 text-left font-medium">Service / Country</th>
                    <th class="px-5 py-3 text-left font-medium">SMS Code</th>
                    <th class="px-5 py-3 text-left font-medium">Cost</th>
                    <th class="px-5 py-3 text-left font-medium">Status</th>
                    <th class="px-5 py-3 text-left font-medium">Date</th>
                </tr>
            </thead>
            <tbody>
            @forelse($orders as $order)
                <tr class="border-b border-slate-700/50 hover:bg-slate-700/20 transition-colors">
                    <td class="px-5 py-3">
                        <p class="text-white text-sm font-medium">{{ $order->user?->name }}</p>
                        <p class="text-xs text-slate-500">{{ $order->user?->email }}</p>
                    </td>
                    <td class="px-5 py-3">
                        <span class="font-mono text-white text-sm">+{{ $order->phone_number ?? '—' }}</span>
                    </td>
                    <td class="px-5 py-3">
                        <p class="text-white text-sm">{{ $order->service_name }}</p>
                        <p class="text-xs text-slate-500">{{ $order->country_name ?: 'Country #'.$order->country }}</p>
                    </td>
                    <td class="px-5 py-3">
                        @if($order->sms_code)
                        <span class="font-mono text-emerald-400 font-semibold tracking-widest">{{ $order->sms_code }}</span>
                        @else
                        <span class="text-slate-600">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-white font-semibold">₦{{ number_format($order->cost, 2) }}</td>
                    <td class="px-5 py-3">
                        @php
                            $badge = match($order->status) {
                                'waiting'   => 'bg-blue-900/50 text-blue-400 border-blue-700/50',
                                'received'  => 'bg-sky-900/50 text-sky-300 border-sky-700/50',
                                'completed' => 'bg-green-900/50 text-green-400 border-green-700/50',
                                'cancelled' => 'bg-slate-700 text-slate-400 border-slate-600',
                                'expired'   => 'bg-slate-800 text-slate-500 border-slate-700',
                                default     => 'bg-slate-700 text-slate-400 border-slate-600',
                            };
                            $icon = match($order->status) {
                                'waiting'   => '⏳',
                                'received'  => '📨',
                                'completed' => '✓',
                                'cancelled' => '✕',
                                'expired'   => '⌛',
                                default     => '',
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs border {{ $badge }}">
                            {{ $icon }} {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-slate-400 whitespace-nowrap text-xs">
                        {{ $order->created_at->format('M j, Y g:ia') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-14 text-center text-slate-500">
                        <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        No virtual number orders found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
    <div class="px-5 py-4 border-t border-slate-700">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
