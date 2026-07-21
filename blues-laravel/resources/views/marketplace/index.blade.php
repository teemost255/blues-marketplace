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
        $categoryMap = $categories->keyBy('slug');
        $grouped     = $listings->groupBy('category');
    @endphp

    @if(request('search') || request('sort'))
        <p class="text-xs text-slate-500 mb-4">{{ $listings->total() }} result{{ $listings->total() !== 1 ? 's' : '' }}</p>
        <div class="space-y-3">
            @foreach($listings as $listing)
                @include('marketplace._card', ['listing' => $listing, 'categoryMap' => $categoryMap])
            @endforeach
        </div>
    @else
        @foreach($grouped as $catSlug => $items)
        @php $catInfo = $categoryMap[$catSlug] ?? null; $catLabel = $catInfo?->name ?? $catSlug ?? 'Other'; @endphp
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

    @if($listings->hasPages())
    <div class="mt-8 flex justify-center">
        {{ $listings->appends(request()->query())->links('pagination::tailwind') }}
    </div>
    @endif

    @endif

    {{-- ── More Products ─────────────────────────────────────────────────────── --}}
    @if(!empty($apiProducts) && !request('category'))
    <div class="mb-8 {{ $listings->isEmpty() ? 'mt-0' : 'mt-8' }}">
        <div class="mb-4">
            <h2 class="text-lg font-extrabold text-white">More Products</h2>
            <p class="text-sm text-slate-400">Instant delivery — available right now</p>
        </div>
        <div class="space-y-3">
            @foreach($apiProducts as $product)
                @include('marketplace._api_card', ['product' => $product])
            @endforeach
        </div>
    </div>
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════════════
     PREVIEW MODAL
════════════════════════════════════════════════════════════════ --}}
<div id="preview-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/75 backdrop-blur-sm" onclick="closePreviewModal()"></div>
    <div class="relative w-full max-w-md bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl z-10 overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-700/60">
            <p class="font-bold text-white text-base" id="pm-title">Product Preview</p>
            <button onclick="closePreviewModal()" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700/50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Image --}}
        <div id="pm-image-wrap" class="hidden">
            <img id="pm-image" src="" alt="" class="w-full h-48 object-cover">
        </div>

        {{-- Body --}}
        <div class="p-5 space-y-4">

            {{-- Category + instant badge --}}
            <div class="flex items-center gap-2 flex-wrap">
                <span id="pm-category" class="text-xs font-semibold text-brand bg-brand/10 border border-brand/20 rounded-full px-2.5 py-0.5 hidden"></span>
                <span class="flex items-center gap-1 text-[10px] font-bold bg-green-500/10 text-green-400 border border-green-500/20 rounded-full px-2 py-0.5">
                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Instant Delivery
                </span>
            </div>

            {{-- Description --}}
            <p id="pm-description" class="text-sm text-slate-300 leading-relaxed hidden"></p>

            {{-- Format line --}}
            <div id="pm-format-wrap" class="hidden bg-slate-800 rounded-xl px-4 py-3 border border-slate-700/40">
                <p class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold mb-1">Credential Format</p>
                <p id="pm-format" class="text-sm text-slate-300 font-mono leading-relaxed"></p>
            </div>

            {{-- Stock / Price row --}}
            <div class="flex items-center justify-between bg-slate-800 rounded-xl px-4 py-3 border border-slate-700/40">
                <div>
                    <p class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold mb-0.5">Stock</p>
                    <p id="pm-stock" class="font-bold text-white text-lg"></p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold mb-0.5">Price</p>
                    <p id="pm-price" class="font-extrabold text-brand text-xl"></p>
                </div>
            </div>

            {{-- What you receive note --}}
            <p class="text-xs text-slate-500 flex items-start gap-1.5">
                <svg class="w-3.5 h-3.5 text-yellow-400 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                You receive one credential set delivered instantly to your Orders page. Each set is unique and never reused.
            </p>
        </div>

        {{-- Actions --}}
        <div class="px-5 pb-5">
            <div id="pm-buy-container">
                {{-- Buy button injected by JS --}}
            </div>
        </div>
    </div>
</div>

<script>
let currentPreviewData = null;

function openPreviewModal(data) {
    currentPreviewData = data;
    const modal = document.getElementById('preview-modal');

    document.getElementById('pm-title').textContent          = data.title || 'Product Preview';
    document.getElementById('pm-stock').textContent          = data.stock;
    document.getElementById('pm-price').textContent          = 'NGN ' + data.price;

    // Image
    const imgWrap = document.getElementById('pm-image-wrap');
    const img     = document.getElementById('pm-image');
    if (data.image) { img.src = data.image; imgWrap.classList.remove('hidden'); }
    else { imgWrap.classList.add('hidden'); }

    // Category
    const catEl = document.getElementById('pm-category');
    if (data.category) { catEl.textContent = data.category; catEl.classList.remove('hidden'); }
    else { catEl.classList.add('hidden'); }

    // Description
    const descEl = document.getElementById('pm-description');
    if (data.description) { descEl.textContent = data.description; descEl.classList.remove('hidden'); }
    else { descEl.classList.add('hidden'); }

    // Format
    const fmtWrap = document.getElementById('pm-format-wrap');
    const fmtEl   = document.getElementById('pm-format');
    if (data.format) { fmtEl.textContent = data.format; fmtWrap.classList.remove('hidden'); }
    else { fmtWrap.classList.add('hidden'); }

    // Stock colour
    const stockEl = document.getElementById('pm-stock');
    stockEl.textContent = data.stock;
    stockEl.className = 'font-bold text-lg ' + (
        data.stock <= 0  ? 'text-red-400'    :
        data.stock <= 10 ? 'text-orange-400' : 'text-white'
    );

    // Buy button
    const buyContainer = document.getElementById('pm-buy-container');
    if (data.stock > 0 && data.buyUrl) {
        buyContainer.innerHTML = `
            <form method="POST" action="${data.buyUrl}" class="w-full"
                onsubmit="return confirm('Buy this listing for NGN ${data.price}?')">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <button type="submit"
                    class="w-full py-3 flex items-center justify-center gap-2 font-bold text-white rounded-xl text-sm transition-all"
                    style="background:linear-gradient(135deg,#0ea5e9,#6366f1)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Buy Now
                </button>
            </form>`;
    } else if (data.stock <= 0) {
        buyContainer.innerHTML = `<button disabled class="w-full py-3 bg-slate-700 text-slate-500 font-bold rounded-xl text-sm cursor-not-allowed">Out of Stock</button>`;
    } else {
        buyContainer.innerHTML = `<a href="/login" class="w-full py-3 flex items-center justify-center font-bold text-white rounded-xl text-sm" style="background:linear-gradient(135deg,#0ea5e9,#6366f1)">Sign In to Buy</a>`;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closePreviewModal() {
    document.getElementById('preview-modal').classList.add('hidden');
    document.getElementById('preview-modal').classList.remove('flex');
    document.body.style.overflow = '';
    currentPreviewData = null;
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closePreviewModal(); });
</script>
@endsection
