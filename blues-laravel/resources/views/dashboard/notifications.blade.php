@extends('layouts.dashboard')
@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')
<div class="bg-slate-800 border border-slate-700 rounded-xl">
    <div class="px-6 py-4 border-b border-slate-700">
        <h2 class="font-semibold text-white">All Notifications</h2>
    </div>
    <div class="divide-y divide-slate-700/50">
        @forelse($notifications as $notif)
            <div class="px-6 py-4 flex gap-4 {{ $notif->is_read ? '' : 'bg-slate-700/20' }}">
                <div class="mt-0.5 flex-shrink-0">
                    @php
                    $colors = ['success'=>'bg-green-500/20 text-green-400','error'=>'bg-red-500/20 text-red-400','warning'=>'bg-yellow-500/20 text-yellow-400','info'=>'bg-brand/20 text-brand'];
                    $c = $colors[$notif->type] ?? $colors['info'];
                    @endphp
                    <div class="w-9 h-9 rounded-full {{ $c }} flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-4">
                        <p class="text-white font-medium text-sm {{ $notif->is_read ? '' : 'text-white' }}">{{ $notif->title }}</p>
                        <span class="text-xs text-slate-500 whitespace-nowrap flex-shrink-0">{{ $notif->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-slate-400 text-sm mt-1">{{ $notif->message }}</p>
                </div>
                @if(!$notif->is_read)
                    <div class="flex-shrink-0 mt-2"><div class="w-2 h-2 rounded-full bg-brand"></div></div>
                @endif
            </div>
        @empty
            <div class="py-16 text-center text-slate-500">
                <svg class="w-10 h-10 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <p>No notifications yet</p>
            </div>
        @endforelse
    </div>
    @if($notifications->hasPages())
        <div class="px-6 py-4 border-t border-slate-700">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
