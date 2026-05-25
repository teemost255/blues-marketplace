<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — Blues Marketplace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { brand: { DEFAULT: '#0ea5e9', dark: '#0284c7' } } } }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-link { display:flex; align-items:center; gap:.75rem; padding:.625rem 1rem; border-radius:.5rem; color:#cbd5e1; font-size:.875rem; font-weight:500; transition:background .15s,color .15s; text-decoration:none; }
        .sidebar-link:hover { background:#334155; color:#fff; }
        .sidebar-link.active { background:#334155; color:#fff; }
        .modal-overlay { position:fixed;inset:0;background:rgba(0,0,0,.7);display:flex;align-items:center;justify-content:center;z-index:100;padding:1rem; }
        .modal-box { background:#1e293b;border:1px solid #334155;border-radius:1rem;padding:1.75rem;width:100%;max-width:480px;max-height:90vh;overflow-y:auto; }
        input[type=text],input[type=email],input[type=number],input[type=password],select,textarea {
            width:100%;background:#0f172a;border:1px solid #475569;border-radius:.5rem;padding:.5rem .75rem;color:#fff;font-size:.875rem;outline:none;
        }
        input:focus,select:focus,textarea:focus { border-color:#0ea5e9; }
        .btn-primary { background:#0ea5e9;color:#fff;padding:.5rem 1.25rem;border-radius:.5rem;font-size:.875rem;font-weight:500;cursor:pointer;border:none;transition:background .15s; }
        .btn-primary:hover { background:#0284c7; }
        .btn-danger  { background:#dc2626;color:#fff;padding:.5rem 1.25rem;border-radius:.5rem;font-size:.875rem;font-weight:500;cursor:pointer;border:none;transition:background .15s; }
        .btn-danger:hover { background:#b91c1c; }
        .status-active    { display:inline-flex;padding:.15rem .6rem;border-radius:9999px;font-size:.75rem;font-weight:500;background:rgba(16,185,129,.15);color:#34d399; }
        .status-suspended { display:inline-flex;padding:.15rem .6rem;border-radius:9999px;font-size:.75rem;font-weight:500;background:rgba(245,158,11,.15);color:#fbbf24; }
        .status-banned    { display:inline-flex;padding:.15rem .6rem;border-radius:9999px;font-size:.75rem;font-weight:500;background:rgba(239,68,68,.15);color:#f87171; }

        /* Mobile sidebar transition */
        #admin-sidebar {
            transition: transform 0.28s cubic-bezier(.4,0,.2,1);
        }
        #admin-sidebar.sidebar-open {
            transform: translateX(0) !important;
        }
    </style>
</head>
<body class="bg-slate-900 text-white min-h-screen flex">

{{-- Mobile overlay --}}
<div id="admin-overlay"
    onclick="closeMobileSidebar()"
    class="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm hidden lg:hidden"></div>

{{-- Sidebar --}}
<aside id="admin-sidebar"
    class="w-64 bg-slate-800 border-r border-slate-700 flex flex-col min-h-screen fixed top-0 left-0 bottom-0 z-40
           -translate-x-full lg:translate-x-0">
    <div class="px-6 py-5 border-b border-slate-700 flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <svg class="w-6 h-6 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span class="font-bold text-white">Blues Marketplace</span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Admin Panel</p>
        </div>
        {{-- Close button (mobile only) --}}
        <button onclick="closeMobileSidebar()"
            class="lg:hidden text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>
        <a href="{{ route('admin.users') }}" class="sidebar-link {{ request()->routeIs('admin.users') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            Users
        </a>
        <a href="{{ route('admin.listings') }}" class="sidebar-link {{ request()->routeIs('admin.listings*') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            Listings
        </a>
        <a href="{{ route('admin.categories') }}" class="sidebar-link {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            Categories
        </a>
        <a href="{{ route('admin.moderators') }}" class="sidebar-link {{ request()->routeIs('admin.moderators*') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            Moderators
        </a>
        <a href="{{ route('admin.transactions') }}" class="sidebar-link {{ request()->routeIs('admin.transactions') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            Transactions
        </a>
        @php $pendingBankTransfers = \App\Models\BankTransferPayment::where('status','pending')->count(); @endphp
        <a href="{{ route('admin.bank-transfers') }}" class="sidebar-link {{ request()->routeIs('admin.bank-transfers*') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Bank Transfers
            @if($pendingBankTransfers > 0)
                <span class="ml-auto bg-yellow-500 text-slate-900 text-xs font-bold px-1.5 py-0.5 rounded-full">{{ $pendingBankTransfers }}</span>
            @endif
        </a>
        <a href="{{ route('admin.tickets') }}" class="sidebar-link {{ request()->routeIs('admin.tickets') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
            Support Tickets
        </a>
        <a href="{{ route('admin.audit') }}" class="sidebar-link {{ request()->routeIs('admin.audit') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Audit Log
        </a>
        <a href="{{ route('admin.virtual-numbers') }}" class="sidebar-link {{ request()->routeIs('admin.virtual-numbers*') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            Virtual Numbers
        </a>
        <a href="{{ route('admin.announcements') }}" class="sidebar-link {{ request()->routeIs('admin.announcements*') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
            Announcements
        </a>
        <a href="{{ route('admin.referrals') }}" class="sidebar-link {{ request()->routeIs('admin.referrals*') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Referrals
        </a>
        <a href="{{ route('admin.reviews') }}" class="sidebar-link {{ request()->routeIs('admin.reviews*') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
            Reviews
        </a>
        <a href="{{ route('admin.settings') }}" class="sidebar-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Settings
        </a>
    </nav>

    <div class="px-4 py-4 border-t border-slate-700">
        <p class="text-xs text-slate-400 truncate mb-2">{{ session('admin_email') }}</p>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="sidebar-link text-red-400 hover:text-red-300 w-full" style="background:transparent;border:none;cursor:pointer;">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Sign out
            </button>
        </form>
    </div>
</aside>

{{-- Main content --}}
<div class="lg:ml-64 flex-1 flex flex-col min-h-screen">
    <header class="bg-slate-800 border-b border-slate-700 px-4 lg:px-8 py-4 flex items-center justify-between sticky top-0 z-30">
        <div class="flex items-center gap-3">
            {{-- Hamburger (mobile only) --}}
            <button onclick="openMobileSidebar()"
                class="lg:hidden text-slate-400 hover:text-white p-2 rounded-lg hover:bg-slate-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <h1 class="text-base lg:text-lg font-semibold text-white">@yield('page-title', 'Dashboard')</h1>
        </div>
        <div class="flex items-center gap-2 lg:gap-3">
            <a href="{{ route('home') }}" target="_blank" class="hidden sm:block text-xs text-slate-400 hover:text-sky-400 transition-colors">View Site →</a>
            <span class="hidden sm:block text-sm text-slate-400">{{ session('admin_name') ?? session('admin_email') }}</span>
            <div class="w-8 h-8 rounded-full bg-sky-600 flex items-center justify-center text-white text-sm font-bold shrink-0">
                {{ strtoupper(substr(session('admin_name') ?? session('admin_email', 'A'), 0, 1)) }}
            </div>
        </div>
    </header>

    <main class="flex-1 p-4 lg:p-8">
        @if(session('success'))
            <div class="mb-5 p-4 bg-green-900/40 border border-green-700 rounded-lg text-green-300 text-sm flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-5 p-4 bg-red-900/40 border border-red-700 rounded-lg text-red-300 text-sm flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('error') }}
            </div>
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
    document.getElementById('admin-sidebar').classList.add('sidebar-open');
    document.getElementById('admin-overlay').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeMobileSidebar() {
    document.getElementById('admin-sidebar').classList.remove('sidebar-open');
    document.getElementById('admin-overlay').classList.add('hidden');
    document.body.style.overflow = '';
}
function openModal(id) { document.getElementById(id).style.display = 'flex'; document.body.style.overflow='hidden'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; document.body.style.overflow=''; }
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMobileSidebar();
        document.querySelectorAll('.modal-overlay').forEach(m => { m.style.display='none'; document.body.style.overflow=''; });
    }
});
</script>
@stack('scripts')
</body>
</html>
