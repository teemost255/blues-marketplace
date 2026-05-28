@extends('layouts.app')
@section('title', 'Blues Marketplace — Buy Verified Digital Accounts')

@push('head')
<style>
/* ── Keyframes ──────────────────────────────────────────── */
@keyframes float-slow   { 0%,100%{transform:translateY(0) scale(1)}  50%{transform:translateY(-22px) scale(1.04)} }
@keyframes float-medium { 0%,100%{transform:translateY(0) scale(1)}  50%{transform:translateY(-14px) scale(1.02)} }
@keyframes pulse-ring   { 0%{transform:scale(.95);opacity:.7} 70%{transform:scale(1.15);opacity:0} 100%{transform:scale(.95);opacity:0} }
@keyframes shimmer      { 0%{background-position:-200% center} 100%{background-position:200% center} }
@keyframes slide-up     { from{opacity:0;transform:translateY(30px)} to{opacity:1;transform:translateY(0)} }
@keyframes slide-right  { from{opacity:0;transform:translateX(-24px)} to{opacity:1;transform:translateX(0)} }
@keyframes marquee      { 0%{transform:translateX(0)} 100%{transform:translateX(-50%)} }
@keyframes toast-in     { from{opacity:0;transform:translateX(110%)} to{opacity:1;transform:translateX(0)} }
@keyframes toast-out    { from{opacity:1;transform:translateX(0)} to{opacity:0;transform:translateX(110%)} }
@keyframes gradient-x   { 0%,100%{background-position:0% 50%} 50%{background-position:100% 50%} }
@keyframes spin-slow    { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }
@keyframes blink        { 0%,100%{opacity:1} 50%{opacity:0} }
@keyframes count-up     { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }

/* ── Orbs ───────────────────────────────────────────────── */
.orb { position:absolute; border-radius:9999px; filter:blur(80px); pointer-events:none; }
.orb-1 { width:420px;height:420px;background:rgba(14,165,233,.18); top:-80px;right:-80px; animation:float-slow 8s ease-in-out infinite; }
.orb-2 { width:320px;height:320px;background:rgba(99,102,241,.14); bottom:60px;left:-60px; animation:float-medium 6s ease-in-out infinite 2s; }
.orb-3 { width:200px;height:200px;background:rgba(236,72,153,.10); top:40%;left:40%; animation:float-slow 10s ease-in-out infinite 1s; }

