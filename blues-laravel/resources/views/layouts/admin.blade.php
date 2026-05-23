<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — BluesMarketplace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { brand: { DEFAULT: '#0ea5e9', dark: '#0284c7' } } } }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-link { @apply flex items-center gap-3 px-4 py-2.5 rounded-lg text-slate-300 hover:bg-slate-700 hover:text-white transition-colors text-sm font-medium; }
        .sidebar-link.active { @apply bg-slate-700 text-white; }
    </style>
</head>
<body class="bg-slate-900 text-white min-h-screen flex">

{{-- Sidebar --}}
<aside class="w-64 bg-slate-800 border-r border-slate-700 flex flex-col min-h-screen fixed top-0 left-0 bottom-0 z-40">
    <div class="px-6 py-5 border-b border-slate-700">
        <div class="flex items-center gap-2">
            <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            <span class="font-bold text-white">BluesMarketplace</span>
        </div>
        <p class="text-xs text-slate-400 mt-1">Admin Panel</p>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-1">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>
        <a href="{{ route('admin.users') }}" class="sidebar-link {{ request()->routeIs('admin.users') ? 'active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            Users
        </a>
        <a href="{{ route('admin.listings') }}" class="sidebar-link {{ request()->routeIs('admin.listings') ? 'active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            Listings
        </a>
        <a href="{{ route('admin.transactions') }}" class="sidebar-link {{ request()->routeIs('admin.transactions') ? 'active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            Transactions
        </a>
        <a href="{{ route('admin.tickets') }}" class="sidebar-link {{ request()->routeIs('admin.tickets') ? 'active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
            Support Tickets
        </a>
        <a href="{{ route('admin.audit') }}" class="sidebar-link {{ request()->routeIs('admin.audit') ? 'active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Audit Log
        </a>
        <a href="{{ route('admin.settings') }}" class="sidebar-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Settings
        </a>
        <div class="pt-2 border-t border-slate-700 mt-2">
            <a href="/adminer" target="_blank" class="sidebar-link">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582 4 8 4s8 1.79 8 4"/></svg>
                Adminer (DB)
            </a>
        </div>
    </nav>

    <div class="px-4 py-4 border-t border-slate-700">
        <p class="text-xs text-slate-400 truncate mb-2">{{ session('admin_email') }}</p>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="w-full text-left sidebar-link text-red-400 hover:text-red-300 hover:bg-red-900/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Sign out
            </button>
        </form>
    </div>
</aside>

{{-- Main content --}}
<div class="ml-64 flex-1 flex flex-col min-h-screen">
    <header class="bg-slate-800 border-b border-slate-700 px-8 py-4 flex items-center justify-between">
        <h1 class="text-lg font-semibold text-white">@yield('page-title', 'Dashboard')</h1>
        <div class="flex items-center gap-3">
            <span class="text-sm text-slate-400">{{ session('admin_name') ?? session('admin_email') }}</span>
            <div class="w-8 h-8 rounded-full bg-brand flex items-center justify-center text-white text-sm font-bold">
                {{ strtoupper(substr(session('admin_name') ?? session('admin_email', 'A'), 0, 1)) }}
            </div>
        </div>
    </header>

    <main class="flex-1 p-8">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-900/40 border border-green-700 rounded-lg text-green-300 text-sm">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-4 p-4 bg-red-900/40 border border-red-700 rounded-lg text-red-300 text-sm">
                @foreach($errors->all() as $e) <p>{{ $e }}</p> @endforeach
            </div>
        @endif
        @yield('content')
    </main>
</div>

</body>
</html>
