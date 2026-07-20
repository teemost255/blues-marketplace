@php
    $colours = ['bg-blue-600','bg-indigo-600','bg-violet-600','bg-pink-600','bg-rose-600',
                'bg-orange-600','bg-amber-600','bg-teal-600','bg-cyan-600','bg-green-600'];
    $iconBg   = $colours[abs(crc32($product['name'] ?? '')) % count($colours)];
    $letter   = strtoupper(substr($product['name'] ?? '?', 0, 1));
    $stock    = (int) ($product['stock'] ?? 0);
    $price    = (float) ($product['price'] ?? 0);
    $pid      = (int) ($product['id'] ?? 0);
    $name     = $product['name'] ?? 'Unknown Product';
    $desc     = $product['description'] ?? null;

    $previewData = json_encode([
        'title'       => $name,
        'category'    => 'Catalog',
        'description' => $desc,
        'format'      => null,
        'stock'       => $stock,
        'price'       => number_format($price, 0),
        'image'       => null,
        'buyUrl'      => auth()->check() ? route('dashboard.marketplace.buy-api', $pid) : null,
        'iconBg'      => $iconBg,
        'letter'      => $letter,
        'isApi'       => true,
    ]);
@endphp

<div class="bg-slate-800 border border-slate-700/50 rounded-2xl p-4 hover:border-brand/30 transition-all">

    {{-- ── Title row ─────────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-3 mb-3">
        <div class="shrink-0 w-10 h-10 rounded-full {{ $iconBg }} flex items-center justify-center shadow-md">
            <span class="text-white font-extrabold text-base leading-none">{{ $letter }}</span>
        </div>

        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="font-bold text-white text-sm leading-snug line-clamp-1">{{ $name }}</span>
                <span class="shrink-0 flex items-center gap-1 text-[10px] font-bold bg-green-500/10 text-green-400 border border-green-500/20 rounded-full px-2 py-0.5 whitespace-nowrap">
                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Instant
                </span>
                <span class="shrink-0 text-[10px] font-semibold text-sky-400 border border-sky-600/40 bg-sky-500/10 rounded-full px-2 py-0.5 whitespace-nowrap">Catalog</span>
            </div>
        </div>
    </div>

    {{-- ── Description ────────────────────────────────────────────────────────── --}}
    @if($desc)
    <p class="text-xs text-slate-400 leading-relaxed mb-3 line-clamp-2">{{ $desc }}</p>
    @endif

    {{-- ── Stock + Price row ─────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-3">
        <p class="text-sm text-slate-300">
            Stock: <span class="font-bold {{ $stock <= 0 ? 'text-red-400' : ($stock <= 10 ? 'text-orange-400' : 'text-white') }}">{{ $stock }}</span>
        </p>
        <p class="text-base font-extrabold text-brand tracking-wide">
            NGN {{ number_format($price, 0) }}
        </p>
    </div>

    {{-- ── Split pill: Preview | Buy ─────────────────────────────────────────── --}}
    <div class="flex rounded-full overflow-hidden border border-brand/30 text-sm font-bold">

        <button type="button"
            onclick="openPreviewModal({{ $previewData }})"
            class="flex-1 flex items-center justify-center py-2.5 bg-brand hover:brightness-110 text-white transition-all select-none">
            Preview
        </button>

        <div class="w-px bg-white/10 shrink-0"></div>

        @if($stock > 0)
            @auth
            <form method="POST" action="{{ route('dashboard.marketplace.buy-api', $pid) }}" class="flex-1"
                onsubmit="return confirm('Buy \'{{ addslashes($name) }}\' for NGN {{ number_format($price, 2) }}?')">
                @csrf
                <button type="submit" class="w-full h-full flex items-center justify-center py-2.5 bg-slate-900 hover:bg-slate-950 text-white transition-all">Buy</button>
            </form>
            @else
            <a href="{{ route('login') }}" class="flex-1 flex items-center justify-center py-2.5 bg-slate-900 hover:bg-slate-950 text-white transition-all">Buy</a>
            @endauth
        @else
            <div class="flex-1 flex items-center justify-center py-2.5 bg-slate-900 text-red-400/60 cursor-not-allowed font-semibold">Out of Stock</div>
        @endif
    </div>
</div>