/* ── Shimmer text ───────────────────────────────────────── */
.shimmer-text {
  background: linear-gradient(90deg,#38bdf8 20%,#818cf8 40%,#f472b6 60%,#38bdf8 80%);
  background-size: 200% auto;
  -webkit-background-clip:text; -webkit-text-fill-color:transparent;
  background-clip:text;
  animation: shimmer 4s linear infinite;
}

/* ── Scroll-reveal ──────────────────────────────────────── */
.reveal { opacity:0; transform:translateY(28px); transition:opacity .65s ease, transform .65s ease; }
.reveal.visible { opacity:1; transform:translateY(0); }
.reveal-left  { opacity:0; transform:translateX(-28px); transition:opacity .65s ease, transform .65s ease; }
.reveal-left.visible { opacity:1; transform:translateX(0); }
.reveal-scale { opacity:0; transform:scale(.92); transition:opacity .55s ease, transform .55s ease; }
.reveal-scale.visible { opacity:1; transform:scale(1); }

/* ── Category card glow ─────────────────────────────────── */
.cat-card { transition:all .3s ease; }
.cat-card:hover { transform:translateY(-6px) scale(1.04); }
.cat-card.fb:hover  { box-shadow:0 0 32px rgba(59,130,246,.35); }
.cat-card.ig:hover  { box-shadow:0 0 32px rgba(236,72,153,.35); }
.cat-card.tt:hover  { box-shadow:0 0 32px rgba(139,92,246,.35); }
.cat-card.num:hover { box-shadow:0 0 32px rgba(16,185,129,.35); }

/* ── Listing card ───────────────────────────────────────── */
.listing-card { transition:all .3s ease; }
.listing-card:hover { transform:translateY(-5px); box-shadow:0 0 0 1px rgba(14,165,233,.4), 0 20px 40px rgba(0,0,0,.4); }

/* ── FAQ ────────────────────────────────────────────────── */
.faq-body { max-height:0; overflow:hidden; transition:max-height .4s ease, padding .3s ease; }
.faq-body.open { max-height:300px; }

/* ── Marquee ────────────────────────────────────────────── */
.marquee-track { display:flex; width:max-content; animation:marquee 22s linear infinite; }
.marquee-track:hover { animation-play-state:paused; }

/* ── Gradient animated bg ───────────────────────────────── */
.grad-bg {
  background: linear-gradient(270deg, #0ea5e9, #6366f1, #ec4899, #0ea5e9);
  background-size: 600% 600%;
  animation: gradient-x 10s ease infinite;
}

/* ── Toast ──────────────────────────────────────────────── */
.activity-toast { position:fixed; bottom:24px; right:24px; z-index:9999; display:flex; flex-direction:column; gap:10px; pointer-events:none; }
.toast-item { background:#1e293b; border:1px solid #334155; border-left:3px solid #0ea5e9; border-radius:12px; padding:12px 16px; min-width:260px; max-width:320px; display:flex; align-items:center; gap:12px; box-shadow:0 8px 32px rgba(0,0,0,.5); pointer-events:auto; }
.toast-item.toast-enter { animation: toast-in .4s ease forwards; }
.toast-item.toast-exit  { animation: toast-out .4s ease forwards; }

/* ── Testimonial dots ───────────────────────────────────── */
.testi-dot { width:8px;height:8px;border-radius:9999px;background:#334155;transition:all .3s ease;cursor:pointer; }
.testi-dot.active { width:24px;background:#0ea5e9; }

/* ── Cursor blink ───────────────────────────────────────── */
.cursor { display:inline-block; width:3px; height:1em; background:#0ea5e9; margin-left:2px; vertical-align:text-bottom; animation:blink 1s step-end infinite; }

/* ── Grid bg ────────────────────────────────────────────── */
.grid-bg {
  background-image: linear-gradient(rgba(14,165,233,.05) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(14,165,233,.05) 1px, transparent 1px);
  background-size: 60px 60px;
}

/* ── Step connector ─────────────────────────────────────── */
.step-line { position:absolute; top:28px; left:calc(50% + 40px); right:calc(-50% + 40px); height:2px; background:linear-gradient(90deg,#0ea5e9,#6366f1); opacity:.35; }
</style>
@endpush

@section('content')

{{-- ═══════════════════════════════════════════════════════
     HERO
═══════════════════════════════════════════════════════ --}}
<section class="relative overflow-hidden bg-slate-900 grid-bg min-h-[92vh] flex items-center py-20 px-4">
    {{-- Orbs --}}
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
    {{-- Radial glow --}}
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_60%_at_50%_-10%,rgba(14,165,233,0.18),transparent)]"></div>

    <div class="relative max-w-5xl mx-auto text-center w-full">
        {{-- Badge --}}
        <div class="inline-flex items-center gap-2 bg-brand/10 border border-brand/30 text-brand text-xs font-semibold px-4 py-1.5 rounded-full mb-8" style="animation:slide-up .6s ease both">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-brand"></span>
            </span>
            Trusted Digital Accounts Marketplace
        </div>

        {{-- Headline with typing effect --}}
        <h1 class="text-5xl sm:text-6xl lg:text-7xl font-extrabold text-white leading-tight mb-6" style="animation:slide-up .7s ease .1s both">
            Buy Verified<br>
            <span class="shimmer-text" id="typing-target">Digital Accounts</span><span class="cursor" id="cursor"></span>
        </h1>

        <p class="text-xl text-slate-400 max-w-2xl mx-auto mb-10" style="animation:slide-up .7s ease .2s both">
            Social Media accounts &amp; Virtual Numbers —
            <span class="text-white font-medium">all verified, delivered instantly.</span>
        </p>

        {{-- CTAs --}}
        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12" style="animation:slide-up .7s ease .3s both">
            <a href="{{ route('dashboard.marketplace') }}" class="group relative inline-flex items-center justify-center gap-2 text-white font-bold px-8 py-4 rounded-xl text-base overflow-hidden">
                <span class="absolute inset-0 grad-bg opacity-90 group-hover:opacity-100 transition-opacity"></span>
                <span class="relative flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Browse Marketplace
                </span>
            </a>
            @guest
            <a href="{{ route('register') }}" class="group inline-flex items-center justify-center gap-2 border border-slate-600 hover:border-brand bg-slate-800/60 backdrop-blur text-slate-300 hover:text-white font-bold px-8 py-4 rounded-xl text-base transition-all hover:bg-slate-800">
                <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Create Free Account
            </a>
            @endguest
        </div>

        {{-- Trust mini-badges --}}
        <div class="flex flex-wrap items-center justify-center gap-6 text-xs text-slate-500" style="animation:slide-up .7s ease .4s both">
            @foreach(['Instant Delivery','Verified Accounts','Secure Wallet','24/7 Support'] as $badge)
            <span class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                {{ $badge }}
            </span>
            @endforeach
        </div>
    </div>

    {{-- Scroll indicator --}}
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-1 text-slate-600" style="animation:float-medium 2s ease-in-out infinite">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════
     ANIMATED STATS
═══════════════════════════════════════════════════════ --}}
<section class="bg-slate-800 border-y border-slate-700 relative overflow-hidden" id="stats-section">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(14,165,233,0.06),transparent_70%)]"></div>
    <div class="relative max-w-6xl mx-auto px-4 py-10 grid grid-cols-2 md:grid-cols-4 gap-0 divide-x divide-slate-700 text-center">
        @php
        $statItems = [
            ['val' => $stats['listings'],   'label' => 'Active Listings',    'suffix' => '+', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', 'color' => 'text-brand'],
            ['val' => $stats['users'],      'label' => 'Happy Customers',    'suffix' => '+', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color' => 'text-purple-400'],
            ['val' => $stats['sales'],      'label' => 'Completed Sales',    'suffix' => '+', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'text-green-400'],
            ['val' => $stats['categories'], 'label' => 'Account Categories', 'suffix' => '',  'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z', 'color' => 'text-pink-400'],
        ];
        @endphp
        @foreach($statItems as $i => $s)
        <div class="px-6 py-4 reveal" style="transition-delay:{{ $i * 100 }}ms">
            <div class="flex justify-center mb-2">
                <svg class="w-5 h-5 {{ $s['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $s['icon'] }}"/></svg>
            </div>
            <p class="text-3xl font-extrabold text-white stat-count" data-target="{{ $s['val'] }}">0</p>
            <p class="text-xs text-slate-400 mt-1 font-medium uppercase tracking-wider">{{ $s['label'] }}</p>
        </div>
        @endforeach
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════
     MARQUEE — Platform names
═══════════════════════════════════════════════════════ --}}
<div class="bg-slate-900 border-b border-slate-800 py-3 overflow-hidden">
    <div class="marquee-track select-none">
        @foreach(array_fill(0, 2, ['Facebook Accounts','Instagram Accounts','TikTok Accounts','Twitter Accounts','Telegram Accounts','Virtual Numbers','Verified Profiles','Aged Accounts','High Followers','Business Pages','Creator Accounts','Phone Verified']) as $chunk)
        @foreach($chunk as $item)
        <span class="inline-flex items-center gap-2 mx-8 text-slate-500 text-sm font-medium whitespace-nowrap">
            <span class="w-1.5 h-1.5 rounded-full bg-brand/60"></span>
            {{ $item }}
        </span>
        @endforeach
        @endforeach
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     CATEGORIES
═══════════════════════════════════════════════════════ --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="text-center mb-14 reveal">
        <span class="text-xs font-bold text-brand uppercase tracking-widest">What We Offer</span>
        <h2 class="text-4xl font-bold text-white mt-2 mb-3">Browse by Category</h2>
        <p class="text-slate-400 max-w-lg mx-auto">Social Media accounts, Virtual Numbers — every account is hand-verified before listing.</p>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-5">
        @php
        $catData = [
            'Facebook'       => ['cls'=>'fb',   'border'=>'border-blue-500/25',   'ring'=>'ring-blue-500/40',   'icon_bg'=>'bg-blue-500/15',   'label_color'=>'text-blue-300',   'desc'=>'Aged & new FB accounts, pages, business',    'path'=>'M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z'],
            'Instagram'      => ['cls'=>'ig',   'border'=>'border-pink-500/25',   'ring'=>'ring-pink-500/40',   'icon_bg'=>'bg-pink-500/15',   'label_color'=>'text-pink-300',   'desc'=>'High-follower & niche IG profiles',             'path'=>'M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37zm1.5-4.87h.01M6.5 19.5h11a3 3 0 003-3v-11a3 3 0 00-3-3h-11a3 3 0 00-3 3v11a3 3 0 003 3z'],
            'TikTok'         => ['cls'=>'tt',   'border'=>'border-purple-500/25', 'ring'=>'ring-purple-500/40', 'icon_bg'=>'bg-purple-500/15', 'label_color'=>'text-purple-300', 'desc'=>'Creator & brand TikTok accounts',                'path'=>'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3'],
            'Twitter'        => ['cls'=>'tw',   'border'=>'border-sky-500/25',    'ring'=>'ring-sky-500/40',    'icon_bg'=>'bg-sky-500/15',    'label_color'=>'text-sky-300',    'desc'=>'Aged Twitter/X accounts with followers',       'path'=>'M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z'],
            'Telegram'       => ['cls'=>'tg',   'border'=>'border-cyan-500/25',   'ring'=>'ring-cyan-500/40',   'icon_bg'=>'bg-cyan-500/15',   'label_color'=>'text-cyan-300',   'desc'=>'Telegram accounts & channel memberships',      'path'=>'M21.198 2.433a2.242 2.242 0 00-1.022.215l-16.5 7.5a2.25 2.25 0 00.126 4.238l3.218 1.07 1.675 5.025a.75.75 0 001.373.142l2.116-3.527 4.29 3.206a2.25 2.25 0 003.496-1.39l2.997-15a2.25 2.25 0 00-2.769-2.479z'],
            'Virtual Numbers'=> ['cls'=>'num',  'border'=>'border-emerald-500/25','ring'=>'ring-emerald-500/40','icon_bg'=>'bg-emerald-500/15','label_color'=>'text-emerald-300','desc'=>'Virtual & physical second phone numbers',       'path'=>'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z'],
        ];
        @endphp
        @foreach($categories as $i => $cat)
        @php $d = $catData[$cat->name] ?? ['cls'=>'other','border'=>'border-slate-600','ring'=>'ring-brand/40','icon_bg'=>'bg-brand/10','label_color'=>'text-brand','desc'=>'Digital accounts','path'=>'M4 6h16M4 12h16M4 18h16']; @endphp
        <a href="{{ route('dashboard.marketplace') }}?category={{ urlencode($cat->name) }}"
           class="cat-card {{ $d['cls'] }} relative bg-slate-800/80 border {{ $d['border'] }} hover:ring-2 {{ $d['ring'] }} rounded-2xl p-6 flex flex-col items-center gap-3 overflow-hidden reveal"
           style="transition-delay:{{ $i * 80 }}ms">
            {{-- Glow bg --}}
            <div class="absolute inset-0 opacity-0 group-hover:opacity-100 bg-gradient-to-br from-white/2 to-transparent rounded-2xl transition-opacity"></div>
            {{-- Icon --}}
            <div class="w-16 h-16 rounded-2xl {{ $d['icon_bg'] }} flex items-center justify-center mb-1">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $d['path'] }}"/></svg>
            </div>
            <span class="font-bold text-white text-base">{{ $cat->name }}</span>
            <p class="text-xs text-slate-400 text-center leading-relaxed">{{ $d['desc'] }}</p>
            <span class="{{ $d['label_color'] }} text-xs font-semibold flex items-center gap-1 mt-1">
                Shop now <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </span>
        </a>
        @endforeach
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════
     FEATURED LISTINGS
