@extends('layouts.app')
@section('title', 'BluesMarketplace — Buy Verified Digital Accounts')

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 py-24 px-4">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_rgba(14,165,233,0.15),transparent_60%)]"></div>
    <div class="relative max-w-5xl mx-auto text-center">
        <span class="inline-block bg-brand/10 border border-brand/30 text-brand text-xs font-semibold px-3 py-1 rounded-full mb-6 uppercase tracking-wider">Trusted Digital Accounts Marketplace</span>
        <h1 class="text-5xl sm:text-6xl font-extrabold text-white leading-tight mb-6">
            Buy Verified<br><span class="text-brand">Digital Accounts</span>
        </h1>
        <p class="text-xl text-slate-400 max-w-2xl mx-auto mb-10">
            Facebook, Instagram, TikTok accounts and second phone numbers — all verified, delivered instantly.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('marketplace') }}" class="inline-flex items-center justify-center gap-2 bg-brand hover:bg-brand-dark text-white font-bold px-8 py-4 rounded-xl text-base transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Browse Marketplace
            </a>
            @guest
            <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 border border-slate-600 hover:border-brand text-slate-300 hover:text-white font-bold px-8 py-4 rounded-xl text-base transition-colors">
                Create Free Account
            </a>
            @endguest
        </div>
    </div>
</section>

{{-- Stats --}}
<section class="bg-slate-800 border-y border-slate-700">
    <div class="max-w-5xl mx-auto px-4 py-8 grid grid-cols-3 gap-0 divide-x divide-slate-700 text-center">
        <div class="px-6 py-2">
            <p class="text-3xl font-extrabold text-white">{{ number_format($stats['listings']) }}+</p>
            <p class="text-sm text-slate-400 mt-1">Active Listings</p>
        </div>
        <div class="px-6 py-2">
            <p class="text-3xl font-extrabold text-white">{{ number_format($stats['users']) }}+</p>
            <p class="text-sm text-slate-400 mt-1">Registered Users</p>
        </div>
        <div class="px-6 py-2">
            <p class="text-3xl font-extrabold text-white">{{ number_format($stats['sales']) }}+</p>
            <p class="text-sm text-slate-400 mt-1">Completed Sales</p>
        </div>
    </div>
</section>

{{-- Categories --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="text-center mb-12">
        <h2 class="text-3xl font-bold text-white mb-3">Browse by Category</h2>
        <p class="text-slate-400">Find exactly what you need from our curated selection</p>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @php
        $catIcons = [
            'Facebook'    => ['icon' => 'M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z', 'color' => 'bg-blue-500/10 border-blue-500/30 hover:border-blue-400'],
            'Instagram'   => ['icon' => 'M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37zm1.5-4.87h.01M6.5 19.5h11a3 3 0 003-3v-11a3 3 0 00-3-3h-11a3 3 0 00-3 3v11a3 3 0 003 3z', 'color' => 'bg-pink-500/10 border-pink-500/30 hover:border-pink-400'],
            'TikTok'      => ['icon' => 'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3', 'color' => 'bg-purple-500/10 border-purple-500/30 hover:border-purple-400'],
            '2nd Numbers' => ['icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z', 'color' => 'bg-emerald-500/10 border-emerald-500/30 hover:border-emerald-400'],
        ];
        @endphp
        @foreach($categories as $cat)
        @php $meta = $catIcons[$cat->name] ?? ['icon' => 'M4 6h16M4 12h16M4 18h16', 'color' => 'bg-brand/10 border-brand/30 hover:border-brand']; @endphp
        <a href="{{ route('marketplace') }}?category={{ urlencode($cat->name) }}" class="border {{ $meta['color'] }} rounded-xl p-6 flex flex-col items-center gap-3 transition-all group">
            <div class="w-12 h-12 rounded-xl {{ str_replace(['hover:border-blue-400','hover:border-pink-400','hover:border-purple-400','hover:border-emerald-400','hover:border-brand'], ['bg-blue-500/20','bg-pink-500/20','bg-purple-500/20','bg-emerald-500/20','bg-brand/20'], explode(' ', $meta['color'])[0]) }} flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $meta['icon'] }}"/></svg>
            </div>
            <span class="font-semibold text-white text-sm">{{ $cat->name }}</span>
        </a>
        @endforeach
    </div>
</section>

{{-- Featured Listings --}}
@if($featuredListings->isNotEmpty())
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-2xl font-bold text-white">Featured Listings</h2>
        <a href="{{ route('marketplace') }}" class="text-brand hover:text-sky-300 text-sm font-medium">View all →</a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($featuredListings as $listing)
        <a href="{{ route('marketplace.show', $listing->id) }}" class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden hover:border-brand/50 transition-all group">
            <div class="h-32 bg-gradient-to-br from-slate-700 to-slate-600 flex items-center justify-center">
                @if($listing->image_url)
                    <img src="{{ $listing->image_url }}" alt="{{ $listing->title }}" class="w-full h-full object-cover">
                @else
                    <svg class="w-10 h-10 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                @endif
            </div>
            <div class="p-4">
                @if($listing->category)
                    <span class="text-xs text-brand font-medium">{{ $listing->category }}</span>
                @endif
                <h3 class="font-semibold text-white text-sm mt-1 line-clamp-2 group-hover:text-brand transition-colors">{{ $listing->title }}</h3>
                <div class="flex items-center justify-between mt-3">
                    <span class="text-lg font-bold text-white">${{ number_format($listing->price, 2) }}</span>
                    <span class="text-xs text-slate-400">{{ $listing->stock }} left</span>
                </div>
            </div>
        </a>
        @endforeach
    </div>
</section>
@endif

{{-- How it works --}}
<section class="bg-slate-800/50 border-y border-slate-700 py-20">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-white text-center mb-12">How It Works</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach([
                ['step'=>'1','title'=>'Create an Account','desc'=>'Register for free and verify your email to get started.','icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                ['step'=>'2','title'=>'Top Up Wallet','desc'=>'Add funds to your wallet using our secure payment system.','icon'=>'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                ['step'=>'3','title'=>'Instant Delivery','desc'=>'Purchase any listing and receive your account credentials instantly.','icon'=>'M13 10V3L4 14h7v7l9-11h-7z'],
            ] as $step)
            <div class="text-center">
                <div class="w-14 h-14 bg-brand/10 border border-brand/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $step['icon'] }}"/></svg>
                </div>
                <div class="inline-block bg-brand text-white text-xs font-bold px-2 py-0.5 rounded-full mb-3">Step {{ $step['step'] }}</div>
                <h3 class="font-semibold text-white text-lg mb-2">{{ $step['title'] }}</h3>
                <p class="text-slate-400 text-sm">{{ $step['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
@guest
<section class="max-w-3xl mx-auto px-4 py-20 text-center">
    <h2 class="text-3xl font-bold text-white mb-4">Ready to Get Started?</h2>
    <p class="text-slate-400 mb-8">Join thousands of satisfied customers. Create your free account today.</p>
    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-brand hover:bg-brand-dark text-white font-bold px-10 py-4 rounded-xl text-base transition-colors">
        Create Free Account
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
    </a>
</section>
@endguest
@endsection
