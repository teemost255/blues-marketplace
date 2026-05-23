<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'BluesMarketplace') — Buy Digital Accounts</title>
    <meta name="description" content="@yield('meta_description', 'BluesMarketplace — Buy verified Facebook, Instagram, TikTok accounts and second phone numbers.')">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { DEFAULT: '#0ea5e9', dark: '#0284c7', light: '#38bdf8' }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, sans-serif; }
        .nav-link { @apply text-slate-300 hover:text-white transition-colors text-sm font-medium; }
        .btn-primary { @apply inline-flex items-center gap-2 bg-brand hover:bg-brand-dark text-white font-semibold px-5 py-2.5 rounded-lg transition-colors text-sm; }
        .btn-outline { @apply inline-flex items-center gap-2 border border-slate-600 hover:border-brand text-slate-300 hover:text-white font-medium px-5 py-2.5 rounded-lg transition-colors text-sm; }
        .card { @apply bg-slate-800 border border-slate-700 rounded-xl; }
    </style>
    @stack('head')
</head>
<body class="bg-slate-900 text-white min-h-screen flex flex-col">

{{-- Navbar --}}
<header class="sticky top-0 z-50 bg-slate-900/95 backdrop-blur border-b border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <div class="w-8 h-8 bg-brand rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <span class="font-bold text-white text-lg">Blues<span class="text-brand">Market</span></span>
            </a>

            {{-- Nav links --}}
            <nav class="hidden md:flex items-center gap-6">
                <a href="{{ route('marketplace') }}" class="nav-link {{ request()->routeIs('marketplace*') ? 'text-white' : '' }}">Marketplace</a>
                <a href="{{ route('terms') }}" class="nav-link">Terms</a>
                <a href="{{ route('privacy') }}" class="nav-link">Privacy</a>
            </nav>

            {{-- Auth buttons --}}
            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard.index') }}" class="nav-link hidden sm:inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Dashboard
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="btn-outline !py-2 !px-4">Sign out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="nav-link hidden sm:inline">Sign in</a>
                    <a href="{{ route('register') }}" class="btn-primary !py-2 !px-4">Get Started</a>
                @endauth
            </div>
        </div>
    </div>
</header>

{{-- Flash messages --}}
@if(session('success'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
        <div class="p-4 bg-green-900/40 border border-green-700 rounded-lg text-green-300 text-sm">{{ session('success') }}</div>
    </div>
@endif
@if(session('error'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
        <div class="p-4 bg-red-900/40 border border-red-700 rounded-lg text-red-300 text-sm">{{ session('error') }}</div>
    </div>
@endif

{{-- Main content --}}
<main class="flex-1">
    @yield('content')
</main>

{{-- Footer --}}
<footer class="bg-slate-900 border-t border-slate-800 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="col-span-1 md:col-span-2">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-7 h-7 bg-brand rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <span class="font-bold text-white">Blues<span class="text-brand">Market</span></span>
                </div>
                <p class="text-slate-400 text-sm leading-relaxed max-w-sm">The trusted marketplace for verified digital accounts — Facebook, Instagram, TikTok, and second phone numbers.</p>
            </div>
            <div>
                <h4 class="text-white font-semibold text-sm mb-3">Marketplace</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li><a href="{{ route('marketplace') }}?category=Facebook" class="hover:text-white transition-colors">Facebook Accounts</a></li>
                    <li><a href="{{ route('marketplace') }}?category=Instagram" class="hover:text-white transition-colors">Instagram Accounts</a></li>
                    <li><a href="{{ route('marketplace') }}?category=TikTok" class="hover:text-white transition-colors">TikTok Accounts</a></li>
                    <li><a href="{{ route('marketplace') }}?category=2nd+Numbers" class="hover:text-white transition-colors">2nd Numbers</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold text-sm mb-3">Support</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li><a href="{{ route('terms') }}" class="hover:text-white transition-colors">Terms of Service</a></li>
                    <li><a href="{{ route('privacy') }}" class="hover:text-white transition-colors">Privacy Policy</a></li>
                    @auth
                    <li><a href="{{ route('dashboard.support') }}" class="hover:text-white transition-colors">Contact Support</a></li>
                    @endauth
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-800 mt-8 pt-6 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} BluesMarketplace. All rights reserved.
        </div>
    </div>
</footer>

@stack('scripts')
</body>
</html>