═══════════════════════════════════════════════════════ --}}
@if($featuredListings->isNotEmpty())
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
    <div class="flex items-end justify-between mb-10 reveal">
        <div>
            <span class="text-xs font-bold text-brand uppercase tracking-widest">Hand-Picked</span>
            <h2 class="text-3xl font-bold text-white mt-1">Featured Listings</h2>
        </div>
        <a href="{{ route('dashboard.marketplace') }}" class="group flex items-center gap-1 text-brand hover:text-sky-300 text-sm font-semibold transition-colors">
            View all
            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
    </div>

    @php $categoryMap = $categories->keyBy('slug'); @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5" id="listings-grid">
        @foreach($featuredListings as $i => $listing)
        @php $displayImage = ($categoryMap[$listing->category]->image ?? null) ?? $listing->image; @endphp
        <div class="listing-card bg-slate-800 border border-slate-700 rounded-2xl overflow-hidden group flex flex-col reveal"
           style="transition-delay:{{ $i * 70 }}ms">
            {{-- Thumbnail --}}
            <div class="h-36 bg-gradient-to-br from-slate-700 to-slate-600 relative overflow-hidden flex items-center justify-center">
                @if($displayImage)
                    <img src="{{ $displayImage }}" alt="{{ $listing->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                @else
                    @php
                    $catGrads = ['facebook'=>'from-blue-600/40 to-blue-900/40','instagram'=>'from-pink-600/40 to-purple-900/40','tiktok'=>'from-purple-600/40 to-black/60','2nd-numbers'=>'from-emerald-600/40 to-teal-900/40'];
                    $grad = $catGrads[$listing->category] ?? 'from-brand/30 to-slate-700';
                    @endphp
                    <div class="absolute inset-0 bg-gradient-to-br {{ $grad }}"></div>
                    <svg class="relative w-10 h-10 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                @endif
                {{-- Stock badge --}}
                @if($listing->stock <= 3)
                <span class="absolute top-2 right-2 bg-red-500/90 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $listing->stock }} left</span>
                @endif
            </div>
            <div class="p-4 flex flex-col flex-1">
                @if($listing->category)
                <span class="text-[11px] text-brand font-semibold uppercase tracking-wider">{{ $listing->category }}</span>
                @endif
                <h3 class="font-semibold text-slate-200 text-sm mt-1 mb-3 line-clamp-2 group-hover:text-white transition-colors leading-snug">{{ $listing->title }}</h3>
                <div class="mt-auto flex items-center justify-between pt-3 border-t border-slate-700/60">
                    <span class="text-xl font-extrabold text-white">₦{{ number_format($listing->price, 2) }}</span>
                    <span class="bg-brand/10 hover:bg-brand text-brand hover:text-white text-xs font-bold px-3 py-1.5 rounded-lg border border-brand/30 hover:border-brand transition-all">Buy Now</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════════════
     PROMO BANNER
═══════════════════════════════════════════════════════ --}}
@if($promoBannerEnabled && $promoBannerText)
<div id="promoBanner" class="w-full py-2.5 px-4
    @if($promoBannerColor === 'green')   bg-green-600/90
    @elseif($promoBannerColor === 'yellow') bg-yellow-500/90
    @elseif($promoBannerColor === 'red')  bg-red-600/90
    @elseif($promoBannerColor === 'purple') bg-purple-600/90
    @else bg-brand/90
    @endif
    text-white text-center text-sm font-medium relative" style="display:none" id="promoBannerInner">
    <span>{{ $promoBannerText }}</span>
    <button onclick="dismissBanner()" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white text-lg leading-none">&times;</button>
</div>
<script>
(function() {
    var key = 'promo_dismissed_{{ Str::slug($promoBannerText) }}';
    if (!localStorage.getItem(key)) {
        var b = document.getElementById('promoBannerInner');
        if (b) b.style.display = '';
    }
    window.dismissBanner = function() {
        localStorage.setItem(key, '1');
        var b = document.getElementById('promoBannerInner');
        if (b) b.style.display = 'none';
    };
})();
</script>
@endif

