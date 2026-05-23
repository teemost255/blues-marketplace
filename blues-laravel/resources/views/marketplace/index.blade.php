@extends('layouts.app')
@section('title', 'Marketplace')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white mb-1">Marketplace</h1>
        <p class="text-slate-400">Browse verified digital accounts and phone numbers</p>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('marketplace') }}" class="flex flex-wrap gap-3 mb-8">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search listings..."
            class="bg-slate-800 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand w-full sm:w-64">
        <select name="category" class="bg-slate-800 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-white focus:outline-none focus:border-brand">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->name }}" {{ request('category') === $cat->name ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <select name="sort" class="bg-slate-800 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-white focus:outline-none focus:border-brand">
            <option value="">Latest</option>
            <option value="price_asc"  {{ request('sort') === 'price_asc'  ? 'selected' : '' }}>Price: Low to High</option>
            <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
        </select>
        <button type="submit" class="bg-brand hover:bg-brand-dark text-white font-medium px-5 py-2.5 rounded-lg text-sm transition-colors">Search</button>
        @if(request()->hasAny(['search','category','sort']))
            <a href="{{ route('marketplace') }}" class="border border-slate-600 hover:border-slate-500 text-slate-400 hover:text-white font-medium px-5 py-2.5 rounded-lg text-sm transition-colors">Clear</a>
        @endif
    </form>

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
            <div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden hover:border-brand/50 transition-all group flex flex-col">
                <a href="{{ route('marketplace.show', $listing->id) }}" class="block">
                    <div class="h-36 bg-gradient-to-br from-slate-700 to-slate-600 flex items-center justify-center overflow-hidden">
                        @if($listing->image_url)
                            <img src="{{ $listing->image_url }}" alt="{{ $listing->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                        @else
                            <svg class="w-10 h-10 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        @endif
                    </div>
                </a>
                <div class="p-4 flex flex-col flex-1">
                    @if($listing->category)
                        <span class="text-xs text-brand font-medium">{{ $listing->category }}</span>
                    @endif
                    <a href="{{ route('marketplace.show', $listing->id) }}">
                        <h3 class="font-semibold text-white text-sm mt-1 mb-2 line-clamp-2 group-hover:text-brand transition-colors">{{ $listing->title }}</h3>
                    </a>
                    <div class="mt-auto flex items-center justify-between pt-3 border-t border-slate-700">
                        <div>
                            <span class="text-xl font-bold text-white">${{ number_format($listing->price, 2) }}</span>
                            <p class="text-xs text-slate-400">{{ $listing->stock }} in stock</p>
                        </div>
                        <div class="flex gap-2">
                            @auth
                                <form method="POST" action="{{ route('dashboard.wishlist.store') }}">
                                    @csrf
                                    <input type="hidden" name="listing_id" value="{{ $listing->id }}">
                                    <button type="submit" title="{{ in_array($listing->id, $wishlistIds) ? 'In wishlist' : 'Add to wishlist' }}"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-600 hover:border-pink-400 transition-colors {{ in_array($listing->id, $wishlistIds) ? 'text-pink-400 border-pink-400' : 'text-slate-400' }}">
                                        <svg class="w-4 h-4" fill="{{ in_array($listing->id, $wishlistIds) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                    </button>
                                </form>
                            @endauth
                            <a href="{{ route('marketplace.show', $listing->id) }}" class="bg-brand hover:bg-brand-dark text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">Buy</a>
                        </div>
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
