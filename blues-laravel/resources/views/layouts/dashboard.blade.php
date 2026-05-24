<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — BluesMarketplace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { brand: { DEFAULT: '#0ea5e9', dark: '#0284c7' } } } }
        }
    </script>
    <style>
        .sidebar-link { display:flex; align-items:center; gap:0.75rem; padding:0.625rem 1rem; border-radius:0.5rem; color:#cbd5e1; font-size:0.875rem; font-weight:500; transition:all .15s; }
        .sidebar-link:hover { background:#334155; color:#fff; }
        .sidebar-link.active { background:#334155; color:#fff; }
    </style>
</head>
<body class="bg-slate-900 text-white min-h-screen flex">

{{-- Sidebar --}}
<aside class="w-60 bg-slate-800 border-r border-slate-700 flex flex-col min-h-screen fixed top-0 left-0 bottom-0 z-40">
    <div class="px-5 py-4 border-b border-slate-700">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <div class="w-7 h-7 bg-brand rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <span class="font-bold text-white text-sm">Blues<span class="text-brand">Market</span></span>
        </a>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-0.5">
        <a href="{{ route('dashboard.index') }}" class="sidebar-link {{ request()->routeIs('dashboard.index') ? 'active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Overview
        </a>
        <a href="{{ route('dashboard.wallet') }}" class="sidebar-link {{ request()->routeIs('dashboard.wallet') ? 'active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            Wallet
        </a>
        <a href="{{ route('dashboard.orders') }}" class="sidebar-link {{ request()->routeIs('dashboard.orders') ? 'active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            Orders
        </a>
        <a href="{{ route('dashboard.virtual-numbers') }}" class="sidebar-link {{ request()->routeIs('dashboard.virtual-numbers*') ? 'active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            Virtual Numbers
        </a>
        <a href="{{ route('dashboard.wishlist') }}" class="sidebar-link {{ request()->routeIs('dashboard.wishlist') ? 'active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            Wishlist
        </a>
        <a href="{{ route('dashboard.notifications') }}" class="sidebar-link {{ request()->routeIs('dashboard.notifications') ? 'active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            Notifications
            @php $unread = \App\Models\Notification::where('user_id', auth()->id())->where('is_read', false)->count(); @endphp
            @if($unread > 0)
                <span class="ml-auto bg-brand text-white text-xs rounded-full px-1.5 py-0.5 leading-none">{{ $unread }}</span>
            @endif
        </a>
        <a href="{{ route('dashboard.referrals') }}" class="sidebar-link {{ request()->routeIs('dashboard.referrals') ? 'active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Refer a Friend
        </a>
        <a href="{{ route('dashboard.support') }}" class="sidebar-link {{ request()->routeIs('dashboard.support') ? 'active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
            Support
        </a>
        <a href="{{ route('dashboard.profile') }}" class="sidebar-link {{ request()->routeIs('dashboard.profile') ? 'active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            Profile
        </a>

        <div class="pt-2 border-t border-slate-700 mt-2">
            <a href="{{ route('marketplace') }}" class="sidebar-link">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Browse Marketplace
            </a>
        </div>
    </nav>

    <div class="px-4 py-4 border-t border-slate-700">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-8 h-8 rounded-full bg-brand flex items-center justify-center text-white text-sm font-bold">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-red-400 hover:text-red-300 hover:bg-red-900/20 text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Sign out
            </button>
        </form>
    </div>
</aside>

{{-- Main --}}
<div class="ml-60 flex-1 flex flex-col min-h-screen">
    <header class="bg-slate-800 border-b border-slate-700 px-8 py-4 flex items-center justify-between">
        <h1 class="text-base font-semibold text-white">@yield('page-title', 'Dashboard')</h1>
        <div class="flex items-center gap-3 text-sm text-slate-400">
            <span>Wallet:</span>
            <span class="text-white font-semibold">₦{{ number_format(\App\Models\Wallet::where('user_id', auth()->id())->value('balance') ?? 0, 2) }}</span>
            <a href="{{ route('dashboard.wallet') }}" class="text-brand hover:text-sky-300 text-xs">Top up →</a>
        </div>
    </header>

    <main class="flex-1 p-8">
        @if(session('success'))
            <div class="mb-5 p-4 bg-green-900/40 border border-green-700 rounded-lg text-green-300 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-5 p-4 bg-red-900/40 border border-red-700 rounded-lg text-red-300 text-sm">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-5 p-4 bg-red-900/40 border border-red-700 rounded-lg text-red-300 text-sm">
                @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
        @endif
        @yield('content')
    </main>
</div>

</body>
</html>