{{-- ═══════════════════════════════════════════════════════
     LATEST ARRIVALS
═══════════════════════════════════════════════════════ --}}
@if(isset($latestListings) && $latestListings->isNotEmpty())
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
    <div class="flex items-end justify-between mb-8 reveal">
        <div>
            <span class="text-xs font-bold text-green-400 uppercase tracking-widest">Just Added</span>
            <h2 class="text-3xl font-bold text-white mt-1">Latest Arrivals</h2>
        </div>
        <a href="{{ route('dashboard.marketplace') }}?sort=latest" class="group flex items-center gap-1 text-green-400 hover:text-green-300 text-sm font-semibold transition-colors">
            See all new
            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($latestListings as $i => $listing)
        @php $latDisplayImage = ($categoryMap[$listing->category]->image ?? null) ?? $listing->image; @endphp
        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-4 flex items-center gap-4 group reveal"
           style="transition-delay:{{ $i * 60 }}ms">
            {{-- Icon / thumb --}}
            <div class="w-14 h-14 rounded-xl flex-shrink-0 overflow-hidden flex items-center justify-center relative
                @if(!$latDisplayImage)
                    @php $latGrad = ['facebook'=>'from-blue-600/40 to-blue-900/40','instagram'=>'from-pink-600/40 to-purple-900/40','tiktok'=>'from-purple-600/40 to-black/60','2nd-numbers'=>'from-emerald-600/40 to-teal-900/40']; @endphp
                    bg-gradient-to-br {{ $latGrad[$listing->category] ?? 'from-brand/30 to-slate-700' }}
                @endif">
                @if($latDisplayImage)
                    <img src="{{ $latDisplayImage }}" alt="{{ $listing->title }}" class="w-full h-full object-cover">
                @else
                    <svg class="w-7 h-7 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                @if($listing->category)
                <span class="text-[10px] text-green-400 font-bold uppercase tracking-wider">{{ $listing->category }}</span>
                @endif
                <h3 class="font-semibold text-white text-sm mt-0.5 truncate group-hover:text-green-300 transition-colors">{{ $listing->title }}</h3>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-white font-extrabold text-base">₦{{ number_format($listing->price, 2) }}</span>
                    <span class="text-slate-500 text-xs">·</span>
                    <span class="text-slate-400 text-xs">{{ $listing->stock }} in stock</span>
                </div>
            </div>
            <div class="flex-shrink-0">
                <span class="bg-green-500/10 border border-green-500/30 text-green-400 text-xs font-bold px-3 py-1.5 rounded-lg group-hover:bg-green-500 group-hover:text-white group-hover:border-green-500 transition-all">
                    Buy
                </span>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════════════
     WHY BLUESMARKETPLACE
