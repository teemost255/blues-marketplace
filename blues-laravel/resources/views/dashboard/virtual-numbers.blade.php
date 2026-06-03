@extends('layouts.dashboard')
@section('title', 'Virtual Numbers')

@push('styles')
<style>
    .vn-tab { position:relative; padding: 0.75rem 1.25rem; font-size:0.875rem; font-weight:500; color:#94a3b8; cursor:pointer; white-space:nowrap; transition:color .15s; border-bottom:2px solid transparent; display:flex; align-items:center; gap:0.5rem; }
    .vn-tab.active { color:#f97316; border-bottom-color:#f97316; }
    .vn-tab:hover:not(.active){ color:#e2e8f0; }
    .vn-tab-panel { display:none; }
    .vn-tab-panel.active { display:block; }
    .svc-card { background:#1e2433; border:1px solid #2a3347; border-radius:0.75rem; padding:0.875rem 1rem; display:flex; align-items:center; gap:0.75rem; transition:border-color .15s, background .15s; }
    .svc-card:hover { border-color:#f97316; background:#232b3e; }
    .svc-badge { width:2.25rem; height:2.25rem; border-radius:0.5rem; background:linear-gradient(135deg,#7c3aed,#a855f7); display:flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:700; color:#fff; flex-shrink:0; }
    .buy-btn { background:#f97316; color:#fff; font-size:0.75rem; font-weight:700; padding:0.375rem 0.75rem; border-radius:0.5rem; border:none; cursor:pointer; white-space:nowrap; transition:background .15s; display:flex; align-items:center; gap:0.25rem; }
    .buy-btn:hover { background:#ea6c0a; }
    .buy-btn:disabled { background:#4b5563; cursor:not-allowed; }
    .vn-search { background:#1e2433; border:1px solid #2a3347; border-radius:0.5rem; padding:0.625rem 0.875rem 0.625rem 2.5rem; color:#e2e8f0; font-size:0.875rem; outline:none; width:100%; }
    .vn-search:focus { border-color:#f97316; }
    .vn-select { background:#1e2433; border:1px solid #2a3347; border-radius:0.5rem; padding:0.625rem 2rem 0.625rem 0.875rem; color:#e2e8f0; font-size:0.875rem; outline:none; appearance:none; cursor:pointer; }
    .vn-select:focus { border-color:#f97316; }
    .active-card { background:#1e2433; border:1px solid #2a3347; border-radius:0.75rem; padding:1.125rem; }
    .status-waiting { background:#422006; color:#fb923c; border:1px solid #7c2d12; border-radius:9999px; padding:0.25rem 0.75rem; font-size:0.7rem; font-weight:600; }
    .status-received { background:#052e16; color:#4ade80; border:1px solid #14532d; border-radius:9999px; padding:0.25rem 0.75rem; font-size:0.7rem; font-weight:600; }
    .modal-backdrop { position:fixed; inset:0; background:rgba(0,0,0,.7); z-index:50; display:flex; align-items:center; justify-content:center; padding:1rem; }
    .modal-box { background:#1a2035; border:1px solid #2a3347; border-radius:1rem; padding:1.5rem; width:100%; max-width:24rem; }
    #buy-modal { display:none; }
    #buy-modal.open { display:flex; }
    .empty-state { padding:4rem 1rem; text-align:center; color:#64748b; }
    .empty-state svg { margin:0 auto 1rem; opacity:.4; }
</style>
@endpush

@section('content')

{{-- Page header row --}}
<div class="flex flex-wrap items-start justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:linear-gradient(135deg,#f97316,#ea580c)">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
        </div>
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-lg font-bold text-white">Virtual Numbers</h1>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full" style="background:#422006;color:#fb923c;">HeroSMS</span>
            </div>
            <p class="text-xs text-slate-400 mt-0.5">Receive SMS codes for any service worldwide</p>
        </div>
    </div>

    {{-- Wallet balance card --}}
    <div class="flex items-center gap-3 bg-slate-800 border border-slate-700 rounded-xl px-4 py-3">
        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
        <div>
            <p class="text-xs text-slate-400">Balance</p>
            <p class="text-sm font-bold text-white" id="hdr-balance">₦{{ number_format($wallet->balance, 2) }}</p>
        </div>
        <a href="{{ route('dashboard.wallet') }}" class="ml-2 text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors" style="background:#f97316;color:#fff;">Top up</a>
    </div>
</div>

@if(!$enabled)
<div class="rounded-xl border px-5 py-4 mb-5 flex items-start gap-3" style="background:#422006;border-color:#7c2d12;color:#fb923c;">
    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <div>
        <p class="font-semibold text-sm">Virtual numbers are temporarily unavailable</p>
        <p class="text-xs mt-0.5 opacity-75">Our team is working on restoring this service. Please check back soon.</p>
    </div>
</div>
@endif

{{-- Main card --}}
<div class="rounded-xl border overflow-hidden" style="background:#161c2c;border-color:#1e2a3a;">

    {{-- Tab bar --}}
    <div class="border-b flex overflow-x-auto" style="border-color:#1e2a3a;">
        <button class="vn-tab active" onclick="switchTab('services', this)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            Available Services
        </button>
        <button class="vn-tab" onclick="switchTab('active', this)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            Active Rentals
            @php $activeCount = $orders->getCollection()->whereIn('status',['waiting','received'])->count(); @endphp
            @if($activeCount > 0)
            <span class="text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center" style="background:#f97316;color:#fff;">{{ $activeCount }}</span>
            @endif
        </button>
        <button class="vn-tab" onclick="switchTab('history', this)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Rental History
        </button>
    </div>

    {{-- ======================== TAB: Available Services ======================== --}}
    <div class="vn-tab-panel active" id="tab-services">
        @if($enabled)
        {{-- Filter bar --}}
        <div class="flex flex-wrap items-center gap-3 p-4 border-b" style="border-color:#1e2a3a;">
            <div class="relative flex-1 min-w-48">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="svc-search" class="vn-search" placeholder="Search services…" oninput="filterServices()">
            </div>
            <div class="relative">
                <select id="country-filter" class="vn-select pr-8" onchange="loadServices()">
                    <option value="0">— All Countries —</option>
                </select>
                <svg class="w-4 h-4 absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
            <div class="relative">
                <select id="sort-filter" class="vn-select pr-8" onchange="loadServices()">
                    <option value="az">Sort: A–Z</option>
                    <option value="za">Sort: Z–A</option>
                    <option value="count">Most Available</option>
                </select>
                <svg class="w-4 h-4 absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
            <span class="text-xs text-slate-400 whitespace-nowrap" id="svc-count-label"></span>
        </div>

        {{-- Section label --}}
        <div class="px-4 pt-3 pb-1 flex items-center gap-2">
            <span class="text-base">🌍</span>
            <span class="text-sm font-semibold text-white" id="svc-section-label">All Countries</span>
            <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:#1e2a3a;color:#94a3b8;" id="svc-section-count"></span>
        </div>

        {{-- Service grid --}}
        <div class="p-4 pt-2" id="svc-grid-wrap">
            <div id="svc-grid" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                {{-- Skeleton --}}
                @for($i = 0; $i < 8; $i++)
                <div class="svc-card animate-pulse">
                    <div class="w-9 h-9 rounded-lg" style="background:#2a3347;"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-3 w-24 rounded" style="background:#2a3347;"></div>
                        <div class="h-2.5 w-16 rounded" style="background:#2a3347;"></div>
                    </div>
                    <div class="space-y-2 text-right">
                        <div class="h-4 w-12 rounded" style="background:#2a3347;"></div>
                        <div class="h-6 w-14 rounded" style="background:#2a3347;"></div>
                    </div>
                </div>
                @endfor
            </div>
            <div id="svc-empty" class="hidden empty-state">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <p class="text-sm">No services found</p>
            </div>
        </div>
        @else
        <div class="empty-state">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            <p class="text-sm mt-2">Service currently unavailable</p>
        </div>
        @endif
    </div>

    {{-- ======================== TAB: Active Rentals ======================== --}}
    <div class="vn-tab-panel" id="tab-active">
        @php $activeOrders = $orders->getCollection()->whereIn('status', ['waiting', 'received']); @endphp
        @if($activeOrders->isEmpty())
        <div class="empty-state">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            <p class="text-sm mt-2">No active rentals</p>
            <p class="text-xs mt-1">Go to Available Services to rent a number</p>
            <button onclick="switchTab('services', document.querySelector('.vn-tab'))" class="mt-3 text-xs font-semibold px-4 py-2 rounded-lg" style="background:#f97316;color:#fff;">Browse Services</button>
        </div>
        @else
        <div class="p-4 space-y-3">
            @foreach($activeOrders as $order)
            <div class="active-card" id="active-order-{{ $order->id }}">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:linear-gradient(135deg,#f97316,#ea580c);">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <div>
                            <p class="font-bold text-white font-mono tracking-wide">+{{ $order->phone_number }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $order->service_name }} · {{ $order->country_name ?: 'Country #'.$order->country }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($order->status === 'received')
                        <span class="status-received">✓ SMS Received</span>
                        @else
                        <span class="status-waiting flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-orange-400 inline-block animate-pulse"></span>Waiting for SMS
                        </span>
                        @endif
                        <span class="text-xs font-bold" style="color:#f97316;">₦{{ number_format($order->cost, 0) }}</span>
                    </div>
                </div>

                {{-- Code display --}}
                <div id="sms-code-{{ $order->id }}" class="{{ $order->sms_code ? '' : 'hidden' }} mt-3 rounded-xl px-4 py-3 flex items-center gap-3" style="background:#052e16;border:1px solid #14532d;">
                    <svg class="w-5 h-5 flex-shrink-0" style="color:#4ade80;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="text-xs mb-0.5" style="color:#4ade80;">Verification Code</p>
                        <p class="text-2xl font-bold text-white tracking-widest font-mono" id="code-text-{{ $order->id }}">{{ $order->sms_code ?? '' }}</p>
                    </div>
                    <button onclick="copyCode('{{ $order->sms_code ?? '' }}')" class="ml-auto text-slate-400 hover:text-white" title="Copy">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </button>
                </div>

                @if($order->expires_at && $order->status === 'waiting')
                <p class="text-xs text-slate-500 mt-2">Expires {{ $order->expires_at->diffForHumans() }}</p>
                @endif

                <div class="flex items-center gap-2 mt-3 flex-wrap">
                    @if($order->status === 'waiting')
                    <button onclick="checkStatus({{ $order->id }})" class="flex items-center gap-1.5 text-xs font-semibold px-3 py-2 rounded-lg transition-colors" style="background:#1e2a3a;color:#e2e8f0;" onmouseover="this.style.background='#2a3a5a'" onmouseout="this.style.background='#1e2a3a'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Check SMS
                    </button>
                    @endif
                    @if($order->status === 'received')
                    <button onclick="completeOrder({{ $order->id }})" class="flex items-center gap-1.5 text-xs font-semibold px-3 py-2 rounded-lg transition-colors" style="background:#052e16;color:#4ade80;border:1px solid #14532d;">
                        ✓ Mark Complete
                    </button>
                    @endif
                    @if($order->status === 'waiting')
                    <button onclick="cancelOrder({{ $order->id }})" class="text-xs font-semibold px-3 py-2 rounded-lg text-slate-400 hover:text-red-400 transition-colors" style="background:#1e2a3a;" onmouseover="this.style.background='#2a1a1a'" onmouseout="this.style.background='#1e2a3a'">
                        Cancel
                    </button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ======================== TAB: Rental History ======================== --}}
    <div class="vn-tab-panel" id="tab-history">
        @if($orders->isEmpty())
        <div class="empty-state">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm mt-2">No rental history yet</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs uppercase text-slate-500 border-b" style="border-color:#1e2a3a;">
                        <th class="px-5 py-3 text-left">Phone Number</th>
                        <th class="px-5 py-3 text-left">Service</th>
                        <th class="px-5 py-3 text-left">SMS Code</th>
                        <th class="px-5 py-3 text-left">Cost</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Date</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($orders as $order)
                <tr class="border-b transition-colors hover:bg-white/5" style="border-color:#1e2a3a;">
                    <td class="px-5 py-3 font-mono text-white text-sm">+{{ $order->phone_number ?? '—' }}</td>
                    <td class="px-5 py-3">
                        <p class="text-white">{{ $order->service_name }}</p>
                        <p class="text-xs text-slate-500">{{ $order->country_name ?: 'Country #'.$order->country }}</p>
                    </td>
                    <td class="px-5 py-3">
                        @if($order->sms_code)
                        <span class="font-mono font-bold tracking-widest" style="color:#4ade80;">{{ $order->sms_code }}</span>
                        @else
                        <span class="text-slate-600">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 font-bold" style="color:#f97316;">₦{{ number_format($order->cost, 2) }}</td>
                    <td class="px-5 py-3">
                        @php
                        $badge = match($order->status) {
                            'waiting'   => ['bg:#422006;color:#fb923c;border:1px solid #7c2d12;',  'Waiting'],
                            'received'  => ['bg:#052e16;color:#4ade80;border:1px solid #14532d;',  'Received'],
                            'completed' => ['bg:#0f2c14;color:#4ade80;border:1px solid #166534;',  'Completed'],
                            'cancelled' => ['bg:#1c1c1c;color:#9ca3af;border:1px solid #374151;',  'Cancelled'],
                            'expired'   => ['bg:#1c1c1c;color:#6b7280;border:1px solid #374151;',  'Expired'],
                            default     => ['bg:#1e2433;color:#94a3b8;border:1px solid #2a3347;',  ucfirst($order->status)],
                        };
                        @endphp
                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full inline-block" style="{{ $badge[0] }}">{{ $badge[1] }}</span>
                    </td>
                    <td class="px-5 py-3 text-slate-500 text-xs whitespace-nowrap">{{ $order->created_at->format('M j, Y g:ia') }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="px-5 py-4 border-t" style="border-color:#1e2a3a;">{{ $orders->links() }}</div>
        @endif
        @endif
    </div>

</div>{{-- /main card --}}

{{-- ======================== BUY MODAL ======================== --}}
<div id="buy-modal" class="modal-backdrop" onclick="if(event.target===this)closeBuyModal()">
    <div class="modal-box">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-base font-bold text-white" id="modal-service-name">Buy Number</h3>
                <p class="text-xs text-slate-400 mt-0.5" id="modal-service-count"></p>
            </div>
            <button onclick="closeBuyModal()" class="text-slate-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="mb-4">
            <label class="block text-xs text-slate-400 mb-1.5">Select Country</label>
            <div class="relative">
                <select id="modal-country" class="vn-select w-full pr-8">
                    <option value="">Loading countries…</option>
                </select>
                <svg class="w-4 h-4 absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
        </div>

        <div class="rounded-lg px-4 py-3 mb-5 flex items-center justify-between" style="background:#0f1a2a;border:1px solid #1e2a3a;">
            <span class="text-xs text-slate-400">Cost from wallet</span>
            <span class="text-lg font-bold" style="color:#f97316;">₦{{ number_format($price, 2) }}</span>
        </div>

        <div id="modal-error" class="hidden text-xs rounded-lg px-3 py-2.5 mb-4" style="background:#422006;color:#fb923c;border:1px solid #7c2d12;"></div>

        <div class="flex gap-3">
            <button onclick="closeBuyModal()" class="flex-1 py-2.5 text-sm font-semibold rounded-lg text-slate-400 transition-colors" style="background:#1e2a3a;" onmouseover="this.style.background='#2a3a5a'" onmouseout="this.style.background='#1e2a3a'">Cancel</button>
            <button id="confirm-buy-btn" onclick="confirmBuy()" class="flex-1 py-2.5 text-sm font-semibold rounded-lg text-white flex items-center justify-center gap-2" style="background:#f97316;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Confirm — ₦{{ number_format($price, 0) }}
            </button>
        </div>
    </div>
</div>

{{-- Toast container --}}
<div id="toast-area" class="fixed bottom-5 right-5 z-50 space-y-2"></div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';
const PRICE = {{ $price }};

let allServices = [];
let countryMap  = {};
let selectedService = null;

/* ---- Tab switching ---- */
function switchTab(tabId, btn) {
    document.querySelectorAll('.vn-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.vn-tab-panel').forEach(p => p.classList.remove('active'));
    if (btn) btn.classList.add('active');
    document.getElementById('tab-' + tabId)?.classList.add('active');
}

/* ---- Country loading ---- */
async function loadCountries() {
    try {
        const resp = await fetch('{{ route("dashboard.virtual-numbers.countries") }}', {
            headers: { 'Accept': 'application/json' }
        });
        const data = await resp.json();
        if (!data.countries?.length) return;

        const filterSel  = document.getElementById('country-filter');
        const modalSel   = document.getElementById('modal-country');

        filterSel.innerHTML = '<option value="0">— All Countries —</option>';
        modalSel.innerHTML  = '<option value="">— Select country —</option>';

        data.countries.forEach(c => {
            countryMap[c.id] = c.name;
            const html = `<option value="${c.id}">${c.name}</option>`;
            filterSel.insertAdjacentHTML('beforeend', html);
            modalSel.insertAdjacentHTML('beforeend', html);
        });
    } catch(e) { console.error(e); }
}

/* ---- Service loading ---- */
async function loadServices() {
    const country = document.getElementById('country-filter')?.value ?? '0';
    const sort    = document.getElementById('sort-filter')?.value ?? 'az';

    const grid = document.getElementById('svc-grid');
    grid.innerHTML = Array(6).fill(0).map(() => `
        <div class="svc-card animate-pulse">
            <div class="w-9 h-9 rounded-lg" style="background:#2a3347;"></div>
            <div class="flex-1 space-y-2">
                <div class="h-3 w-24 rounded" style="background:#2a3347;"></div>
                <div class="h-2.5 w-16 rounded" style="background:#2a3347;"></div>
            </div>
            <div class="space-y-2"><div class="h-4 w-14 rounded" style="background:#2a3347;"></div><div class="h-6 w-14 rounded mt-1" style="background:#2a3347;"></div></div>
        </div>`).join('');
    document.getElementById('svc-empty').classList.add('hidden');

    try {
        const url = `{{ route("dashboard.virtual-numbers.services") }}?country=${country}&sort=${sort}`;
        const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const data = await resp.json();

        allServices = data.services ?? [];
        renderServices(allServices, country);
    } catch(e) {
        grid.innerHTML = '<div class="col-span-2 text-center py-8 text-slate-500 text-sm">Failed to load services.</div>';
    }
}

function renderServices(services, country) {
    const search = (document.getElementById('svc-search')?.value ?? '').toLowerCase();
    const filtered = search ? services.filter(s => s.name.toLowerCase().includes(search)) : services;

    const grid    = document.getElementById('svc-grid');
    const empty   = document.getElementById('svc-empty');
    const label   = document.getElementById('svc-section-label');
    const countBadge = document.getElementById('svc-section-count');
    const countLabel = document.getElementById('svc-count-label');
    const countryName = (country && country !== '0') ? (countryMap[country] ?? 'Country') : 'All Countries';

    label.textContent = countryName;
    countBadge.textContent = filtered.length;
    countLabel.textContent = filtered.length + ' services';

    if (!filtered.length) {
        grid.innerHTML = '';
        empty.classList.remove('hidden');
        return;
    }
    empty.classList.add('hidden');

    grid.innerHTML = filtered.map((s, i) => `
        <div class="svc-card">
            <div class="svc-badge">${i + 1}</div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-white truncate">${s.name}</p>
                <p class="text-xs text-slate-500 mt-0.5">${s.count.toLocaleString()} pcs</p>
            </div>
            <div class="text-right flex-shrink-0">
                <p class="text-sm font-bold mb-1.5" style="color:#f97316;">₦${PRICE.toLocaleString()}</p>
                <button class="buy-btn" onclick='openBuyModal(${JSON.stringify(s)}, "${country}")'>
                    Buy <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>`).join('');
}

function filterServices() {
    renderServices(allServices, document.getElementById('country-filter')?.value ?? '0');
}

/* ---- Buy modal ---- */
function openBuyModal(service, preCountry) {
    selectedService = service;
    document.getElementById('modal-service-name').textContent = 'Buy — ' + service.name;
    document.getElementById('modal-service-count').textContent = service.count.toLocaleString() + ' numbers available';
    document.getElementById('modal-error').classList.add('hidden');

    const modalSel = document.getElementById('modal-country');
    if (preCountry && preCountry !== '0') {
        modalSel.value = preCountry;
    } else {
        modalSel.value = '';
    }

    document.getElementById('buy-modal').classList.add('open');
}

function closeBuyModal() {
    document.getElementById('buy-modal').classList.remove('open');
    selectedService = null;
}

async function confirmBuy() {
    const country = document.getElementById('modal-country').value;
    const errDiv  = document.getElementById('modal-error');
    const btn     = document.getElementById('confirm-buy-btn');

    errDiv.classList.add('hidden');

    if (!country) { showModalErr('Please select a country.'); return; }
    if (!selectedService) return;

    btn.disabled = true;
    btn.innerHTML = `<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg> Getting number…`;

    try {
        const resp = await fetch('{{ route("dashboard.virtual-numbers.order") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({
                country: parseInt(country),
                country_name: countryMap[country] ?? '',
                service: selectedService.code
            })
        });
        const data = await resp.json();

        if (!resp.ok || !data.success) {
            showModalErr(data.error ?? 'Failed to get number. Try again.');
            return;
        }

        closeBuyModal();
        showToast('Number assigned: +' + data.order.phone_number, 'success');
        setTimeout(() => window.location.reload(), 1200);
    } catch(e) {
        showModalErr('Network error. Please try again.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Confirm — ₦{{ number_format($price, 0) }}`;
    }
}

function showModalErr(msg) {
    const d = document.getElementById('modal-error');
    d.textContent = msg;
    d.classList.remove('hidden');
}

/* ---- Active order actions ---- */
async function checkStatus(orderId) {
    try {
        const resp = await fetch(`/dashboard/virtual-numbers/${orderId}/status`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await resp.json();
        if (data.status === 'received' && data.code) {
            document.getElementById(`code-text-${orderId}`).textContent = data.code;
            document.getElementById(`sms-code-${orderId}`).classList.remove('hidden');
            showToast('SMS received! Code: ' + data.code, 'success');
            setTimeout(() => window.location.reload(), 2000);
        } else if (data.status === 'cancelled' || data.status === 'expired') {
            window.location.reload();
        } else {
            showToast('No SMS yet. Try again in a moment.', 'info');
        }
    } catch(e) { showToast('Could not check status.', 'error'); }
}

async function completeOrder(orderId) {
    if (!confirm('Mark this number as complete?')) return;
    const resp = await fetch(`/dashboard/virtual-numbers/${orderId}/complete`, {
        method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    });
    if (resp.ok) window.location.reload();
}

async function cancelOrder(orderId) {
    if (!confirm('Cancel this number? A partial refund may be issued.')) return;
    const resp = await fetch(`/dashboard/virtual-numbers/${orderId}/cancel`, {
        method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    });
    const data = await resp.json();
    if (resp.ok && data.success) {
        if (data.refunded > 0) showToast(`Cancelled. ₦${data.refunded.toFixed(2)} refunded.`, 'success');
        setTimeout(() => window.location.reload(), 1500);
    }
}

function copyCode(code) {
    navigator.clipboard.writeText(code).then(() => showToast('Code copied!', 'success'));
}

/* ---- Toast ---- */
function showToast(msg, type = 'info') {
    const styles = {
        success: 'background:#052e16;border:1px solid #14532d;color:#4ade80;',
        error:   'background:#450a0a;border:1px solid #7f1d1d;color:#f87171;',
        info:    'background:#1e2a3a;border:1px solid #2a3a5a;color:#e2e8f0;',
    };
    const t = document.createElement('div');
    t.style.cssText = `${styles[type] || styles.info} padding:.75rem 1rem; border-radius:.5rem; font-size:.875rem; box-shadow:0 4px 24px rgba(0,0,0,.4); min-width:220px;`;
    t.textContent = msg;
    document.getElementById('toast-area').appendChild(t);
    setTimeout(() => t.remove(), 3500);
}

/* ---- Init ---- */
document.addEventListener('DOMContentLoaded', () => {
    @if($enabled)
    loadCountries().then(() => loadServices());
    @endif
});
</script>
@endsection
