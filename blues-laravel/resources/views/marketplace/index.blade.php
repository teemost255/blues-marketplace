@extends('layouts.dashboard')
@section('title', 'Marketplace')
@section('page-title', 'Marketplace')

@section('content')
<div class="max-w-7xl mx-auto">

    {{-- ── Hero ──────────────────────────────────────────────────────────────── --}}
    <div class="rounded-2xl bg-gradient-to-r from-[#0c1a3a] via-[#0f1f45] to-[#0c1a3a] border border-slate-700/60 p-6 mb-7 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white mb-1">Digital Marketplace</h1>
            <p class="text-sm text-slate-400">Verified accounts &amp; digital goods — delivered instantly to your dashboard</p>
        </div>
        <div class="flex items-center gap-4 flex-wrap text-sm">
            <div class="flex items-center gap-2 text-slate-300">
                <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                Instant Delivery
            </div>
            <div class="flex items-center gap-2 text-slate-300">
                <svg class="w-4 h-4 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                Verified Accounts
            </div>
            <div class="flex items-center gap-2 text-slate-300">
                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                Buyer Protection
            </div>
        </div>
    </div>

    {{-- ── Category tabs ────────────────────────────────────────────────────── --}}
    @if($categories->count())
    <div class="flex items-center gap-2 flex-wrap mb-5">
        <a href="{{ route('dashboard.marketplace') }}"
            class="px-4 py-1.5 rounded-full text-sm font-semibold border transition-all {{ !request('category') ? 'bg-brand text-white border-brand' : 'bg-slate-800 text-slate-300 border-slate-700 hover:border-brand/50 hover:text-white' }}">
            All
        </a>
        @foreach($categories as $cat)
        <a href="{{ route('dashboard.marketplace') }}?category={{ $cat->slug }}{{ request('search') ? '&search='.urlencode(request('search')) : '' }}"
            class="px-4 py-1.5 rounded-full text-sm font-semibold border transition-all {{ request('category') === $cat->slug ? 'bg-brand text-white border-brand' : 'bg-slate-800 text-slate-300 border-slate-700 hover:border-brand/50 hover:text-white' }}">
            {{ $cat->name }}
        </a>
        @endforeach
    </div>
    @endif

    {{-- ── Search + Sort bar ────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('dashboard.marketplace') }}" class="flex flex-wrap gap-2.5 mb-6 items-center">
        @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif

        <div class="relative flex-1 min-w-[200px]">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search listings…"
                class="w-full pl-9 pr-4 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl text-sm focus:outline-none focus:border-brand placeholder-slate-500">
        </div>

        <div class="relative">
            <select name="sort" class="appearance-none bg-slate-800 border border-slate-700 text-white rounded-xl px-4 py-2.5 pr-8 text-sm focus:outline-none focus:border-brand">
                <option value="">Latest</option>
                <option value="price_asc"  {{ request('sort') === 'price_asc'  ? 'selected' : '' }}>Price: Low → High</option>
                <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High → Low</option>
            </select>
            <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>

        {{-- Price range --}}
        <div class="flex items-center gap-1.5">
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs font-medium">₦</span>
                <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min"
                    min="0" step="1"
                    class="bg-slate-800 border border-slate-700 rounded-xl pl-7 pr-3 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand w-24">
            </div>
            <span class="text-slate-600 text-xs">—</span>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs font-medium">₦</span>
                <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max"
                    min="0" step="1"
                    class="bg-slate-800 border border-slate-700 rounded-xl pl-7 pr-3 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand w-24">
            </div>
        </div>

        <button type="submit" class="px-5 py-2.5 bg-brand hover:bg-brand-dark text-white font-semibold rounded-xl text-sm transition-colors">Search</button>
        @if(request()->hasAny(['search','sort','min_price','max_price']))
            <a href="{{ route('dashboard.marketplace') }}{{ request('category') ? '?category='.urlencode(request('category')) : '' }}"
               class="px-4 py-2.5 border border-slate-700 hover:border-slate-500 text-slate-400 hover:text-white rounded-xl text-sm font-medium transition-colors">
                Clear
            </a>
        @endif
    </form>

    @php $categoryMap = $categories->keyBy('slug'); @endphp

    {{-- ── Results count ────────────────────────────────────────────────────── --}}
    @if(!$listings->isEmpty())
    <p class="text-xs text-slate-500 mb-4">
        Showing {{ $listings->firstItem() }}–{{ $listings->lastItem() }} of {{ $listings->total() }} listing{{ $listings->total() !== 1 ? 's' : '' }}
        @if(request('search')) for "<span class="text-slate-300">{{ request('search') }}</span>"@endif
    </p>
    @endif

    {{-- ── Grid ─────────────────────────────────────────────────────────────── --}}
    @if($listings->isEmpty())
        <div class="flex flex-col items-center justify-center py-24 bg-slate-800/30 rounded-2xl border border-slate-700/40 text-center">
            <div class="w-14 h-14 rounded-2xl bg-slate-700 flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <p class="text-white font-semibold text-lg mb-1">No listings found</p>
            <p class="text-slate-400 text-sm">Try adjusting your search or browse all categories.</p>
            @if(request()->hasAny(['search','category','sort','min_price','max_price']))
            <a href="{{ route('dashboard.marketplace') }}" class="mt-4 px-5 py-2 bg-brand hover:bg-brand-dark text-white text-sm font-semibold rounded-xl transition-colors">Clear Filters</a>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($listings as $listing)
            @php
                $displayImage = ($categoryMap[$listing->category]->image ?? null) ?? $listing->image;
            @endphp
            <div class="listing-card bg-slate-800 border border-slate-700/60 rounded-2xl overflow-hidden flex flex-col group transition-all duration-200 hover:border-brand/40 hover:shadow-xl hover:shadow-black/30 hover:-translate-y-0.5">

                {{-- Image area --}}
                <a href="{{ route('dashboard.marketplace.show', $listing->id) }}" class="block relative overflow-hidden bg-slate-900 aspect-[4/3] flex items-center justify-center">
                    @if($displayImage)
                        <img src="{{ $displayImage }}" alt="{{ $listing->title }}"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    @else
                        <div class="flex items-center justify-center w-full h-full">
                            <svg class="w-12 h-12 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @endif

                    {{-- Stock badge overlay --}}
                    @if($listing->stock <= 0)
                    <div class="absolute inset-0 bg-black/60 flex items-center justify-center">
                        <span class="text-sm font-bold text-white bg-slate-900/80 px-3 py-1 rounded-full border border-slate-600">Out of Stock</span>
                    </div>
                    @elseif($listing->stock <= 5)
                    <span class="absolute top-2 right-2 text-[10px] font-bold bg-orange-500 text-white px-2 py-0.5 rounded-full">{{ $listing->stock }} left!</span>
                    @endif

                    {{-- Category badge --}}
                    @if($listing->category)
                    <span class="absolute top-2 left-2 text-[10px] font-semibold bg-slate-900/80 backdrop-blur-sm text-slate-300 border border-slate-700/60 px-2 py-0.5 rounded-full">{{ $listing->category }}</span>
                    @endif
                </a>

                {{-- Content --}}
                <div class="p-4 flex flex-col flex-1 gap-2">
                    <a href="{{ route('dashboard.marketplace.show', $listing->id) }}" class="font-semibold text-white text-sm leading-snug line-clamp-2 hover:text-brand transition-colors">{{ $listing->title }}</a>

                    {{-- Stock count --}}
                    <p class="text-xs {{ $listing->stock > 0 ? 'text-slate-500' : 'text-red-400' }}">
                        @if($listing->stock > 0)
                            {{ $listing->stock }} in stock
                        @else
                            Out of stock
                        @endif
                    </p>

                    <div class="mt-auto pt-3 border-t border-slate-700/40 flex items-center justify-between gap-2">
                        <span class="text-xl font-extrabold text-white">₦{{ number_format($listing->price, 0) }}</span>

                        <div class="flex items-center gap-1.5">
                            {{-- Wishlist --}}
                            @auth
                            <form method="POST" action="{{ route('dashboard.wishlist.store') }}">
                                @csrf
                                <input type="hidden" name="listing_id" value="{{ $listing->id }}">
                                <button type="submit" title="{{ in_array($listing->id, $wishlistIds) ? 'In wishlist' : 'Save' }}"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-600 hover:border-pink-400/60 transition-colors {{ in_array($listing->id, $wishlistIds) ? 'text-pink-400 border-pink-400/50 bg-pink-400/5' : 'text-slate-500 hover:text-pink-400' }}">
                                    <svg class="w-3.5 h-3.5" fill="{{ in_array($listing->id, $wishlistIds) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                </button>
                            </form>
                            @endauth

                            {{-- Buy --}}
                            @if($listing->stock > 0)
                            <form method="POST" action="{{ route('dashboard.marketplace.buy', $listing->id) }}"
                                onsubmit="return confirm('Buy {{ addslashes($listing->title) }} for ₦{{ number_format($listing->price, 2) }}?')">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 bg-brand hover:bg-brand-dark text-white text-xs font-bold rounded-lg transition-colors whitespace-nowrap">
                                    Buy Now
                                </button>
                            </form>
                            @else
                            <span class="px-3 py-1.5 bg-slate-700 text-slate-500 text-xs font-bold rounded-lg cursor-not-allowed">Sold Out</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- ── Pagination ──────────────────────────────────────────────────── --}}
        @if($listings->hasPages())
        <div class="mt-8 flex justify-center">
            {{ $listings->appends(request()->query())->links('pagination::tailwind') }}
        </div>
        @endif
    @endif
</div>

<style>
.listing-card { transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease; }
</style>
@endsection