═══════════════════════════════════════════════════════ --}}
<section class="relative overflow-hidden py-20 bg-slate-800/40 border-y border-slate-700/50">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_left,rgba(99,102,241,0.08),transparent_60%)]"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 reveal">
            <span class="text-xs font-bold text-purple-400 uppercase tracking-widest">Why Us?</span>
            <h2 class="text-4xl font-bold text-white mt-2 mb-3">Built for Trust &amp; Speed</h2>
            <p class="text-slate-400 max-w-lg mx-auto">Every feature is designed to make your buying experience safe, fast, and reliable.</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @php
            $features = [
                ['icon'=>'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z','color'=>'text-green-400','bg'=>'bg-green-400/10','title'=>'100% Verified Accounts','desc'=>'Every listing is manually reviewed and verified by our team before going live on the platform.'],
                ['icon'=>'M13 10V3L4 14h7v7l9-11h-7z','color'=>'text-yellow-400','bg'=>'bg-yellow-400/10','title'=>'Instant Delivery','desc'=>'Account credentials are delivered to your dashboard automatically the moment your purchase is confirmed.'],
                ['icon'=>'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z','color'=>'text-brand','bg'=>'bg-brand/10','title'=>'Secure Wallet System','desc'=>'Top up your wallet and buy with confidence. Your funds are protected and never shared with third parties.'],
                ['icon'=>'M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z','color'=>'text-purple-400','bg'=>'bg-purple-400/10','title'=>'24/7 Live Support','desc'=>'Our support team is always available. Open a ticket any time and get a response within hours.'],
                ['icon'=>'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z','color'=>'text-red-400','bg'=>'bg-red-400/10','title'=>'Privacy Protected','desc'=>'Your personal data is encrypted and never sold. We respect your privacy at every step.'],
                ['icon'=>'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z','color'=>'text-pink-400','bg'=>'bg-pink-400/10','title'=>'Wishlist & Tracking','desc'=>'Save listings to your wishlist and track every order and delivery from your personal dashboard.'],
            ];
            @endphp
            @foreach($features as $i => $f)
            <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 hover:border-slate-600 transition-all hover:-translate-y-1 reveal" style="transition-delay:{{ $i * 80 }}ms">
                <div class="w-12 h-12 {{ $f['bg'] }} rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 {{ $f['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $f['icon'] }}"/></svg>
                </div>
                <h3 class="font-bold text-white text-base mb-2">{{ $f['title'] }}</h3>
                <p class="text-slate-400 text-sm leading-relaxed">{{ $f['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════
     HOW IT WORKS
═══════════════════════════════════════════════════════ --}}
<section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
    <div class="text-center mb-16 reveal">
        <span class="text-xs font-bold text-green-400 uppercase tracking-widest">Simple Process</span>
        <h2 class="text-4xl font-bold text-white mt-2 mb-3">Get Started in 3 Steps</h2>
        <p class="text-slate-400">From signup to delivery in under 5 minutes.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
        @php
        $steps = [
            ['n'=>'01','title'=>'Create Account','desc'=>'Register free in seconds. No credit card required — just your email and a password.','icon'=>'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z','color'=>'text-brand','bg'=>'bg-brand/10 border-brand/30'],
            ['n'=>'02','title'=>'Fund Your Wallet','desc'=>'Add any amount to your wallet. Instant balance, ready to spend across all listings.','icon'=>'M12 4v16m8-8H4','color'=>'text-green-400','bg'=>'bg-green-400/10 border-green-400/30'],
            ['n'=>'03','title'=>'Buy &amp; Receive','desc'=>'Pick a listing, click Buy — credentials land in your orders tab instantly.','icon'=>'M13 10V3L4 14h7v7l9-11h-7z','color'=>'text-yellow-400','bg'=>'bg-yellow-400/10 border-yellow-400/30'],
        ];
        @endphp
        @foreach($steps as $i => $step)
        <div class="relative text-center reveal" style="transition-delay:{{ $i * 120 }}ms">
            {{-- Connector line (desktop) --}}
            @if($i < 2)
            <div class="hidden md:block absolute top-10 left-[calc(50%+52px)] right-[calc(-50%+52px)] h-px bg-gradient-to-r from-slate-600 to-slate-700 z-0"></div>
            @endif
            {{-- Step number circle --}}
            <div class="relative inline-flex items-center justify-center w-20 h-20 rounded-full {{ $step['bg'] }} border-2 mx-auto mb-6 z-10">
                <svg class="w-9 h-9 {{ $step['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{!! $step['icon'] !!}"/></svg>
                <span class="absolute -top-2 -right-2 w-7 h-7 rounded-full bg-slate-900 border-2 border-slate-700 flex items-center justify-center text-xs font-bold text-slate-300">{{ $step['n'] }}</span>
            </div>
            <h3 class="font-bold text-white text-xl mb-3">{!! $step['title'] !!}</h3>
            <p class="text-slate-400 text-sm leading-relaxed max-w-xs mx-auto">{{ $step['desc'] }}</p>
        </div>
        @endforeach
    </div>
    <div class="text-center mt-12 reveal">
        @guest
        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-brand hover:bg-brand-dark text-white font-bold px-8 py-3.5 rounded-xl text-sm transition-colors">
            Start Now — It's Free
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
        @else
        <a href="{{ route('dashboard.marketplace') }}" class="inline-flex items-center gap-2 bg-brand hover:bg-brand-dark text-white font-bold px-8 py-3.5 rounded-xl text-sm transition-colors">
            Browse Listings
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
        @endauth
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════
     TESTIMONIALS
═══════════════════════════════════════════════════════ --}}
<section class="bg-slate-800/40 border-y border-slate-700/50 py-20 overflow-hidden">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 reveal">
            <span class="text-xs font-bold text-pink-400 uppercase tracking-widest">Reviews</span>
            <h2 class="text-4xl font-bold text-white mt-2">What Buyers Say</h2>
        </div>
        <div class="relative" id="testimonial-container">
            @php
            $testimonials = [
                ['name'=>'Alex M.','handle'=>'@alexm_digital','avatar'=>'A','color'=>'bg-blue-500','text'=>'Got my Facebook account within seconds of purchase. Everything matched the description perfectly. BluesMarket is now my go-to marketplace!','rating'=>5],
                ['name'=>'Sara K.','handle'=>'@sara.k','avatar'=>'S','color'=>'bg-pink-500','text'=>'The Instagram account I bought had exactly the followers count listed. Delivery was instant and support was helpful. Highly recommended!','rating'=>5],
                ['name'=>'James T.','handle'=>'@jamest99','avatar'=>'J','color'=>'bg-purple-500','text'=>'Used the wallet system for the first time and it was seamless. Topped up and bought a TikTok account in under 3 minutes. Amazing service.','rating'=>5],
                ['name'=>'Nina O.','handle'=>'@ninaofficial','avatar'=>'N','color'=>'bg-emerald-500','text'=>'Bought a 2nd number for verification and it worked flawlessly. The process is so simple — wishlist, buy, done. Love this platform!','rating'=>5],
                ['name'=>'Chris B.','handle'=>'@chrisb','avatar'=>'C','color'=>'bg-orange-500','text'=>'Support team responded to my ticket in under an hour. Had a small issue that was resolved quickly. The platform is reliable and professional.','rating'=>5],
            ];
            @endphp
            <div class="overflow-hidden">
                <div class="flex transition-transform duration-500 ease-in-out" id="testi-track">
                    @foreach($testimonials as $t)
                    <div class="min-w-full px-4">
                        <div class="max-w-2xl mx-auto bg-slate-800 border border-slate-700 rounded-2xl p-8 text-center">
                            {{-- Stars --}}
                            <div class="flex justify-center gap-1 mb-4">
                                @for($s=0;$s<5;$s++)
                                <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>
                            <p class="text-slate-300 text-lg italic leading-relaxed mb-6">"{{ $t['text'] }}"</p>
                            <div class="flex items-center justify-center gap-3">
                                <div class="w-10 h-10 rounded-full {{ $t['color'] }} flex items-center justify-center text-white font-bold text-sm">{{ $t['avatar'] }}</div>
                                <div class="text-left">
                                    <p class="text-white font-semibold text-sm">{{ $t['name'] }}</p>
                                    <p class="text-slate-500 text-xs">{{ $t['handle'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            {{-- Controls --}}
            <div class="flex items-center justify-center gap-3 mt-8">
                <button onclick="testiPrev()" class="w-9 h-9 rounded-full border border-slate-600 hover:border-brand text-slate-400 hover:text-white flex items-center justify-center transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <div class="flex gap-2" id="testi-dots">
                    @foreach($testimonials as $i => $t)
                    <button onclick="testiGo({{ $i }})" class="testi-dot {{ $i === 0 ? 'active' : '' }}"></button>
                    @endforeach
                </div>
                <button onclick="testiNext()" class="w-9 h-9 rounded-full border border-slate-600 hover:border-brand text-slate-400 hover:text-white flex items-center justify-center transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════
     MOBILE APP PREVIEW
═══════════════════════════════════════════════════════ --}}
<section class="relative overflow-hidden py-24 bg-slate-900">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_60%_80%_at_70%_50%,rgba(14,165,233,0.08),transparent_70%)]"></div>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_40%_60%_at_30%_50%,rgba(99,102,241,0.06),transparent_60%)]"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row items-center gap-16">

            {{-- Left: copy --}}
            <div class="flex-1 text-center lg:text-left reveal-left">
                <span class="text-xs font-bold text-brand uppercase tracking-widest">Always With You</span>
                <h2 class="text-4xl font-bold text-white mt-3 mb-4 leading-tight">
                    Your Marketplace,<br>
                    <span class="shimmer-text">In Your Pocket</span>
                </h2>
                <p class="text-slate-400 text-lg mb-8 max-w-lg">
                    Browse, buy, and manage your digital accounts from anywhere. Our mobile-optimised platform gives you the full experience on any screen.
                </p>
                <div class="space-y-4 mb-10">
                    @foreach([
                        ['icon'=>'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z','color'=>'text-brand','bg'=>'bg-brand/10','title'=>'Instant wallet top-up','desc'=>'Fund your wallet in seconds via Paystack'],
                        ['icon'=>'M13 10V3L4 14h7v7l9-11h-7z','color'=>'text-yellow-400','bg'=>'bg-yellow-400/10','title'=>'One-tap purchases','desc'=>'Buy & receive credentials in under a minute'],
                        ['icon'=>'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9','color'=>'text-purple-400','bg'=>'bg-purple-400/10','title'=>'Real-time notifications','desc'=>'Order updates delivered instantly'],
                    ] as $feat)
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl {{ $feat['bg'] }} flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 {{ $feat['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feat['icon'] }}"/></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-white text-sm">{{ $feat['title'] }}</p>
                            <p class="text-slate-400 text-xs">{{ $feat['desc'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                <a href="{{ route('dashboard.marketplace') }}" class="inline-flex items-center gap-2 bg-brand hover:bg-brand-dark text-white font-bold px-6 py-3 rounded-xl text-sm transition-colors">
                    Open Marketplace
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>

            {{-- Right: realistic phone mockup --}}
            <div class="flex-shrink-0 reveal flex justify-center">
                {{-- Phone outer shell --}}
                <div class="relative" style="width:280px">
                    {{-- Drop shadow glow --}}
                    <div class="absolute inset-0 blur-3xl opacity-30 bg-gradient-to-b from-brand via-purple-500 to-pink-500 rounded-[50px]"></div>

                    {{-- Phone body --}}
                    <div class="relative bg-slate-900 rounded-[44px] border-[7px] border-slate-700 shadow-2xl overflow-hidden" style="height:580px">

                        {{-- Status bar --}}
                        <div class="bg-slate-950 px-5 pt-3 pb-2 flex items-center justify-between">
                            <span class="text-white text-[10px] font-semibold">9:41</span>
                            <div class="absolute left-1/2 -translate-x-1/2 top-0 w-24 h-5 bg-slate-950 rounded-b-2xl"></div>
                            <div class="flex items-center gap-1">
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M1.5 8.5a13 13 0 0121 0M5 12a10 10 0 0114 0M8.5 15.5a6 6 0 017 0M12 19h.01"/></svg>
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M2 17h20v-6a2 2 0 00-2-2H4a2 2 0 00-2 2v6zM2 17v2h20v-2M22 9V7a2 2 0 00-2-2h-1"/></svg>
                            </div>
                        </div>

                        {{-- Screen content --}}
                        <div class="bg-slate-900 h-full overflow-hidden">
                            {{-- App header --}}
                            <div class="bg-slate-800/80 backdrop-blur border-b border-slate-700 px-4 py-3 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-lg bg-brand flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    </div>
                                    <span class="text-white text-xs font-bold">BluesMarket</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-brand/20 flex items-center justify-center">
                                        <svg class="w-3 h-3 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    </div>
                                    <div class="w-6 h-6 rounded-full bg-slate-700 flex items-center justify-center">
                                        <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                </div>
                            </div>

                            {{-- Hero micro --}}
                            <div class="px-4 py-4 bg-gradient-to-br from-brand/20 to-purple-500/10">
                                <p class="text-slate-400 text-[9px] font-medium mb-0.5">FEATURED</p>
                                <p class="text-white text-sm font-bold mb-2">Verified Social Accounts</p>
                                <div class="flex gap-1.5">
                                    @foreach(['FB','IG','TT','X','TG'] as $p)
                                    <span class="text-[8px] font-bold px-2 py-0.5 rounded-full border border-slate-600 text-slate-300 bg-slate-800">{{ $p }}</span>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Wallet strip --}}
                            <div class="mx-4 mt-3 bg-gradient-to-r from-brand to-indigo-600 rounded-xl px-3 py-2.5 flex items-center justify-between">
                                <div>
                                    <p class="text-sky-100 text-[8px] font-medium">Wallet Balance</p>
                                    <p class="text-white text-sm font-extrabold">₦12,500.00</p>
                                </div>
                                <div class="w-7 h-7 rounded-lg bg-white/20 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </div>
                            </div>

                            {{-- Category pills --}}
                            <div class="px-4 mt-3">
                                <div class="flex gap-1.5 overflow-x-hidden">
                                    @foreach([['Facebook','border-blue-500/40 text-blue-300 bg-blue-500/10'],['Instagram','border-pink-500/40 text-pink-300 bg-pink-500/10'],['TikTok','border-purple-500/40 text-purple-300 bg-purple-500/10'],['Twitter','border-sky-500/40 text-sky-300 bg-sky-500/10'],['Virtual No.','border-emerald-500/40 text-emerald-300 bg-emerald-500/10']] as [$name,$cls])
                                    <span class="text-[7px] font-semibold px-2 py-1 rounded-full border {{ $cls }} whitespace-nowrap">{{ $name }}</span>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Listing cards --}}
                            <div class="px-4 mt-3 space-y-2">
                                <p class="text-slate-500 text-[9px] font-semibold uppercase tracking-wider mb-2">Featured Listings</p>
                                @foreach([
                                    ['🟦','1k FB Page','Facebook','₦2,500','bg-blue-500/10'],
                                    ['🟪','IG 5K Followers','Instagram','₦4,800','bg-pink-500/10'],
                                    ['🔷','Virtual SIM','Virtual Numbers','₦1,200','bg-emerald-500/10'],
                                ] as [$emoji,$title,$cat,$price,$bg])
                                <div class="flex items-center justify-between bg-slate-800 rounded-xl px-3 py-2.5 border border-slate-700">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg {{ $bg }} flex items-center justify-center text-sm">{{ $emoji }}</div>
                                        <div>
                                            <p class="text-white text-[10px] font-semibold leading-tight">{{ $title }}</p>
                                            <p class="text-slate-500 text-[8px]">{{ $cat }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-white text-[10px] font-bold">{{ $price }}</p>
                                        <span class="text-[7px] font-bold text-brand bg-brand/10 px-1.5 py-0.5 rounded-md">Buy</span>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            {{-- Bottom nav bar --}}
                            <div class="absolute bottom-0 left-0 right-0 bg-slate-800/95 backdrop-blur border-t border-slate-700 px-3 py-2 flex items-center justify-around">
                                @foreach([
                                    ['M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6','text-brand'],
                                    ['M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z','text-slate-500'],
                                    ['M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z','text-slate-500'],
                                    ['M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z','text-slate-500'],
                                ] as [$path,$color])
                                <button class="flex flex-col items-center gap-0.5">
                                    <svg class="w-4 h-4 {{ $color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}"/></svg>
                                </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Side buttons --}}
                    <div class="absolute -right-2 top-24 w-1 h-8 bg-slate-600 rounded-full"></div>
                    <div class="absolute -left-2 top-20 w-1 h-6 bg-slate-600 rounded-full"></div>
                    <div class="absolute -left-2 top-28 w-1 h-10 bg-slate-600 rounded-full"></div>
                    <div class="absolute -left-2 top-40 w-1 h-10 bg-slate-600 rounded-full"></div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════
     FAQ
═══════════════════════════════════════════════════════ --}}
<section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
    <div class="text-center mb-14 reveal">
        <span class="text-xs font-bold text-yellow-400 uppercase tracking-widest">FAQ</span>
        <h2 class="text-4xl font-bold text-white mt-2 mb-3">Common Questions</h2>
        <p class="text-slate-400">Quick answers about buying on BluesMarketplace.</p>
    </div>
    @php
    $faqs = [
        ['q'=>'Are the accounts real and safe to use?','a'=>'Yes — every account is manually verified by our team before it goes live. We only list accounts that pass our authenticity checks. That said, always follow the platform\'s own terms of service when using any account.'],
        ['q'=>'How fast is delivery after purchase?','a'=>'Delivery is instant. The moment your purchase is confirmed your account credentials appear in your Orders tab in the dashboard. No waiting, no manual processing.'],
        ['q'=>'What if my account stops working?','a'=>'Open a support ticket within 48 hours of purchase. Our team will investigate and either replace the account or issue a wallet refund, depending on the situation.'],
        ['q'=>'How do I add funds to my wallet?','a'=>'Go to Dashboard → Wallet → Top Up. Enter any amount and it will be credited to your balance immediately. Your balance is shown in real-time on every page.'],
        ['q'=>'Can I buy multiple accounts?','a'=>'Absolutely — there are no limits on how many accounts you can purchase. Each listing shows the current stock count so you can plan accordingly.'],
        ['q'=>'Is my personal data kept private?','a'=>'Yes. We never sell or share your data. Passwords are hashed, sessions are encrypted, and we do not use third-party ad trackers. See our Privacy Policy for full details.'],
    ];
    @endphp
    <div class="space-y-3" id="faq-list">
        @foreach($faqs as $i => $faq)
        <div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden reveal" style="transition-delay:{{ $i * 60 }}ms">
            <button onclick="toggleFaq({{ $i }})"
                class="w-full flex items-center justify-between px-6 py-5 text-left group"
                id="faq-btn-{{ $i }}">
                <span class="font-semibold text-white text-sm group-hover:text-brand transition-colors pr-4">{{ $faq['q'] }}</span>
                <svg class="w-5 h-5 text-slate-400 flex-shrink-0 transition-transform duration-300" id="faq-icon-{{ $i }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="faq-body" id="faq-body-{{ $i }}">
                <p class="px-6 pb-5 text-slate-400 text-sm leading-relaxed">{{ $faq['a'] }}</p>
            </div>
        </div>
        @endforeach
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════
     REFERRAL CTA (logged-in users)
