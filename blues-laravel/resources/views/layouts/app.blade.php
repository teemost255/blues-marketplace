<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="format-detection" content="telephone=no">
    <title>@yield('title', 'Blues Marketplace') — Buy Digital Accounts</title>
    <meta name="description" content="@yield('meta_description', 'Blues Marketplace — Buy verified Facebook, Instagram, TikTok accounts and second phone numbers.')">
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
        /* ── Mobile overflow / zoom prevention ── */
        *, *::before, *::after { box-sizing: border-box; }
        html {
            overflow-x: hidden;
            -webkit-text-size-adjust: 100%;
            text-size-adjust: 100%;
        }
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;
            overflow-x: hidden;
            max-width: 100vw;
        }
        img, video, svg { max-width: 100%; height: auto; }

        /* Prevent iOS auto-zoom on input focus (font-size must be ≥ 16px) */
        @media screen and (max-width: 768px) {
            input, select, textarea {
                font-size: 16px !important;
            }
        }

        /* Horizontal-scroll wrapper for tables */
        .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }

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
                <span class="font-bold text-white text-lg">Blues <span class="text-brand">Marketplace</span></span>
            </a>

            {{-- Nav links --}}
            <nav class="hidden md:flex items-center gap-6">
                <a href="{{ route('dashboard.marketplace') }}" class="nav-link {{ request()->routeIs('marketplace*') ? 'text-white' : '' }}">Marketplace</a>
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
                    <span class="font-bold text-white">Blues <span class="text-brand">Marketplace</span></span>
                </div>
                <p class="text-slate-400 text-sm leading-relaxed max-w-sm">The trusted marketplace for verified digital accounts — Facebook, Instagram, TikTok, and second phone numbers.</p>
            </div>
            <div>
                <h4 class="text-white font-semibold text-sm mb-3">Marketplace</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li><a href="{{ route('dashboard.marketplace') }}?category=Facebook" class="hover:text-white transition-colors">Facebook Accounts</a></li>
                    <li><a href="{{ route('dashboard.marketplace') }}?category=Instagram" class="hover:text-white transition-colors">Instagram Accounts</a></li>
                    <li><a href="{{ route('dashboard.marketplace') }}?category=TikTok" class="hover:text-white transition-colors">TikTok Accounts</a></li>
                    <li><a href="{{ route('dashboard.marketplace') }}?category=2nd+Numbers" class="hover:text-white transition-colors">2nd Numbers</a></li>
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
            &copy; {{ date('Y') }} Blues Marketplace. All rights reserved.
        </div>
    </div>
</footer>

{{-- Floating WhatsApp Support Button --}}
@php $waNumber = \App\Models\Setting::get('whatsapp_number', ''); @endphp
@if($waNumber)
<style>
    .wa-btn {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: .625rem;
        background: #25D366;
        color: #fff;
        border-radius: 9999px;
        box-shadow: 0 4px 24px rgba(37,211,102,.45);
        cursor: pointer;
        text-decoration: none;
        padding: .75rem 1.25rem .75rem .875rem;
        font-size: .875rem;
        font-weight: 600;
        transition: transform .2s, box-shadow .2s, padding .35s, max-width .35s;
        max-width: 220px;
        overflow: hidden;
        white-space: nowrap;
    }
    .wa-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 32px rgba(37,211,102,.55);
    }
    .wa-btn svg { flex-shrink: 0; width: 1.5rem; height: 1.5rem; }
    .wa-label { transition: opacity .25s, max-width .35s; max-width: 160px; overflow: hidden; }

    /* Pulse ring */
    .wa-pulse {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        z-index: 9998;
        width: 56px;
        height: 56px;
        border-radius: 9999px;
        background: rgba(37,211,102,.3);
        animation: wa-ring 2.2s ease-out infinite;
        pointer-events: none;
    }
    @keyframes wa-ring {
        0%   { transform: scale(1);   opacity: .7; }
        70%  { transform: scale(1.55); opacity: 0; }
        100% { transform: scale(1.55); opacity: 0; }
    }

    @media (max-width: 480px) {
        .wa-btn { padding: .75rem; max-width: 56px; border-radius: 9999px; }
        .wa-label { max-width: 0; opacity: 0; }
    }
</style>

<div class="wa-pulse"></div>
<a href="https://wa.me/{{ $waNumber }}?text={{ urlencode('Hello! I need support with Blues Marketplace.') }}"
   target="_blank"
   rel="noopener noreferrer"
   class="wa-btn"
   title="Chat with us on WhatsApp">
    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
        <path d="M12 0C5.373 0 0 5.373 0 12c0 2.123.554 4.118 1.528 5.847L.057 23.882l6.204-1.448A11.945 11.945 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.891 0-3.667-.5-5.208-1.378l-.374-.217-3.872.904.951-3.768-.243-.389A9.956 9.956 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
    </svg>
    <span class="wa-label">Chat with us</span>
</a>
@endif

@stack('scripts')
</body>
</html>
