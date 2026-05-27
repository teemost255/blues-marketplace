@extends('layouts.dashboard')
@section('title', 'Marketplace')
@section('page-title', 'Marketplace')

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- ── Search bar ──────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('dashboard.marketplace') }}" class="flex gap-3 mb-6">
        @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
        @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
        <div class="relative flex-1">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Search by name or description…"
                style="font-size:16px"
                class="w-full bg-white/5 border border-slate-600/60 text-white rounded-2xl pl-5 pr-10 py-3.5 text-sm placeholder-slate-400 focus:outline-none focus:border-brand">
            <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <button type="submit"
            class="flex items-center gap-2 px-6 py-3.5 bg-brand hover:bg-brand-dark text-white font-bold rounded-2xl text-sm transition-colors whitespace-nowrap shadow-lg shadow-brand/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Search
        </button>
    </form>

    {{-- ── Category pills ───────────────────────────────────────────────────── --}}
    @if($categories->count())
    <div class="flex items-center gap-2 flex-wrap mb-6">
        <a href="{{ route('dashboard.marketplace') }}{{ request('search') ? '?search='.urlencode(request('search')) : '' }}"
            class="px-4 py-1.5 rounded-full text-sm font-semibold border transition-all {{ !request('category') ? 'bg-brand text-white border-brand' : 'bg-white/5 text-slate-300 border-slate-600/60 hover:border-brand/50 hover:text-white' }}">
            All
        </a>
        @foreach($categories as $cat)
        <a href="{{ route('dashboard.marketplace') }}?category={{ $cat->slug }}{{ request('search') ? '&search='.urlencode(request('search')) : '' }}"
            class="px-4 py-1.5 rounded-full text-sm font-semibold border transition-all {{ request('category') === $cat->slug ? 'bg-brand text-white border-brand' : 'bg-white/5 text-slate-300 border-slate-600/60 hover:border-brand/50 hover:text-white' }}">
            {{ $cat->name }}
        </a>
        @endforeach
    </div>
    @endif

    {{-- ── Listings ──────────────────────────────────────────────────────────── --}}
    @if($listings->isEmpty())
        <div class="flex flex-col items-center justify-center py-24 text-center">
            <div class="w-14 h-14 rounded-2xl bg-slate-700 flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <p class="text-white font-semibold text-lg mb-1">No listings found</p>
            <p class="text-slate-400 text-sm mb-4">Try a different search or browse all categories.</p>
            @if(request()->hasAny(['search','category','sort','min_price','max_price']))
            <a href="{{ route('dashboard.marketplace') }}" class="px-5 py-2 bg-brand hover:bg-brand-dark text-white text-sm font-semibold rounded-xl transition-colors">Clear Filters</a>
            @endif
        </div>
    @else

    @php
        $categoryMap  = $categories->keyBy('slug');
        $grouped      = $listings->groupBy('category');
        $ungrouped    = $listings->whereNull('category')->merge($listings->where('category', ''));
        $usedGroups   = [];
    @endphp

    {{-- Group by category when browsing; flat list when searching --}}
    @if(request('search') || request('sort'))
        {{-- Flat list with count --}}
        <p class="text-xs text-slate-500 mb-4">{{ $listings->total() }} result{{ $listings->total() !== 1 ? 's' : '' }}</p>
        <div class="space-y-3">
            @foreach($listings as $listing)
                @include('marketplace._card', ['listing' => $listing, 'categoryMap' => $categoryMap])
            @endforeach
        </div>
    @else
        {{-- Grouped by category --}}
        @foreach($grouped as $catSlug => $items)
        @php
            $catInfo = $categoryMap[$catSlug] ?? null;
            $catLabel = $catInfo?->name ?? $catSlug ?? 'Other';
        @endphp
        <div class="mb-8">
            <div class="mb-4">
                <h2 class="text-lg font-extrabold text-white">{{ $catLabel }}</h2>
                <p class="text-sm text-slate-400">Available logs</p>
            </div>
            <div class="space-y-3">
                @foreach($items as $listing)
                    @include('marketplace._card', ['listing' => $listing, 'categoryMap' => $categoryMap])
                @endforeach
            </div>
        </div>
        @endforeach
    @endif

    {{-- Pagination --}}
    @if($listings->hasPages())
    <div class="mt-8 flex justify-center">
        {{ $listings->appends(request()->query())->links('pagination::tailwind') }}
    </div>
    @endif

    @endif
</div>
@endsection