═══════════════════════════════════════════════════════ --}}
@auth
@php $homeProfile = Auth::user()->profile; @endphp
@if($homeProfile && $homeProfile->referral_code)
<section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pb-16 reveal">
    <div class="relative overflow-hidden bg-gradient-to-r from-brand/15 via-purple-500/10 to-pink-500/10 border border-brand/25 rounded-2xl p-8">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_right,rgba(99,102,241,0.15),transparent_60%)]"></div>
        <div class="relative flex flex-col md:flex-row items-center gap-6">
            <div class="flex-1 text-center md:text-left">
                <span class="text-xs font-bold text-brand uppercase tracking-widest">Referral Program</span>
                <h2 class="text-2xl font-bold text-white mt-2 mb-2">Earn by Sharing BluesMarket</h2>
                <p class="text-slate-400 text-sm max-w-md">Share your unique referral link. When a friend joins, you earn a wallet bonus — automatically.</p>
            </div>
            <div class="flex-shrink-0 text-center">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Your Referral Code</p>
                <code class="block text-2xl font-black font-mono text-brand tracking-widest mb-4">{{ $homeProfile->referral_code }}</code>
                <button onclick="copyHomeRef('{{ url('/r/'.$homeProfile->referral_code) }}')" id="home-ref-btn"
                    class="inline-flex items-center gap-2 bg-brand hover:bg-brand-dark text-white font-bold px-6 py-2.5 rounded-xl text-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                    Copy My Referral Link
                </button>
                <a href="{{ route('dashboard.profile') }}" class="block text-xs text-slate-500 hover:text-brand mt-2 transition-colors">View referral stats →</a>
            </div>
        </div>
    </div>
