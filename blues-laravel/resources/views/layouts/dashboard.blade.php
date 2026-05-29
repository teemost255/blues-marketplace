<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="format-detection" content="telephone=no">
    <title>@yield('title', 'Dashboard') — BluesMarketplace</title>
    <script>(function(){var t=localStorage.getItem('theme')||'dark';document.documentElement.setAttribute('data-theme',t);if(t==='light')document.documentElement.classList.add('light-mode');}());</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { brand: { DEFAULT: '#0ea5e9', dark: '#0284c7' } } } }
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
            overflow-x: hidden;
            max-width: 100vw;
        }
        img, video, svg { max-width: 100%; }

        /* Prevent iOS auto-zoom on input focus (font-size must be ≥ 16px) */
        @media screen and (max-width: 768px) {
            input, select, textarea {
                font-size: 16px !important;
            }
        }

        /* Horizontal-scroll wrapper for tables on mobile */
        .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }

        .sidebar-link { display:flex; align-items:center; gap:0.75rem; padding:0.625rem 1rem; border-radius:0.5rem; color:#cbd5e1; font-size:0.875rem; font-weight:500; transition:all .15s; }
        .sidebar-link:hover { background:#334155; color:#fff; }
        .sidebar-link.active { background:#334155; color:#fff; }

        /* Mobile sidebar transition */
        #dash-sidebar {
            transition: transform 0.28s cubic-bezier(.4,0,.2,1);
        }
        #dash-sidebar.sidebar-open {
            transform: translateX(0) !important;
        }

        /* ── Light Mode Overrides ── */
        [data-theme="light"] body { background-color: #f1f5f9; color: #0f172a; }
        [data-theme="light"] .bg-slate-950 { background-color: #e2e8f0 !important; }
        [data-theme="light"] .bg-slate-900 { background-color: #f1f5f9 !important; }
        [data-theme="light"] .bg-slate-800 { background-color: #ffffff !important; }
        [data-theme="light"] .bg-slate-700 { background-color: #e2e8f0 !important; }
        [data-theme="light"] .bg-slate-600 { background-color: #cbd5e1 !important; }
        [data-theme="light"] .text-white   { color: #0f172a !important; }
        [data-theme="light"] .text-slate-100 { color: #1e293b !important; }
        [data-theme="light"] .text-slate-200 { color: #334155 !important; }
        [data-theme="light"] .text-slate-300 { color: #475569 !important; }
        [data-theme="light"] .text-slate-400 { color: #64748b !important; }
        [data-theme="light"] .text-slate-500 { color: #94a3b8 !important; }
        [data-theme="light"] .text-slate-600 { color: #cbd5e1 !important; }
        [data-theme="light"] .border-slate-900 { border-color: #e2e8f0 !important; }
        [data-theme="light"] .border-slate-800 { border-color: #e2e8f0 !important; }
        [data-theme="light"] .border-slate-700 { border-color: #e2e8f0 !important; }
        [data-theme="light"] .border-slate-600 { border-color: #d1d5db !important; }
        [data-theme="light"] .hover\:bg-slate-700:hover { background-color: #e2e8f0 !important; }
        [data-theme="light"] .hover\:bg-slate-800:hover { background-color: #f1f5f9 !important; }
        [data-theme="light"] .hover\:border-slate-500:hover { border-color: #94a3b8 !important; }
        [data-theme="light"] .hover\:border-slate-600:hover { border-color: #94a3b8 !important; }
        [data-theme="light"] .hover\:text-white:hover { color: #0f172a !important; }
        [data-theme="light"] .divide-slate-700 > * + * { border-color: #e2e8f0 !important; }
        [data-theme="light"] .divide-slate-800 > * + * { border-color: #f1f5f9 !important; }
        /* Sidebar */
        [data-theme="light"] .sidebar-link { color: #475569 !important; }
        [data-theme="light"] .sidebar-link:hover { background:#e2e8f0 !important; color:#0f172a !important; }
        [data-theme="light"] .sidebar-link.active { background:#e2e8f0 !important; color:#0f172a !important; }
        /* Inputs */
        [data-theme="light"] input:not([type="checkbox"]):not([type="radio"]):not([type="range"]),
        [data-theme="light"] select,
        [data-theme="light"] textarea {
            background-color: #ffffff !important;
            color: #0f172a !important;
            border-color: #cbd5e1 !important;
        }
        [data-theme="light"] input::placeholder,
        [data-theme="light"] textarea::placeholder { color: #94a3b8 !important; }
        /* Alerts */
        [data-theme="light"] .bg-green-900\/40 { background-color: #dcfce7 !important; }
        [data-theme="light"] .border-green-700 { border-color: #86efac !important; }
        [data-theme="light"] .text-green-300 { color: #16a34a !important; }
        [data-theme="light"] .bg-red-900\/40 { background-color: #fee2e2 !important; }
        [data-theme="light"] .border-red-700 { border-color: #fca5a5 !important; }
        [data-theme="light"] .text-red-300 { color: #dc2626 !important; }
        [data-theme="light"] .text-red-400 { color: #ef4444 !important; }
        /* Theme toggle button */
        .theme-toggle { display:flex; align-items:center; justify-content:center; width:2rem; height:2rem; border-radius:0.5rem; background:transparent; border:none; cursor:pointer; color:#94a3b8; transition:background .15s,color .15s; }
        .theme-toggle:hover { background:rgba(148,163,184,.15); color:#fff; }
        [data-theme="light"] .theme-toggle:hover { background:#e2e8f0; color:#0f172a; }
        [data-theme="light"] .theme-toggle { color: #64748b; }
    </style>
</head>
<body class="bg-slate-900 text-white min-h-screen flex">

{{-- Mobile overlay --}}
<div id="dash-overlay"
    onclick="closeMobileSidebar()"
    class="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm hidden lg:hidden"></div>

{{-- Sidebar --}}
<aside id="dash-sidebar"
    class="w-60 bg-slate-800 border-r border-slate-700 flex flex-col min-h-screen fixed top-0 left-0 bottom-0 z-40
           -translate-x-full lg:translate-x-0">
    <div class="px-5 py-4 border-b border-slate-700 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <div class="w-7 h-7 bg-brand rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <span class="font-bold text-white text-sm">Blues<span class="text-brand">Marketplace</span></span>
        </a>
        {{-- Close button (mobile only) --}}
        <button onclick="closeMobileSidebar()"
            class="lg:hidden text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
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
        <a href="{{ route('dashboard.marketplace') }}" class="sidebar-link {{ request()->routeIs('dashboard.marketplace*') ? 'active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Marketplace
        </a>
        <div class="flex items-center gap-1">
            <a href="{{ route('dashboard.virtual-numbers') }}" class="sidebar-link flex-1 {{ request()->routeIs('dashboard.virtual-numbers*') ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                Virtual Numbers
            </a>
            <button onclick="toggleVnMenu()" id="vn-chevron-btn"
                class="p-1.5 rounded-lg text-slate-500 hover:text-slate-300 hover:bg-slate-700/50 transition-colors shrink-0" title="Toggle servers">
                <svg id="vn-chevron" class="w-3.5 h-3.5 transition-transform duration-200 {{ request()->routeIs('dashboard.virtual-numbers*') ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>
        <div id="vn-submenu" class="{{ request()->routeIs('dashboard.virtual-numbers*') ? '' : 'hidden' }} pl-6 space-y-0.5 mt-0.5">
            <a href="{{ route('dashboard.virtual-numbers') }}?server=1"
               class="sidebar-link py-1.5 text-xs {{ request()->routeIs('dashboard.virtual-numbers*') && request()->get('server') === '1' ? 'active' : '' }}">
                <span class="w-2 h-2 rounded-full bg-purple-400 shrink-0"></span>
                Server 1
            </a>
            <a href="{{ route('dashboard.virtual-numbers') }}?server=2"
               class="sidebar-link py-1.5 text-xs {{ request()->routeIs('dashboard.virtual-numbers*') && request()->get('server') === '2' ? 'active' : '' }}">
                <span class="w-2 h-2 rounded-full bg-green-400 shrink-0"></span>
                Server 2
            </a>
        </div>
        <a href="{{ route('dashboard.wishlist') }}" class="sidebar-link {{ request()->routeIs('dashboard.wishlist') ? 'active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            Wishlist
        </a>
        <a href="{{ route('dashboard.notifications') }}" class="sidebar-link {{ request()->routeIs('dashboard.notifications') ? 'active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            Notifications
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
    </nav>

    <div class="px-4 py-4 border-t border-slate-700">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-8 h-8 rounded-full bg-brand flex items-center justify-center text-white text-sm font-bold shrink-0">
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
<div class="lg:ml-60 flex-1 flex flex-col min-h-screen min-w-0 overflow-x-hidden">
    <header class="bg-slate-800 border-b border-slate-700 px-4 lg:px-8 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            {{-- Hamburger (mobile only) --}}
            <button onclick="openMobileSidebar()"
                class="lg:hidden text-slate-400 hover:text-white p-2 rounded-lg hover:bg-slate-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <h1 class="text-base font-semibold text-white">@yield('page-title', 'Dashboard')</h1>
        </div>
        <div class="flex items-center gap-3 lg:gap-4 text-sm text-slate-400">
            {{-- Theme toggle --}}
            <button onclick="toggleTheme()" class="theme-toggle" title="Toggle dark / light mode">
                <svg id="icon-sun" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/></svg>
                <svg id="icon-moon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
            </button>
            @php $headerUnread = \App\Models\Notification::where('user_id', auth()->id())->where('is_read', false)->count(); @endphp
            <a href="{{ route('dashboard.notifications') }}" class="relative text-slate-400 hover:text-white transition-colors" title="Notifications">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                @if($headerUnread > 0)
                    <span class="absolute -top-1.5 -right-1.5 min-w-[16px] h-4 bg-brand text-white text-[10px] font-bold rounded-full flex items-center justify-center px-0.5 leading-none">{{ $headerUnread > 9 ? '9+' : $headerUnread }}</span>
                @endif
            </a>
            <span class="hidden sm:inline">Wallet:</span>
            <span class="text-white font-semibold">₦{{ number_format(\App\Models\Wallet::where('user_id', auth()->id())->value('balance') ?? 0, 2) }}</span>
            <a href="{{ route('dashboard.wallet') }}" class="text-brand hover:text-sky-300 text-xs">Top up →</a>
        </div>
    </header>

    <main class="flex-1 p-4 lg:p-8">
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

<script>
function openMobileSidebar() {
    document.getElementById('dash-sidebar').classList.add('sidebar-open');
    document.getElementById('dash-overlay').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeMobileSidebar() {
    document.getElementById('dash-sidebar').classList.remove('sidebar-open');
    document.getElementById('dash-overlay').classList.add('hidden');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeMobileSidebar();
});

// ── Theme toggle ──
function applyThemeIcons(theme) {
    var sun  = document.getElementById('icon-sun');
    var moon = document.getElementById('icon-moon');
    if (!sun || !moon) return;
    if (theme === 'light') {
        sun.classList.add('hidden');
        moon.classList.remove('hidden');
    } else {
        sun.classList.remove('hidden');
        moon.classList.add('hidden');
    }
}
function toggleTheme() {
    var current = document.documentElement.getAttribute('data-theme') || 'dark';
    var next = current === 'light' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
    applyThemeIcons(next);
}
// Sync icon on load
applyThemeIcons(document.documentElement.getAttribute('data-theme') || 'dark');

// ── Virtual Numbers submenu toggle ──
function toggleVnMenu() {
    var menu    = document.getElementById('vn-submenu');
    var chevron = document.getElementById('vn-chevron');
    if (!menu) return;
    var isHidden = menu.classList.contains('hidden');
    menu.classList.toggle('hidden', !isHidden);
    chevron.classList.toggle('rotate-180', isHidden);
}
</script>

</body>
</html>
