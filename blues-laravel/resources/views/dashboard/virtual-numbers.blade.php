@extends('layouts.dashboard')
@section('title', 'Virtual Numbers')

@push('styles')
<style>
    /* ── Blue Theme Palette ── */
    :root {
        --blue-50: #eff6ff; --blue-100: #dbeafe; --blue-200: #bfdbfe;
        --blue-400: #60a5fa; --blue-500: #3b82f6; --blue-600: #2563eb;
        --blue-700: #1d4ed8; --blue-800: #1e3a5f; --blue-900: #1e3a8a;
        --surface: #0f172a; --surface-2: #131f35; --surface-3: #162035;
        --border: #1e3a5f; --border-2: #1e3356;
    }

    /* ── Tabs ── */
    .vn-tab { position:relative; padding:.75rem 1.25rem; font-size:.875rem; font-weight:500; color:#94a3b8; cursor:pointer; white-space:nowrap; transition:color .15s; border-bottom:2px solid transparent; display:flex; align-items:center; gap:.5rem; background:none; border-top:none; border-left:none; border-right:none; }
    .vn-tab.active { color:#60a5fa; border-bottom-color:#3b82f6; }
    .vn-tab:hover:not(.active) { color:#e2e8f0; }
    .vn-tab-panel { display:none; }
    .vn-tab-panel.active { display:block; }

    /* ── Service Cards ── */
    .svc-card { background:#131f35; border:1px solid #1e3a5f; border-radius:.75rem; padding:.875rem 1rem; display:flex; align-items:center; gap:.75rem; transition:border-color .15s, background .15s, transform .12s; cursor:pointer; }
    .svc-card:hover { border-color:#3b82f6; background:#162035; transform:translateY(-1px); }
    .svc-badge { width:2.25rem; height:2.25rem; border-radius:.5rem; background:linear-gradient(135deg,#1d4ed8,#3b82f6); display:flex; align-items:center; justify-content:center; font-size:.75rem; font-weight:700; color:#fff; flex-shrink:0; }

    /* ── Buy Button ── */
    .buy-btn { background:#3b82f6; color:#fff; font-size:.75rem; font-weight:700; padding:.375rem .75rem; border-radius:.5rem; border:none; cursor:pointer; white-space:nowrap; transition:background .15s,transform .1s; display:flex; align-items:center; gap:.25rem; }
    .buy-btn:hover { background:#2563eb; transform:scale(1.04); }
    .buy-btn:disabled { background:#334155; cursor:not-allowed; transform:none; }

    /* ── Form controls ── */
    .vn-search { background:#131f35; border:1px solid #1e3a5f; border-radius:.5rem; padding:.625rem .875rem .625rem 2.5rem; color:#e2e8f0; font-size:.875rem; outline:none; width:100%; transition:border-color .15s; }
    .vn-search:focus { border-color:#3b82f6; }
    .vn-select { background:#131f35; border:1px solid #1e3a5f; border-radius:.5rem; padding:.625rem 2rem .625rem .875rem; color:#e2e8f0; font-size:.875rem; outline:none; appearance:none; cursor:pointer; transition:border-color .15s; }
    .vn-select:focus { border-color:#3b82f6; }

    /* ── Active rental cards ── */
    .active-card { background:#131f35; border:1px solid #1e3a5f; border-radius:.75rem; padding:1.125rem; transition:border-color .15s; }
    .active-card:hover { border-color:#3b82f6; }

    /* ── Status badges ── */
    .status-waiting { background:#1e3a8a; color:#93c5fd; border:1px solid #1d4ed8; border-radius:9999px; padding:.25rem .75rem; font-size:.7rem; font-weight:600; display:inline-flex; align-items:center; gap:.375rem; }
    .status-received { background:#052e16; color:#4ade80; border:1px solid #14532d; border-radius:9999px; padding:.25rem .75rem; font-size:.7rem; font-weight:600; }
    .status-expired  { background:#1c1917; color:#78716c; border:1px solid #44403c; border-radius:9999px; padding:.25rem .75rem; font-size:.7rem; font-weight:600; }

    /* ── Modal ── */
    .modal-backdrop { position:fixed; inset:0; background:rgba(0,0,0,.75); z-index:50; display:flex; align-items:center; justify-content:center; padding:1rem; backdrop-filter:blur(4px); }
    .modal-box { background:#0f1e36; border:1px solid #1e3a5f; border-radius:1.25rem; padding:1.75rem; width:100%; max-width:26rem; box-shadow:0 25px 50px -12px rgba(0,0,0,.6); }
    #buy-modal { display:none; }
    #buy-modal.open { display:flex; }

    /* ── Misc ── */
    .empty-state { padding:4rem 1rem; text-align:center; color:#475569; }
    .empty-state svg { margin:0 auto 1rem; opacity:.35; }
    .pulse-dot { width:.5rem; height:.5rem; border-radius:50%; background:#3b82f6; animation:pulse-anim 1.5s ease-in-out infinite; display:inline-block; }
    @keyframes pulse-anim { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(.8)} }
    @keyframes pulse-ring { 0%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(1.4)} 100%{opacity:1;transform:scale(1)} }

    /* ── History table ── */
    .hist-row { border-bottom:1px solid #1e3a5f; transition:background .15s; }
    .hist-row:hover { background:rgba(59,130,246,.04); }

    /* ── Countdown timer ── */
    .timer-bar { height:4px; border-radius:2px; background:#1e3a5f; overflow:hidden; }
    .timer-fill { height:100%; border-radius:2px; background:linear-gradient(90deg,#3b82f6,#60a5fa); transition:width 1s linear; }
    .timer-fill.danger { background:linear-gradient(90deg,#ef4444,#f87171); }

    /* ── Copy feedback ── */
    .copy-btn { color:#475569; cursor:pointer; transition:color .15s; background:none; border:none; padding:.25rem; border-radius:.25rem; }
    .copy-btn:hover { color:#60a5fa; }
    .copy-btn.copied { color:#4ade80; }

    /* ── Toast ── */
    .toast { display:flex; align-items:center; gap:.75rem; background:#0f1e36; border:1px solid #1e3a5f; border-radius:.75rem; padding:.75rem 1rem; box-shadow:0 10px 25px -5px rgba(0,0,0,.5); font-size:.875rem; color:#e2e8f0; animation:slide-in .25s ease; max-width:22rem; }
    .toast.success { border-left:3px solid #3b82f6; }
    .toast.error   { border-left:3px solid #ef4444; }
    @keyframes slide-in { from{transform:translateY(100%);opacity:0} to{transform:translateY(0);opacity:1} }
    @keyframes slide-out{ from{transform:translateY(0);opacity:1}   to{transform:translateY(100%);opacity:0} }

    /* ── Provider badge ── */
    .provider-badge { background:#1e3a8a; color:#93c5fd; border:1px solid #1d4ed8; font-size:.7rem; font-weight:700; padding:.2rem .6rem; border-radius:9999px; letter-spacing:.02em; }

    /* ── Skeleton loader ── */
    @keyframes shimmer { 0%{background-position:-200% 0} 100%{background-position:200% 0} }
    .skeleton { background:linear-gradient(90deg,#131f35 25%,#1a2d47 50%,#131f35 75%); background-size:200% 100%; animation:shimmer 1.5s ease-in-out infinite; border-radius:.375rem; }
</style>
@endpush

@section('content')

{{-- ══════════════════════════════════════════════════════
     PAGE HEADER
══════════════════════════════════════════════════════ --}}
<div class="flex flex-wrap items-start justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
             style="background:linear-gradient(135deg,#1d4ed8,#3b82f6);box-shadow:0 0 20px rgba(59,130,246,.3)">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
        </div>
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-lg font-bold text-white">Virtual Numbers</h1>
            </div>
            <p class="text-xs text-slate-400 mt-0.5">Receive SMS codes for any service worldwide</p>
        </div>
    </div>

    {{-- Balance card --}}
    <div class="flex items-center gap-3 rounded-xl px-4 py-3" style="background:#0f1e36;border:1px solid #1e3a5f;">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(59,130,246,.15)">
            <svg class="w-4 h-4" style="color:#3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
        </div>
        <div>
            <p class="text-xs text-slate-400">Wallet Balance</p>
            <p class="text-sm font-bold text-white" id="hdr-balance">₦{{ number_format($wallet->balance, 2) }}</p>
        </div>
        <a href="{{ route('dashboard.wallet') }}"
           class="ml-2 text-xs font-semibold px-3 py-1.5 rounded-lg transition-all hover:opacity-90"
           style="background:#3b82f6;color:#fff;">Top up</a>
    </div>
</div>

{{-- Service unavailable banner --}}
@if(!$enabled)
<div class="rounded-xl border px-5 py-4 mb-5 flex items-start gap-3"
     style="background:#1e3a8a20;border-color:#1d4ed8;color:#93c5fd;">
    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <div>
        <p class="font-semibold text-sm">Virtual numbers are temporarily unavailable</p>
        <p class="text-xs mt-0.5 opacity-75">Our team is working on restoring this service. Please check back soon.</p>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════
     MAIN CARD
══════════════════════════════════════════════════════ --}}
<div class="rounded-xl border overflow-hidden" style="background:#0a1628;border-color:#1e3a5f;">

    {{-- Tab bar --}}
    <div class="border-b flex overflow-x-auto" style="border-color:#1e3a5f;background:#0f1e36;">
        <button class="vn-tab active" onclick="switchTab('services', this)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
            Available Services
        </button>
        <button class="vn-tab" onclick="switchTab('active', this)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
            Active Rentals
            @php $activeCount = $orders->getCollection()->whereIn('status',['waiting','received'])->count(); @endphp
            @if($activeCount > 0)
            <span class="text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center"
                  style="background:#3b82f6;color:#fff;">{{ $activeCount }}</span>
            @endif
        </button>
        <button class="vn-tab" onclick="switchTab('history', this)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Rental History
        </button>
    </div>

    {{-- ════════════════════ TAB: Available Services ════════════════════ --}}
    <div class="vn-tab-panel active" id="tab-services">
        @if($enabled)
        {{-- Filter bar --}}
        <div class="flex flex-wrap items-center gap-3 p-4 border-b" style="border-color:#1e3a5f;">
            <div class="relative flex-1 min-w-48">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" id="svc-search" class="vn-search" placeholder="Search services…" oninput="filterServices()">
            </div>
            <div class="relative">
                <select id="country-filter" class="vn-select pr-8" onchange="loadServices()">
                    <option value="0">— All Countries —</option>
                </select>
                <svg class="w-4 h-4 absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
            <div class="relative">
                <select id="sort-filter" class="vn-select pr-8" onchange="loadServices()">
                    <option value="az">Sort: A–Z</option>
                    <option value="za">Sort: Z–A</option>
                    <option value="count">Most Available</option>
                    <option value="price_asc">Price: Low–High</option>
                    <option value="price_desc">Price: High–Low</option>
                </select>
                <svg class="w-4 h-4 absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
            <span class="text-xs text-slate-400 whitespace-nowrap" id="svc-count-label"></span>
        </div>

        {{-- Section header --}}
        <div class="px-4 pt-3 pb-1 flex items-center gap-2">
            <span class="text-base">🌍</span>
            <span class="text-sm font-semibold text-white" id="svc-section-label">All Countries</span>
            <span class="text-xs font-bold px-2 py-0.5 rounded-full"
                  style="background:#131f35;color:#60a5fa;border:1px solid #1e3a5f;"
                  id="svc-section-count"></span>
            <div id="svc-loading" class="flex items-center gap-1.5 ml-1 hidden">
                <span class="pulse-dot"></span>
                <span class="text-xs text-slate-500">Loading…</span>
            </div>
        </div>

        {{-- Service grid --}}
        <div class="p-4 pt-2">
            <div id="svc-grid" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @for($i = 0; $i < 8; $i++)
                <div class="svc-card" style="pointer-events:none;">
                    <div class="w-9 h-9 rounded-lg skeleton"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-3 w-28 skeleton"></div>
                        <div class="h-2.5 w-16 skeleton"></div>
                    </div>
                    <div class="space-y-2 text-right">
                        <div class="h-4 w-14 skeleton ml-auto"></div>
                        <div class="h-6 w-16 skeleton ml-auto"></div>
                    </div>
                </div>
                @endfor
            </div>
            <div id="svc-empty" class="hidden empty-state">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <p class="text-sm mt-2">No services found</p>
                <p class="text-xs mt-1 text-slate-600">Try a different search or country</p>
            </div>
        </div>
        @else
        <div class="empty-state">
            <svg class="w-14 h-14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
            <p class="text-sm mt-2 font-medium">Service currently unavailable</p>
            <p class="text-xs mt-1">Please check back later</p>
        </div>
        @endif
    </div>

    {{-- ════════════════════ TAB: Active Rentals ════════════════════ --}}
    <div class="vn-tab-panel" id="tab-active">
        @php $activeOrders = $orders->getCollection()->whereIn('status', ['waiting', 'received']); @endphp
        @if($activeOrders->isEmpty())
        <div class="empty-state">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
            <p class="text-sm mt-2 font-medium">No active rentals</p>
            <p class="text-xs mt-1">Go to Available Services to rent a number</p>
            <button onclick="switchTab('services', document.querySelector('.vn-tab'))"
                    class="mt-4 text-xs font-semibold px-5 py-2.5 rounded-lg transition-all hover:opacity-90"
                    style="background:#3b82f6;color:#fff;">Browse Services</button>
        </div>
        @else
        {{-- ── How-to Guide ── --}}
        <div class="mx-4 mt-4 rounded-xl overflow-hidden" style="background:#0c1a2e;border:1px solid #1e3a5f;">
            <button onclick="toggleGuide()" class="w-full flex items-center justify-between px-4 py-3 text-left" style="background:none;border:none;cursor:pointer;">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0" style="background:rgba(59,130,246,.15);">
                        <svg class="w-3.5 h-3.5" style="color:#60a5fa;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-white">How to use your rented number</span>
                </div>
                <svg id="guide-chevron" class="w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div id="guide-content" class="hidden px-4 pb-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="flex gap-3">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold mt-0.5" style="background:#1d4ed8;color:#bfdbfe;">1</div>
                        <div>
                            <p class="text-sm font-semibold text-white">Copy the phone number</p>
                            <p class="text-xs text-slate-400 mt-0.5">Click the copy icon next to the number shown in your active rental card below.</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold mt-0.5" style="background:#1d4ed8;color:#bfdbfe;">2</div>
                        <div>
                            <p class="text-sm font-semibold text-white">Enter it on the app / website</p>
                            <p class="text-xs text-slate-400 mt-0.5">Paste the number into the service's phone number field and request a verification code.</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold mt-0.5" style="background:#1d4ed8;color:#bfdbfe;">3</div>
                        <div>
                            <p class="text-sm font-semibold text-white">Wait for the code to arrive</p>
                            <p class="text-xs text-slate-400 mt-0.5">This page auto-refreshes every 10 seconds. Your code will appear in the green box automatically — no manual refresh needed.</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold mt-0.5" style="background:#1d4ed8;color:#bfdbfe;">4</div>
                        <div>
                            <p class="text-sm font-semibold text-white">Copy and enter your code</p>
                            <p class="text-xs text-slate-400 mt-0.5">Once the code appears, copy it and enter it on the app/website to complete verification.</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold mt-0.5" style="background:#1d4ed8;color:#bfdbfe;">5</div>
                        <div>
                            <p class="text-sm font-semibold text-white">Click "Mark Complete"</p>
                            <p class="text-xs text-slate-400 mt-0.5">Once you have your code, hit <strong style="color:#4ade80;">Mark Complete</strong> to close the session and release the number.</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold mt-0.5" style="background:#92400e;color:#fcd34d;">!</div>
                        <div>
                            <p class="text-sm font-semibold text-white">No code? Try "Resend SMS"</p>
                            <p class="text-xs text-slate-400 mt-0.5">If no code arrives within 2 minutes, tap <strong style="color:#93c5fd;">Resend SMS</strong> to request a new one, or <strong style="color:#f87171;">Cancel</strong> for a partial refund.</p>
                        </div>
                    </div>
                </div>
                <div class="mt-3 rounded-lg px-3 py-2.5 flex items-start gap-2" style="background:#1e3a8a20;border:1px solid #1d4ed8;">
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" style="color:#60a5fa;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-xs" style="color:#93c5fd;">Numbers expire after the time shown on the timer. If the timer runs out before a code arrives, the rental is automatically closed and a partial refund is issued.</p>
                </div>
            </div>
        </div>

        <div class="p-4 space-y-4" id="active-orders-list">
            @foreach($activeOrders as $order)
            <div class="active-card" id="active-order-{{ $order->id }}">
                {{-- Top row --}}
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                             style="background:linear-gradient(135deg,#1d4ed8,#3b82f6);">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="font-bold text-white font-mono tracking-wide text-sm">+{{ $order->phone_number }}</p>
                                <button onclick="copyCode('+{{ $order->phone_number }}', this)"
                                        class="copy-btn" title="Copy number">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $order->service_name }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap justify-end">
                        @if($order->status === 'received')
                        <span class="status-received">✓ SMS Received</span>
                        @else
                        <span class="status-waiting">
                            <span class="pulse-dot"></span>Waiting for SMS
                        </span>
                        @endif
                        <span class="text-xs font-bold" style="color:#60a5fa;">₦{{ number_format($order->cost, 0) }}</span>
                    </div>
                </div>

                {{-- Countdown timer (waiting only) --}}
                @if($order->expires_at && $order->status === 'waiting')
                @php
                    $totalSeconds = 20 * 60;
                    $remainingSeconds = max(0, now()->diffInSeconds($order->expires_at, false));
                    $pct = min(100, max(0, ($remainingSeconds / $totalSeconds) * 100));
                @endphp
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs text-slate-500">Time remaining</span>
                        <span class="text-xs font-mono font-semibold {{ $remainingSeconds < 300 ? 'text-red-400' : 'text-slate-300' }}"
                              id="timer-{{ $order->id }}"
                              data-expires="{{ $order->expires_at->timestamp }}">
                            {{ gmdate('i:s', $remainingSeconds) }}
                        </span>
                    </div>
                    <div class="timer-bar">
                        <div class="timer-fill {{ $pct < 25 ? 'danger' : '' }}"
                             id="timer-bar-{{ $order->id }}"
                             style="width:{{ $pct }}%"></div>
                    </div>
                </div>
                @endif

                {{-- SMS Code display --}}
                <div id="sms-code-{{ $order->id }}"
                     class="{{ $order->sms_code ? '' : 'hidden' }} mt-3 rounded-xl px-4 py-3 flex items-center gap-3"
                     style="background:#052e16;border:1px solid #14532d;">
                    <svg class="w-5 h-5 flex-shrink-0" style="color:#4ade80;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs mb-0.5" style="color:#4ade80;">Verification Code</p>
                        <p class="text-2xl font-bold text-white tracking-widest font-mono" id="code-text-{{ $order->id }}">{{ $order->sms_code ?? '' }}</p>
                    </div>
                    <button onclick="copyCode('{{ $order->sms_code ?? '' }}', this)"
                            class="copy-btn flex-shrink-0" title="Copy code">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </button>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 mt-3 flex-wrap" id="actions-{{ $order->id }}">
                    @if($order->status === 'waiting')
                    {{-- Live fetch indicator with last-checked time --}}
                    <span class="flex items-center gap-1.5 text-xs font-semibold px-3 py-2 rounded-lg"
                          style="background:#131f35;color:#60a5fa;border:1px solid #1e3a5f;"
                          id="poll-indicator-{{ $order->id }}">
                        <span class="pulse-dot" style="width:6px;height:6px;background:#60a5fa;border-radius:50%;display:inline-block;animation:pulse-ring 1.5s infinite;"></span>
                        <span>Fetching SMS…</span>
                        <span id="last-checked-{{ $order->id }}" class="text-slate-500 font-normal ml-0.5"></span>
                    </span>

                    {{-- Resend SMS button --}}
                    <button onclick="resendSms({{ $order->id }})"
                            class="flex items-center gap-1.5 text-xs font-semibold px-3 py-2 rounded-lg transition-colors"
                            style="background:#131f35;color:#94a3b8;border:1px solid #1e3a5f;"
                            id="resend-btn-{{ $order->id }}"
                            title="Request a new SMS code to this number">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Resend SMS
                    </button>
                    @endif

                    @if($order->status === 'received')
                    <button onclick="completeOrder({{ $order->id }})"
                            class="flex items-center gap-1.5 text-xs font-semibold px-3 py-2 rounded-lg transition-all"
                            style="background:#052e16;color:#4ade80;border:1px solid #14532d;"
                            id="complete-btn-{{ $order->id }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Mark Complete
                    </button>
                    @endif

                    @if($order->status === 'waiting')
                    {{-- Cancel with inline confirmation --}}
                    <span id="cancel-wrap-{{ $order->id }}">
                        <button onclick="askCancel({{ $order->id }})"
                                class="text-xs font-semibold px-3 py-2 rounded-lg text-slate-500 hover:text-red-400 transition-colors"
                                style="background:#131f35;border:1px solid #1e3a5f;"
                                id="cancel-btn-{{ $order->id }}">
                            Cancel
                        </button>
                    </span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ════════════════════ TAB: Rental History ════════════════════ --}}
    <div class="vn-tab-panel" id="tab-history">
        @if($orders->isEmpty())
        <div class="empty-state">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm mt-2 font-medium">No rental history yet</p>
            <p class="text-xs mt-1">Your completed rentals will appear here</p>
        </div>
        @else
        {{-- Summary stats --}}
        @php
            $coll        = $orders->getCollection();
            $totalSpent  = $coll->whereIn('status',['completed','received','waiting'])->sum('cost');
            $completed   = $coll->where('status','completed')->count();
            $cancelled   = $coll->where('status','cancelled')->count();
            $codesRxd    = $coll->whereNotNull('sms_code')->count();
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 p-4 border-b" style="border-color:#1e3a5f;">
            <div class="rounded-xl px-3 py-2.5" style="background:#131f35;border:1px solid #1e3a5f;">
                <p class="text-xs text-slate-400">Total Spent</p>
                <p class="text-sm font-bold mt-0.5" style="color:#60a5fa;">₦{{ number_format($totalSpent, 0) }}</p>
            </div>
            <div class="rounded-xl px-3 py-2.5" style="background:#131f35;border:1px solid #1e3a5f;">
                <p class="text-xs text-slate-400">Completed</p>
                <p class="text-sm font-bold text-green-400 mt-0.5">{{ $completed }}</p>
            </div>
            <div class="rounded-xl px-3 py-2.5" style="background:#131f35;border:1px solid #1e3a5f;">
                <p class="text-xs text-slate-400">Codes Received</p>
                <p class="text-sm font-bold text-white mt-0.5">{{ $codesRxd }}</p>
            </div>
            <div class="rounded-xl px-3 py-2.5" style="background:#131f35;border:1px solid #1e3a5f;">
                <p class="text-xs text-slate-400">Cancelled</p>
                <p class="text-sm font-bold text-slate-400 mt-0.5">{{ $cancelled }}</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs uppercase text-slate-500 border-b" style="border-color:#1e3a5f;">
                        <th class="px-5 py-3 text-left font-medium">Phone Number</th>
                        <th class="px-5 py-3 text-left font-medium">Service</th>
                        <th class="px-5 py-3 text-left font-medium">SMS Code</th>
                        <th class="px-5 py-3 text-left font-medium">Cost</th>
                        <th class="px-5 py-3 text-left font-medium">Status</th>
                        <th class="px-5 py-3 text-left font-medium">Date</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($orders as $order)
                <tr class="hist-row">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-white text-sm">+{{ $order->phone_number ?? '—' }}</span>
                            @if($order->phone_number)
                            <button onclick="copyCode('+{{ $order->phone_number }}', this)" class="copy-btn" title="Copy">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </button>
                            @endif
                        </div>
                    </td>
                    <td class="px-5 py-3">
                        <p class="text-white">{{ $order->service_name }}</p>
                    </td>
                    <td class="px-5 py-3">
                        @if($order->sms_code)
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-bold tracking-widest" style="color:#4ade80;">{{ $order->sms_code }}</span>
                            <button onclick="copyCode('{{ $order->sms_code }}', this)" class="copy-btn">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </button>
                        </div>
                        @else
                        <span class="text-slate-600">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 font-bold" style="color:#60a5fa;">₦{{ number_format($order->cost, 2) }}</td>
                    <td class="px-5 py-3">
                        @php
                        $badge = match($order->status) {
                            'waiting'   => ['background:#1e3a8a20;color:#93c5fd;border:1px solid #1d4ed8;', '⏳ Waiting'],
                            'received'  => ['background:#052e16;color:#4ade80;border:1px solid #14532d;',  '✓ Received'],
                            'completed' => ['background:#052e16;color:#4ade80;border:1px solid #166534;',  '✓ Completed'],
                            'cancelled' => ['background:#1c1c1c;color:#6b7280;border:1px solid #374151;',  '✕ Cancelled'],
                            'expired'   => ['background:#1c1917;color:#78716c;border:1px solid #44403c;',  '⌛ Expired'],
                            default     => ['background:#131f35;color:#94a3b8;border:1px solid #1e3a5f;',  ucfirst($order->status)],
                        };
                        @endphp
                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full inline-block"
                              style="{{ $badge[0] }}">{{ $badge[1] }}</span>
                    </td>
                    <td class="px-5 py-3 text-slate-500 text-xs whitespace-nowrap">
                        {{ $order->created_at->format('M j, Y g:ia') }}
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="px-5 py-4 border-t" style="border-color:#1e3a5f;">{{ $orders->links() }}</div>
        @endif
        @endif
    </div>

</div>{{-- /main card --}}


{{-- ══════════════════════════════════════════════════════
     BUY MODAL
══════════════════════════════════════════════════════ --}}
<div id="buy-modal" class="modal-backdrop" onclick="if(event.target===this)closeBuyModal()">
    <div class="modal-box" style="max-width:23rem;padding:0;overflow:hidden;">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4" style="background:#0f1e36;border-bottom:1px solid #1e3a5f;">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background:linear-gradient(135deg,#1d4ed8,#3b82f6);">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="stroke-width:2"
                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Rent a Number</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Virtual SMS number rental</p>
                </div>
            </div>
            <button onclick="closeBuyModal()"
                    class="text-slate-400 hover:text-white transition-colors p-1 rounded-lg"
                    style="background:#1a2d4a;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Supplemental pin note --}}
        <p id="modal-pin-note" class="hidden text-xs font-semibold px-5 py-2"
           style="background:#0a1f0e;color:#4ade80;border-bottom:1px solid #14532d;"></p>

        {{-- Info table --}}
        <div class="px-5 pt-4 pb-2">
            <div class="rounded-xl overflow-hidden" style="border:1px solid #1e3a5f;">

                {{-- Service row --}}
                <div class="flex items-center justify-between px-4 py-3" style="border-bottom:1px solid #1e3a5f;background:#0a1628;">
                    <span class="text-sm text-slate-400">Service</span>
                    <span class="text-sm font-bold text-white" id="modal-row-service">—</span>
                </div>

                {{-- Country select (hidden — value still used for purchase) --}}
                <select id="modal-country" class="hidden" onchange="fetchModalPrice()">
                    <option value="">Loading…</option>
                </select>

                {{-- Cost row --}}
                <div class="flex items-center justify-between px-4 py-3" style="border-bottom:1px solid #1e3a5f;background:#0a1628;">
                    <span class="text-sm text-slate-400">Cost</span>
                    <span class="text-sm font-bold" id="modal-price-display" style="color:#60a5fa;">—</span>
                </div>

                {{-- Balance row --}}
                <div class="flex items-center justify-between px-4 py-3" style="background:#0a1628;">
                    <span class="text-sm text-slate-400">Your balance</span>
                    <span class="text-sm font-bold" id="modal-row-balance"
                          style="color:#{{ $wallet->balance > 0 ? '4ade80' : 'f87171' }};">
                        ₦{{ number_format($wallet->balance, 2) }}
                    </span>
                </div>
            </div>

            {{-- Hidden breakdown (used internally) --}}
            <p class="hidden" id="modal-price-breakdown"></p>
        </div>

        {{-- Error message --}}
        <div id="modal-error" class="hidden text-xs mx-5 mt-3 rounded-lg px-3 py-2.5 flex items-center gap-2"
             style="background:#1e0a0a;color:#f87171;border:1px solid #7f1d1d;">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span id="modal-error-text"></span>
        </div>

        {{-- Rent button --}}
        <div class="px-5 pt-4 pb-2">
            <button id="confirm-buy-btn" onclick="confirmBuy()"
                    class="w-full py-3 text-sm font-bold rounded-xl text-white flex items-center justify-center gap-2 transition-all"
                    style="background:linear-gradient(135deg,#1d4ed8,#3b82f6);"
                    onmouseover="this.style.opacity='.9'" onmouseout="this.style.opacity='1'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span id="confirm-buy-label">Rent Number</span>
            </button>
        </div>

        {{-- Footer note --}}
        <p class="text-center text-xs text-slate-500 pb-4 px-5">Valid for ~20 min to receive one SMS code</p>
    </div>
</div>

{{-- Toast container --}}
<div id="toast-area" class="fixed bottom-5 right-5 z-50 space-y-2 pointer-events-none"></div>

@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';

let allServices       = [];
let countryMap        = {};
let selectedService   = null;   // { code, name, count, price, usd_cost }
let autoRefreshTimers = {};

/* ══════ Browser Push Notifications ══════ */
/* ── How-to Guide toggle ── */
function toggleGuide() {
    const content  = document.getElementById('guide-content');
    const chevron  = document.getElementById('guide-chevron');
    if (!content) return;
    const isHidden = content.classList.contains('hidden');
    content.classList.toggle('hidden', !isHidden);
    if (chevron) chevron.style.transform = isHidden ? 'rotate(180deg)' : '';
}

function requestNotificationPermission() {
    if (!('Notification' in window)) return;
    if (Notification.permission === 'default') {
        Notification.requestPermission();
    }
}

function sendPushNotification(title, body) {
    if (!('Notification' in window) || Notification.permission !== 'granted') return;
    try {
        new Notification(title, {
            body,
            icon: '/favicon.ico',
            badge: '/favicon.ico',
            tag: 'sms-code',
            renotify: true,
        });
    } catch (_) {}
}

/* ══════ SMS arrival sound (Web Audio API — no files needed) ══════ */
function playSmsSound() {
    try {
        const ctx  = new (window.AudioContext || window.webkitAudioContext)();
        const play = (freq, start, dur) => {
            const o = ctx.createOscillator();
            const g = ctx.createGain();
            o.connect(g); g.connect(ctx.destination);
            o.type = 'sine'; o.frequency.value = freq;
            g.gain.setValueAtTime(0, ctx.currentTime + start);
            g.gain.linearRampToValueAtTime(0.3, ctx.currentTime + start + 0.02);
            g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + start + dur);
            o.start(ctx.currentTime + start);
            o.stop(ctx.currentTime + start + dur);
        };
        play(880, 0,    0.15);
        play(1100, 0.15, 0.15);
        play(1320, 0.30, 0.25);
    } catch (_) {}
}

/* ══════ Tab switching ══════ */
function switchTab(tabId, btn) {
    document.querySelectorAll('.vn-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.vn-tab-panel').forEach(p => p.classList.remove('active'));
    if (btn) btn.classList.add('active');
    document.getElementById('tab-' + tabId)?.classList.add('active');
    if (tabId === 'active') startAutoRefresh();
    else stopAutoRefresh();
}

/* ══════ Country loading ══════ */
async function loadCountries() {
    try {
        const r    = await fetch('{{ route("dashboard.virtual-numbers.countries") }}', { headers: { Accept: 'application/json' } });
        const data = await r.json();
        if (!data.countries?.length) return;

        const filterSel = document.getElementById('country-filter');
        const modalSel  = document.getElementById('modal-country');

        filterSel.innerHTML = '<option value="0">— All Countries —</option>';
        data.countries.forEach(c => {
            countryMap[c.id] = c.name;
            filterSel.innerHTML += `<option value="${c.id}">${c.name}</option>`;
        });

        // Modal select populated lazily on open
        window._countries = data.countries;
    } catch (e) { console.error(e); }
}

function populateModalCountries(pinCountryId) {
    const sel = document.getElementById('modal-country');
    if (!window._countries?.length) { sel.innerHTML = '<option value="">No countries available</option>'; return; }
    const current = document.getElementById('country-filter').value;
    sel.innerHTML = '<option value="" disabled selected>— Select a country —</option>';
    window._countries.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c.id; opt.textContent = c.name;
        // If pinCountryId is provided (supplemental service), lock to that country
        if (pinCountryId != null) {
            if (String(c.id) === String(pinCountryId)) opt.selected = true;
        } else if (String(c.id) === current && current !== '0') {
            opt.selected = true;
        }
        sel.appendChild(opt);
    });
    // If pinCountryId is set, make the select read-only (disable other options visually)
    if (pinCountryId != null) {
        // If the pinned country wasn't found in list, auto-select first
        if (sel.value === '' && window._countries.length) sel.value = window._countries[0].id;
        sel.style.opacity = '.75';
        sel.style.pointerEvents = 'none';
    } else {
        sel.style.opacity = '';
        sel.style.pointerEvents = '';
        if (sel.value === '' && window._countries.length) sel.value = window._countries[0].id;
    }
    fetchModalPrice();
}

/* ══════ Service loading ══════ */
/* ── Detect server preference from URL (?server=1 or ?server=2) ── */
const _vnServerPref = new URLSearchParams(window.location.search).get('server') || '';

async function loadServices() {
    const country = document.getElementById('country-filter').value;
    const sort    = document.getElementById('sort-filter').value;
    const loading = document.getElementById('svc-loading');
    loading?.classList.remove('hidden');

    try {
        let url = `{{ route('dashboard.virtual-numbers.services') }}?country=${country}&sort=${sort}`;
        if (_vnServerPref) url += `&server=${_vnServerPref}`;
        const r   = await fetch(url, { headers: { Accept: 'application/json' } });
        const d   = await r.json();
        allServices = d.services ?? [];
        renderServices(allServices);
    } catch (e) {
        showToast('Failed to load services. Please refresh.', 'error');
    } finally {
        loading?.classList.add('hidden');
    }
}

function renderServices(services) {
    const grid      = document.getElementById('svc-grid');
    const empty     = document.getElementById('svc-empty');
    const label     = document.getElementById('svc-section-label');
    const count     = document.getElementById('svc-section-count');
    const countLbl  = document.getElementById('svc-count-label');
    const country   = document.getElementById('country-filter');
    const countryName = country.options[country.selectedIndex]?.text ?? 'All Countries';

    label.textContent = country.value === '0' ? 'All Countries' : countryName;
    count.textContent = services.length;
    countLbl.textContent = `${services.length} service${services.length !== 1 ? 's' : ''}`;

    if (!services.length) {
        grid.innerHTML = '';
        empty.classList.remove('hidden');
        return;
    }
    empty.classList.add('hidden');

    // Client-side price sort fallback
    const sort = document.getElementById('sort-filter').value;
    let sorted = [...services];
    if (sort === 'price_asc')  sorted.sort((a,b) => (a.price ?? 999999) - (b.price ?? 999999));
    if (sort === 'price_desc') sorted.sort((a,b) => (b.price ?? 0) - (a.price ?? 0));

    grid.innerHTML = sorted.map(svc => {
        const initial  = (svc.name[0] ?? '?').toUpperCase();
        const countTxt = svc.count >= 1000
            ? (svc.count / 1000).toFixed(1) + 'k'
            : svc.count.toLocaleString();

        const priceNgn = svc.price ?? null;
        const priceTag = priceNgn !== null
            ? `<span class="text-sm font-bold" style="color:#60a5fa;">₦${Math.ceil(priceNgn).toLocaleString()}</span>`
            : `<span class="text-xs text-slate-500">—</span>`;

        const usdHint = svc.usd_cost !== null && svc.usd_cost !== undefined
            ? `<span class="text-xs text-slate-600">$${parseFloat(svc.usd_cost).toFixed(2)}</span>`
            : '';

        const priceJson  = priceNgn !== null ? priceNgn : 0;
        const usdJson    = svc.usd_cost !== null && svc.usd_cost !== undefined ? svc.usd_cost : null;
        const nameEsc    = svc.name.replace(/'/g, "\\'").replace(/"/g, '&quot;');

        // Supplemental service — different country source
        const pinId      = svc.supplemental ? (svc.supplemental_country_id ?? null) : null;
        const pinName    = svc.supplemental ? (svc.supplemental_country_name ?? '') : '';
        const suppBadge  = svc.supplemental
            ? `<span class="text-xs font-semibold px-1.5 py-0.5 rounded" style="background:#0f2d1a;color:#4ade80;border:1px solid #14532d;">${pinName}</span>`
            : '';
        const cardStyle  = svc.supplemental
            ? 'border-color:#14532d;background:#0a1a10;'
            : '';
        const badgeSyle  = svc.supplemental
            ? 'background:linear-gradient(135deg,#065f46,#10b981);'
            : '';

        const openCall = `openBuyModal('${svc.code}', '${nameEsc}', ${svc.count}, ${priceJson}, ${JSON.stringify(usdJson)}, ${JSON.stringify(pinId)}, '${pinName}')`;

        return `
        <div class="svc-card" style="${cardStyle}" onclick="${openCall}">
            <div class="svc-badge" style="${badgeSyle}">${initial}</div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <p class="text-sm font-semibold text-white truncate">${svc.name}</p>
                    ${suppBadge}
                </div>
                <p class="text-xs text-slate-500 mt-0.5">${countTxt} available</p>
            </div>
            <div class="flex flex-col items-end gap-1 flex-shrink-0">
                ${priceTag}
                ${usdHint}
                <button class="buy-btn" style="${svc.supplemental ? 'background:#10b981;' : ''}"
                        onclick="event.stopPropagation(); ${openCall}">
                    Buy
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>`;
    }).join('');
}

function filterServices() {
    const q = document.getElementById('svc-search').value.toLowerCase().trim();
    if (!q) { renderServices(allServices); return; }
    renderServices(allServices.filter(s => s.name.toLowerCase().includes(q) || s.code.toLowerCase().includes(q)));
}

/* ══════ Buy Modal ══════ */
function openBuyModal(code, name, count, priceNgn, usdCost, pinCountryId, pinCountryName) {
    selectedService = { code, name, count, price: priceNgn, usd_cost: usdCost };
    const rowSvc = document.getElementById('modal-row-service');
    if (rowSvc) rowSvc.textContent = name;
    document.getElementById('modal-error').classList.add('hidden');

    // Show country pin note if this is a supplemental service
    const pinNote = document.getElementById('modal-pin-note');
    if (pinNote) {
        if (pinCountryId && pinCountryName) {
            pinNote.textContent = `Numbers sourced from ${pinCountryName}`;
            pinNote.classList.remove('hidden');
        } else {
            pinNote.classList.add('hidden');
        }
    }

    populateModalCountries(pinCountryId);
    updateModalPrice(priceNgn, usdCost);
    document.getElementById('buy-modal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function updateModalPrice(priceNgn, usdCost) {
    const priceEl  = document.getElementById('modal-price-display');
    const btnLabel = document.getElementById('confirm-buy-label');
    const price    = priceNgn ?? selectedService?.price;

    if (price && price > 0) {
        const fmt = '₦' + Math.ceil(price).toLocaleString();
        if (priceEl)  priceEl.textContent = fmt;
        if (btnLabel) btnLabel.textContent = `Rent Number — ${fmt}`;
    } else {
        if (priceEl)  priceEl.textContent = '—';
        if (btnLabel) btnLabel.textContent = 'Rent Number';
    }
}

/* Fetch live price for the service+country currently selected in the modal */
async function fetchModalPrice() {
    if (!selectedService) return;
    const countryId = document.getElementById('modal-country')?.value;
    if (!countryId) return;

    const priceEl  = document.getElementById('modal-price-display');
    const btnLabel = document.getElementById('confirm-buy-label');
    if (priceEl)  priceEl.textContent = '…';
    if (btnLabel) btnLabel.textContent = 'Loading price…';

    try {
        const r = await fetch(`{{ route('dashboard.virtual-numbers.services') }}?country=${countryId}`, {
            headers: { Accept: 'application/json' }
        });
        const d   = await r.json();
        const svc = (d.services ?? []).find(s => s.code === selectedService.code);
        if (svc) {
            updateModalPrice(svc.price, svc.usd_cost);
        } else {
            if (priceEl)  priceEl.textContent = '—';
            if (btnLabel) btnLabel.textContent = 'Buy';
        }
    } catch (e) {
        updateModalPrice(selectedService.price, selectedService.usd_cost);
    }
}

function closeBuyModal() {
    document.getElementById('buy-modal').classList.remove('open');
    document.body.style.overflow = '';
    selectedService = null;
}

async function confirmBuy() {
    if (!selectedService) return;
    const btn         = document.getElementById('confirm-buy-btn');
    const errBox      = document.getElementById('modal-error');
    const errTxt      = document.getElementById('modal-error-text');
    const countrySel  = document.getElementById('modal-country');
    const country     = countrySel.value;
    const countryName = countrySel.options[countrySel.selectedIndex]?.text ?? '';

    errBox.classList.add('hidden');

    // Require a specific country to be selected
    if (!country || country === '0') {
        errTxt.textContent = 'Please select a country before purchasing.';
        errBox.classList.remove('hidden');
        countrySel.focus();
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="pulse-dot"></span> Processing…';

    const priceFmt   = document.getElementById('modal-price-display')?.textContent ?? '';
    const resetLabel = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> <span>Buy${priceFmt && priceFmt !== '—' ? ' — ' + priceFmt : ''}</span>`;

    try {
        const orderBody = { service: selectedService.code, country: parseInt(country), country_name: countryName };
        if (_vnServerPref) orderBody.server_pref = parseInt(_vnServerPref);
        const r = await fetch('{{ route("dashboard.virtual-numbers.order") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' },
            body: JSON.stringify(orderBody),
        });
        const data = await r.json();

        if (data.success) {
            closeBuyModal();
            showToast(`✓ Number assigned: +${data.order.phone_number}`, 'success');
            // Reload and switch to Active Rentals tab
            setTimeout(() => { location.href = location.pathname + '?tab=active'; }, 1200);
        } else {
            // data.error = our custom message; data.debug = raw DB error; data.message = Laravel's format
            const msg  = data.error || data.message || 'Something went wrong. Please try again.';
            const dbug = data.debug ? ` (${data.debug})` : '';
            errTxt.textContent = msg + dbug;
            errBox.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = resetLabel;
        }
    } catch (e) {
        errTxt.textContent = 'Network error. Please check your connection and try again.';
        errBox.classList.remove('hidden');
        btn.disabled = false;
        btn.innerHTML = resetLabel;
    }
}

/* Update the order card UI when an SMS code has arrived */
function applyReceivedState(orderId, code) {
    // Show code box with the code
    const codeBox = document.getElementById(`sms-code-${orderId}`);
    if (codeBox) {
        codeBox.classList.remove('hidden');
        // Update copy button to use the new code
        const copyBtn = codeBox.querySelector('button');
        if (copyBtn) copyBtn.setAttribute('onclick', `copyCode('${code}', this)`);
    }
    const codeText = document.getElementById(`code-text-${orderId}`);
    if (codeText) codeText.textContent = code;

    // Swap status badge
    const card = document.getElementById(`active-order-${orderId}`);
    if (!card) return;

    const waitBadge = card.querySelector('.status-waiting');
    if (waitBadge) {
        const received = document.createElement('span');
        received.className = 'status-received';
        received.textContent = '✓ SMS Received';
        waitBadge.replaceWith(received);
    }

    // Replace actions: remove poll indicator + resend btn + cancel wrap, add Mark Complete
    const actionsDiv = document.getElementById(`actions-${orderId}`);
    if (actionsDiv) {
        document.getElementById(`poll-indicator-${orderId}`)?.remove();
        document.getElementById(`resend-btn-${orderId}`)?.remove();
        document.getElementById(`cancel-wrap-${orderId}`)?.remove();

        if (!document.getElementById(`complete-btn-${orderId}`)) {
            const completeBtn = document.createElement('button');
            completeBtn.id = `complete-btn-${orderId}`;
            completeBtn.setAttribute('onclick', `completeOrder(${orderId})`);
            completeBtn.className = 'flex items-center gap-1.5 text-xs font-semibold px-3 py-2 rounded-lg transition-all';
            completeBtn.style.cssText = 'background:#052e16;color:#4ade80;border:1px solid #14532d;';
            completeBtn.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Mark Complete`;
            actionsDiv.prepend(completeBtn);
        }
    }

    // 🔔 Play sound + browser push notification
    playSmsSound();
    sendPushNotification('SMS Code Received!', `Your verification code: ${code}`);

    // 📋 Auto-copy to clipboard when code arrives
    copyCode(code, null, true);
}

/* ══════ Complete order ══════ */
async function completeOrder(orderId) {
    try {
        const r = await fetch(`/dashboard/virtual-numbers/${orderId}/complete`, {
            method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' },
        });
        const d = await r.json();
        if (d.success) {
            showToast('Order marked as complete.', 'success');
            document.getElementById(`active-order-${orderId}`)?.remove();
        }
    } catch (e) { showToast('Error.', 'error'); }
}

/* ══════ Cancel order — inline confirmation (no confirm() dialog) ══════ */
function askCancel(orderId) {
    const wrap = document.getElementById(`cancel-wrap-${orderId}`);
    if (!wrap) return;
    wrap.innerHTML = `
        <span class="flex items-center gap-1.5 text-xs">
            <span class="text-red-400 font-semibold">Cancel rental?</span>
            <button onclick="doCancel(${orderId})"
                    class="px-2.5 py-1 rounded text-xs font-bold text-white transition-colors"
                    style="background:#dc2626;">Yes, cancel</button>
            <button onclick="resetCancel(${orderId})"
                    class="px-2.5 py-1 rounded text-xs font-semibold text-slate-400 hover:text-white transition-colors"
                    style="background:#1e293b;">No</button>
        </span>`;
}

function resetCancel(orderId) {
    const wrap = document.getElementById(`cancel-wrap-${orderId}`);
    if (!wrap) return;
    wrap.innerHTML = `
        <button onclick="askCancel(${orderId})"
                class="text-xs font-semibold px-3 py-2 rounded-lg text-slate-500 hover:text-red-400 transition-colors"
                style="background:#131f35;border:1px solid #1e3a5f;"
                id="cancel-btn-${orderId}">
            Cancel
        </button>`;
}

async function doCancel(orderId) {
    const wrap = document.getElementById(`cancel-wrap-${orderId}`);
    if (wrap) wrap.innerHTML = `<span class="text-xs text-slate-500 px-2">Cancelling…</span>`;

    // Stop polling this order
    if (autoRefreshTimers[orderId]) {
        clearInterval(autoRefreshTimers[orderId]);
        delete autoRefreshTimers[orderId];
    }

    try {
        const r = await fetch(`/dashboard/virtual-numbers/${orderId}/cancel`, {
            method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' },
        });
        const d = await r.json();
        if (d.success) {
            let msg = 'Rental cancelled.';
            if (d.refunded > 0) msg += ` ₦${Number(d.refunded).toLocaleString('en-NG', {minimumFractionDigits:2})} refunded.`;
            showToast(msg, 'success');
            setTimeout(() => document.getElementById(`active-order-${orderId}`)?.remove(), 600);
            const hdr = document.getElementById('hdr-balance');
            if (hdr && d.refunded > 0) {
                const current = parseFloat(hdr.textContent.replace(/[₦,]/g, '')) + d.refunded;
                hdr.textContent = '₦' + current.toLocaleString('en-NG', { minimumFractionDigits:2, maximumFractionDigits:2 });
            }
        } else {
            showToast(d.error ?? 'Could not cancel.', 'error');
            resetCancel(orderId);
        }
    } catch (e) {
        showToast('Could not cancel — please try again.', 'error');
        resetCancel(orderId);
    }
}

/* ══════ Live auto-polling — starts immediately on page load ══════ */
function startAutoRefresh() {
    document.querySelectorAll('[id^="active-order-"]').forEach(el => {
        const orderId = el.id.replace('active-order-', '');
        if (autoRefreshTimers[orderId]) return;
        // Stagger first poll so multiple orders don't hit server simultaneously
        const delay = Math.random() * 1000;
        setTimeout(() => pollOrder(orderId), delay);
        autoRefreshTimers[orderId] = setInterval(() => pollOrder(orderId), 3000);
    });
}

function stopAutoRefresh() {
    Object.values(autoRefreshTimers).forEach(clearInterval);
    autoRefreshTimers = {};
}

async function pollOrder(orderId) {
    try {
        const r    = await fetch(`/dashboard/virtual-numbers/${orderId}/status`, {
            method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' },
        });
        if (!r.ok) return;
        const data = await r.json();

        // Update last-checked timestamp
        const lcEl = document.getElementById(`last-checked-${orderId}`);
        if (lcEl) {
            const now = new Date();
            lcEl.textContent = `· ${now.getHours().toString().padStart(2,'0')}:${now.getMinutes().toString().padStart(2,'0')}:${now.getSeconds().toString().padStart(2,'0')}`;
        }

        if (data.status === 'received' && data.code) {
            applyReceivedState(orderId, data.code);
            clearInterval(autoRefreshTimers[orderId]);
            delete autoRefreshTimers[orderId];
        } else if (data.status === 'cancelled' || data.status === 'expired') {
            clearInterval(autoRefreshTimers[orderId]);
            delete autoRefreshTimers[orderId];
            showToast(`Rental ${data.status}.`, 'error');
            setTimeout(() => document.getElementById(`active-order-${orderId}`)?.remove(), 1500);
        }
        // status === 'waiting' → keep polling silently
    } catch (_) {}
}

/* ══════ Resend SMS ══════ */
async function resendSms(orderId) {
    const btn = document.getElementById(`resend-btn-${orderId}`);
    if (!btn || btn.disabled) return;
    btn.disabled = true;
    btn.style.color = '#60a5fa';
    btn.innerHTML = btn.innerHTML.replace('Resend SMS', 'Requesting…');

    try {
        const r = await fetch(`/dashboard/virtual-numbers/${orderId}/resend`, {
            method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' },
        });
        const d = await r.json();
        if (d.success) {
            showToast('New SMS requested — code will appear automatically.', 'success');
        } else {
            showToast(d.error ?? 'Could not request new SMS.', 'error');
        }
    } catch (_) {
        showToast('Error requesting new SMS.', 'error');
    }

    // Re-enable after 30s cooldown
    setTimeout(() => {
        if (btn) {
            btn.disabled = false;
            btn.style.color = '#94a3b8';
            btn.innerHTML = btn.innerHTML.replace('Requesting…', 'Resend SMS');
        }
    }, 30000);
}

/* ══════ Countdown timers ══════ */
function startCountdownTimers() {
    document.querySelectorAll('[data-expires]').forEach(el => {
        const orderId = el.id.replace('timer-','');
        const expires = parseInt(el.dataset.expires) * 1000;
        const barEl   = document.getElementById(`timer-bar-${orderId}`);
        const TOTAL   = 20 * 60 * 1000;

        function tick() {
            const remaining = Math.max(0, expires - Date.now());
            const pct       = (remaining / TOTAL) * 100;
            const mins      = Math.floor(remaining / 60000);
            const secs      = Math.floor((remaining % 60000) / 1000);
            el.textContent  = `${String(mins).padStart(2,'0')}:${String(secs).padStart(2,'0')}`;
            el.className    = `text-xs font-mono font-semibold ${remaining < 300000 ? 'text-red-400' : 'text-slate-300'}`;
            if (barEl) {
                barEl.style.width = pct + '%';
                barEl.className = `timer-fill${pct < 25 ? ' danger' : ''}`;
            }
            if (remaining > 0) setTimeout(tick, 1000);
        }
        tick();
    });
}

/* ══════ Copy to clipboard — 3-layer bulletproof ══════ */
function copyCode(text, btn, silent) {
    if (!text) return;

    function markCopied(success) {
        if (btn) {
            const orig = btn.innerHTML;
            btn.innerHTML = success
                ? '<svg class="w-4 h-4" fill="none" stroke="#4ade80" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>'
                : '<svg class="w-4 h-4" fill="none" stroke="#f87171" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';
            btn.classList.add(success ? 'copied' : '');
            setTimeout(() => { btn.innerHTML = orig; btn.classList.remove('copied'); }, 2000);
        }
    }

    function fallbackExecCopy() {
        try {
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.cssText = 'position:fixed;top:0;left:0;width:1px;height:1px;opacity:0;';
            document.body.appendChild(ta);
            ta.focus(); ta.select();
            const ok = document.execCommand('copy');
            document.body.removeChild(ta);
            if (ok) {
                markCopied(true);
                if (!silent) showToast(`Copied: ${text}`, 'success');
                else showToast(`✓ Code ${text} received & copied!`, 'success');
                return true;
            }
        } catch (_) {}
        return false;
    }

    function showManualCopy() {
        markCopied(false);
        // Show inline modal so user can tap the pre-selected text
        const existing = document.getElementById('copy-fallback-modal');
        if (existing) existing.remove();
        const modal = document.createElement('div');
        modal.id = 'copy-fallback-modal';
        modal.style.cssText = 'position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.6)';
        modal.innerHTML = `
            <div style="background:#0f1f3d;border:1px solid #1e3a5f;border-radius:1rem;padding:1.5rem;width:min(90vw,380px);text-align:center">
                <p style="color:#e2e8f0;font-weight:600;margin-bottom:.75rem;">Tap below and copy</p>
                <input id="copy-fallback-input" value="${text.replace(/"/g,'&quot;')}" readonly
                       style="width:100%;background:#131f35;border:1px solid #1e3a5f;border-radius:.5rem;
                              padding:.6rem 1rem;color:#fff;font-family:monospace;font-size:1.25rem;
                              letter-spacing:.15em;text-align:center;outline:none;">
                <p style="color:#64748b;font-size:.75rem;margin-top:.5rem;">Select all → Copy (Ctrl+C / ⌘C)</p>
                <button onclick="document.getElementById('copy-fallback-modal').remove()"
                        style="margin-top:1rem;padding:.4rem 1.2rem;border-radius:.4rem;
                               background:#1e3a5f;color:#94a3b8;font-size:.8rem;border:none;cursor:pointer;">
                    Close
                </button>
            </div>`;
        document.body.appendChild(modal);
        modal.addEventListener('click', e => { if (e.target === modal) modal.remove(); });
        setTimeout(() => {
            const inp = document.getElementById('copy-fallback-input');
            if (inp) { inp.focus(); inp.select(); }
        }, 50);
    }

    // Layer 1: Modern Clipboard API
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            markCopied(true);
            if (!silent) showToast(`Copied: ${text}`, 'success');
            else showToast(`✓ Code ${text} received & copied!`, 'success');
        }).catch(() => {
            if (!fallbackExecCopy()) showManualCopy();
        });
        return;
    }

    // Layer 2: execCommand fallback
    if (!fallbackExecCopy()) {
        // Layer 3: manual selection modal
        showManualCopy();
    }
}

/* ══════ Toast notifications ══════ */
function showToast(message, type = 'success') {
    const area  = document.getElementById('toast-area');
    const toast = document.createElement('div');
    const icon  = type === 'success'
        ? '<svg class="w-4 h-4 flex-shrink-0" style="color:#60a5fa" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
        : type === 'error'
        ? '<svg class="w-4 h-4 flex-shrink-0 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>'
        : '<svg class="w-4 h-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
    toast.className = `toast pointer-events-auto ${type}`;
    toast.innerHTML = `${icon}<span>${message}</span>`;
    area.appendChild(toast);
    setTimeout(() => { toast.style.animation = 'slide-out .25s ease forwards'; setTimeout(() => toast.remove(), 250); }, 3500);
}

/* ══════ Close modal on Escape ══════ */
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeBuyModal(); });

/* ══════ Init ══════ */
document.addEventListener('DOMContentLoaded', () => {
    @if($enabled)
    loadCountries();
    loadServices();
    @endif
    startCountdownTimers();

    // Always start live SMS polling immediately for any active orders on the page
    startAutoRefresh();

    // Request push notification permission if there are active orders
    if (document.querySelectorAll('[id^="active-order-"]').length > 0) {
        requestNotificationPermission();
    }

    // Auto-switch to Active Rentals tab if redirected after a purchase
    const params = new URLSearchParams(location.search);
    if (params.get('tab') === 'active') {
        const activeTabBtn = document.querySelector('.vn-tab:nth-child(2)');
        if (activeTabBtn) switchTab('active', activeTabBtn);
        history.replaceState(null, '', location.pathname);
    }
});
</script>
@endpush