</section>
@endif
@endauth

{{-- ═══════════════════════════════════════════════════════
     LIVE ACTIVITY (if data exists)
═══════════════════════════════════════════════════════ --}}
@if($recentActivity->isNotEmpty())
<section class="bg-slate-800/30 border-y border-slate-700/50 py-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 reveal">
        <div class="text-center mb-8">
            <span class="inline-flex items-center gap-2 text-xs font-bold text-green-400 uppercase tracking-widest">
                <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-green-400"></span></span>
                Live Activity
            </span>
            <h2 class="text-2xl font-bold text-white mt-2">Recent Purchases</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3" id="activity-list">
            @foreach($recentActivity as $act)
            <div class="bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 flex items-center gap-3 activity-item">
                <div class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-white text-xs font-medium truncate"><span class="text-brand">{{ $act['user'] }}</span> bought <span class="text-slate-300">{{ \Illuminate\Support\Str::limit($act['listing'], 28) }}</span></p>
                    <p class="text-slate-500 text-[11px] mt-0.5">₦{{ $act['price'] }} · {{ $act['ago'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════════════
     CTA BANNER
═══════════════════════════════════════════════════════ --}}
<section class="relative overflow-hidden py-24 px-4">
    <div class="absolute inset-0 grad-bg opacity-10"></div>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(14,165,233,0.15),transparent_60%)]"></div>
    {{-- Animated ring --}}
    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 rounded-full border border-brand/10 animate-ping" style="animation-duration:3s"></div>
    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 rounded-full border border-brand/15 animate-ping" style="animation-duration:3s;animation-delay:.5s"></div>

    <div class="relative max-w-3xl mx-auto text-center reveal">
        @guest
        <span class="text-xs font-bold text-brand uppercase tracking-widest">Ready?</span>
        <h2 class="text-4xl sm:text-5xl font-extrabold text-white mt-3 mb-4 leading-tight">
            Start Buying <span class="shimmer-text">Digital Accounts</span><br>Today
        </h2>
        <p class="text-slate-400 mb-10 text-lg">Free to join. No monthly fees. Pay only for what you buy.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('register') }}" class="group relative inline-flex items-center justify-center gap-2 text-white font-bold px-10 py-4 rounded-xl text-base overflow-hidden">
                <span class="absolute inset-0 grad-bg"></span>
                <span class="relative">Create Free Account</span>
                <svg class="relative w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
            <a href="{{ route('dashboard.marketplace') }}" class="inline-flex items-center justify-center gap-2 border border-slate-600 hover:border-brand bg-slate-800/60 text-slate-300 hover:text-white font-bold px-10 py-4 rounded-xl text-base transition-all">
                Browse First
            </a>
        </div>
        @else
        <span class="text-xs font-bold text-brand uppercase tracking-widest">You're In</span>
        <h2 class="text-4xl font-extrabold text-white mt-3 mb-4">What Will You Buy Next?</h2>
        <p class="text-slate-400 mb-8">Hundreds of verified accounts waiting in the marketplace.</p>
        <a href="{{ route('dashboard.marketplace') }}" class="group relative inline-flex items-center justify-center gap-2 text-white font-bold px-10 py-4 rounded-xl text-base overflow-hidden">
            <span class="absolute inset-0 grad-bg"></span>
            <span class="relative">Browse Marketplace</span>
            <svg class="relative w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
        @endauth
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════
     TOAST ACTIVITY FEED (bottom-right)
