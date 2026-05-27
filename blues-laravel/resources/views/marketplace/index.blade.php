@extends('layouts.dashboard')
@section('title', 'Marketplace')

@section('content')
<div class="max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white mb-1">Marketplace</h1>
        <p class="text-slate-400">Browse verified digital accounts and phone numbers</p>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('dashboard.marketplace') }}" class="flex flex-wrap gap-3 mb-8">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search listings..."
            class="bg-slate-800 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand w-full sm:w-64">
        <select name="category" class="bg-slate-800 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-white focus:outline-none focus:border-brand">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <select name="sort" class="bg-slate-800 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-white focus:outline-none focus:border-brand">
            <option value="">Latest</option>
            <option value="price_asc"  {{ request('sort') === 'price_asc'  ? 'selected' : '' }}>Price: Low to High</option>
            <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
        </select>
        <div class="flex items-center gap-1.5">
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm">₦</span>
                <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min"
                    min="0" step="0.01"
                    class="bg-slate-800 border border-slate-700 rounded-lg pl-7 pr-3 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand w-28">
            </div>
            <span class="text-slate-500 text-xs">—</span>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm">₦</span>
                <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max"
                    min="0" step="0.01"
                    class="bg-slate-800 border border-slate-700 rounded-lg pl-7 pr-3 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand w-28">
            </div>
        </div>
        <button type="submit" class="bg-brand hover:bg-brand-dark text-white font-medium px-5 py-2.5 rounded-lg text-sm transition-colors">Search</button>
        @if(request()->hasAny(['search','category','sort','min_price','max_price']))
            <a href="{{ route('dashboard.marketplace') }}" class="border border-slate-600 hover:border-slate-500 text-slate-400 hover:text-white font-medium px-5 py-2.5 rounded-lg text-sm transition-colors">Clear</a>
        @endif
    </form>

    @php
        $categoryMap = $categories->keyBy('slug');
    @endphp

    {{-- Grid --}}
    @if($listings->isEmpty())
        <div class="text-center py-20 text-slate-500">
            <svg class="w-12 h-12 mx-auto mb-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <p class="text-lg font-medium">No listings found</p>
            <p class="text-sm mt-1">Try adjusting your search or filters</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach($listings as $listing)
            @php
                $displayImage = ($categoryMap[$listing->category]->image ?? null)
                    ?? $listing->image;
            @endphp
            <div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden hover:border-brand/50 transition-all group flex flex-col">
                {{-- Logo-style image area --}}
                <div class="flex items-center justify-center pt-6 pb-2 px-4">
                    <div class="w-20 h-20 rounded-2xl overflow-hidden bg-slate-700 border border-slate-600 flex items-center justify-center shrink-0 shadow-lg">
                        @if($displayImage)
                            <img src="{{ $displayImage }}" alt="{{ $listing->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <svg class="w-9 h-9 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        @endif
                    </div>
                </div>

                <div class="p-4 flex flex-col flex-1 text-center">
                    @if($listing->category)
                        <span class="text-xs text-brand font-medium">{{ $listing->category }}</span>
                    @endif
                    <h3 class="font-semibold text-white text-sm mt-1 mb-1 line-clamp-2">{{ $listing->title }}</h3>
                    <p class="text-xs text-slate-400 mb-3">{{ $listing->stock }} in stock</p>
                    <span class="text-2xl font-bold text-white block mb-4">₦{{ number_format($listing->price, 2) }}</span>

                    <div class="mt-auto flex items-center gap-2">
                        @auth
                            <form method="POST" action="{{ route('dashboard.wishlist.store') }}" class="shrink-0">
                                @csrf
                                <input type="hidden" name="listing_id" value="{{ $listing->id }}">
                                <button type="submit" title="{{ in_array($listing->id, $wishlistIds) ? 'In wishlist' : 'Add to wishlist' }}"
                                    class="w-9 h-9 flex items-center justify-center rounded-lg border border-slate-600 hover:border-pink-400 transition-colors {{ in_array($listing->id, $wishlistIds) ? 'text-pink-400 border-pink-400' : 'text-slate-400' }}">
                                    <svg class="w-4 h-4" fill="{{ in_array($listing->id, $wishlistIds) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                </button>
                            </form>
                        @endauth
                        {{-- Direct buy: POST straight to checkout --}}
                        <form method="POST" action="{{ route('dashboard.marketplace.buy', $listing->id) }}" class="flex-1"
                              onsubmit="return confirm('Buy {{ addslashes($listing->title) }} for ₦{{ number_format($listing->price, 2) }}?')">
                            @csrf
                            <button type="submit" class="w-full bg-brand hover:bg-brand-dark text-white text-sm font-semibold py-2 rounded-lg transition-colors">
                                Buy Now — ₦{{ number_format($listing->price, 0) }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($listings->hasPages())
            <div class="mt-10 flex justify-center">
                {{ $listings->links('pagination::tailwind') }}
            </div>
        @endif
    @endif
</div>
@endsection