═══════════════════════════════════════════════════════ --}}
@if($recentActivity->isNotEmpty())
<div class="activity-toast" id="activity-toast-container"></div>
<script>
const toastData = @json($recentActivity->values());
</script>
@endif

@endsection

@push('scripts')
<script>
/* ─── Typing animation ────────────────────────────────── */
const phrases = ['Digital Accounts','Facebook Pages','Instagram Profiles','TikTok Accounts','Phone Numbers'];
let phraseIdx = 0, charIdx = 0, deleting = false;
const el = document.getElementById('typing-target');
const cursor = document.getElementById('cursor');
function typeLoop() {
  if (!el) return;
  const phrase = phrases[phraseIdx];
  if (deleting) {
    el.textContent = phrase.substring(0, charIdx--);
    if (charIdx < 0) { deleting = false; phraseIdx = (phraseIdx + 1) % phrases.length; charIdx = 0; setTimeout(typeLoop, 500); return; }
    setTimeout(typeLoop, 50);
  } else {
    el.textContent = phrase.substring(0, ++charIdx);
    if (charIdx === phrase.length) { deleting = true; setTimeout(typeLoop, 2200); return; }
    setTimeout(typeLoop, 90);
  }
}
setTimeout(typeLoop, 1200);

/* ─── Scroll reveal ───────────────────────────────────── */
const revealEls = document.querySelectorAll('.reveal, .reveal-left, .reveal-scale');
const io = new IntersectionObserver((entries) => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); io.unobserve(e.target); } });
}, { threshold: 0.12 });
revealEls.forEach(el => io.observe(el));

/* ─── Animated stat counters ──────────────────────────── */
const statEls = document.querySelectorAll('.stat-count');
const statsIo = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      const target = parseInt(e.target.dataset.target) || 0;
      const suffix = e.target.closest('[data-suffix]')?.dataset.suffix || '';
      let current = 0;
      const duration = 1600;
      const step = Math.ceil(target / (duration / 16));
      const timer = setInterval(() => {
        current = Math.min(current + step, target);
        e.target.textContent = current.toLocaleString() + (target > 0 ? '+' : '');
        if (current >= target) clearInterval(timer);
      }, 16);
      statsIo.unobserve(e.target);
    }
  });
}, { threshold: 0.5 });
statEls.forEach(el => statsIo.observe(el));

/* ─── Testimonials carousel ───────────────────────────── */
let teIdx = 0;
const track = document.getElementById('testi-track');
const dots  = document.querySelectorAll('.testi-dot');
const teCount = dots.length;
function testiGo(i) {
  teIdx = (i + teCount) % teCount;
  track.style.transform = `translateX(-${teIdx * 100}%)`;
  dots.forEach((d,j) => d.classList.toggle('active', j === teIdx));
}
function testiNext() { testiGo(teIdx + 1); }
function testiPrev() { testiGo(teIdx - 1); }
// Auto-advance
let teTimer = setInterval(testiNext, 5000);
document.getElementById('testimonial-container').addEventListener('mouseenter', () => clearInterval(teTimer));
document.getElementById('testimonial-container').addEventListener('mouseleave', () => { teTimer = setInterval(testiNext, 5000); });

/* ─── FAQ accordion ───────────────────────────────────── */
function toggleFaq(i) {
  const body = document.getElementById(`faq-body-${i}`);
  const icon = document.getElementById(`faq-icon-${i}`);
  const open = body.classList.contains('open');
  // Close all
  document.querySelectorAll('.faq-body').forEach(b => b.classList.remove('open'));
  document.querySelectorAll('[id^="faq-icon-"]').forEach(ic => ic.style.transform = '');
  if (!open) { body.classList.add('open'); icon.style.transform = 'rotate(180deg)'; }
}

/* ─── Referral copy ───────────────────────────────────── */
function copyHomeRef(url) {
  const btn = document.getElementById('home-ref-btn');
  if (!btn) return;
  const orig = btn.innerHTML;
  function markDone() {
    btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Copied!';
    btn.classList.add('bg-green-600'); btn.classList.remove('bg-brand','hover:bg-brand-dark');
    setTimeout(() => { btn.innerHTML = orig; btn.classList.remove('bg-green-600'); btn.classList.add('bg-brand','hover:bg-brand-dark'); }, 2500);
  }
  if (navigator.clipboard && window.isSecureContext) {
    navigator.clipboard.writeText(url).then(markDone).catch(() => _fbCopy(url, markDone));
  } else {
    _fbCopy(url, markDone);
  }
}
function _fbCopy(text, cb) {
  const ta = document.createElement('textarea');
  ta.value = text; ta.style.cssText = 'position:fixed;top:0;left:0;opacity:0;pointer-events:none;';
  document.body.appendChild(ta); ta.focus(); ta.select();
  try { document.execCommand('copy'); if (cb) cb(); } catch(e) {}
  document.body.removeChild(ta);
}

/* ─── Live activity toasts ────────────────────────────── */
@if($recentActivity->isNotEmpty())
const toasts = typeof toastData !== 'undefined' ? toastData : [];
let toastIdx = 0;
function showToast() {
  if (!toasts.length) return;
  const container = document.getElementById('activity-toast-container');
  if (!container) return;
  const d = toasts[toastIdx % toasts.length];
  toastIdx++;
  const el = document.createElement('div');
  el.className = 'toast-item toast-enter';
  el.innerHTML = `
    <div style="width:32px;height:32px;border-radius:9999px;background:rgba(34,197,94,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <svg style="width:16px;height:16px;color:#4ade80" fill="none" stroke="#4ade80" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    </div>
    <div style="min-width:0;flex:1">
      <p style="color:#f1f5f9;font-size:12px;font-weight:600;margin:0">${d.user} just purchased</p>
      <p style="color:#94a3b8;font-size:11px;margin:2px 0 0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${d.listing}</p>
    </div>
    <span style="color:#0ea5e9;font-size:12px;font-weight:700;flex-shrink:0">$${d.price}</span>`;
  container.appendChild(el);
  setTimeout(() => {
    el.classList.remove('toast-enter');
    el.classList.add('toast-exit');
    setTimeout(() => el.remove(), 400);
  }, 4500);
}
// Show first after 3s, then every 7s
setTimeout(() => { showToast(); setInterval(showToast, 7000); }, 3000);
@endif
</script>
@endpush
