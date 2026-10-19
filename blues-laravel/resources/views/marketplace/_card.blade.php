@php
    $catInfo   = $categoryMap[$listing->category] ?? null;
    $catLabel  = $catInfo?->name ?? $listing->category ?? '';
    $catIcon   = $catInfo?->icon ?? null;

    $letter    = strtoupper(substr($listing->title ?? '?', 0, 1));
    $colours   = ['bg-blue-600','bg-indigo-600','bg-violet-600','bg-pink-600','bg-rose-600',
                  'bg-orange-600','bg-amber-600','bg-teal-600','bg-cyan-600','bg-green-600'];
    $iconBg    = $colours[abs(crc32($listing->title ?? '')) % count($colours)];
    $formatText = $listing->login_details ?? null;
    $displayImg = $listing->image ?? $catInfo?->image ?? null;

    // For preview modal data
    $previewData = json_encode([
        'id'          => $listing->id,
        'title'       => $listing->title,
        'category'    => $catLabel,
        'description' => $listing->description,
        'format'      => $formatText,
        'stock'       => $listing->stock,
        'price'       => number_format($listing->price, 0),
        'image'       => $displayImg,
        'buyUrl'      => route('dashboard.marketplace.buy', $listing->id),
        'iconBg'      => $iconBg,
        'letter'      => $letter,
    ]);
@endphp

<div class="bg-slate-800 border border-slate-700/50 rounded-2xl p-4 hover:border-brand/30 transition-all">

    {{-- ── Title row: icon + name + badges ─────────────────────────────────── --}}
    <div class="flex items-center gap-3 mb-3">
        <div class="shrink-0 w-10 h-10 rounded-full {{ $iconBg }} flex items-center justify-center shadow-md overflow-hidden">
            @if($displayImg)
                <img src="{{ $displayImg }}" alt="" class="w-full h-full object-cover">
            @elseif($catIcon)
                <span class="text-lg leading-none">{{ $catIcon }}</span>
            @else
                <span class="text-white font-extrabold text-base leading-none">{{ $letter }}</span>
            @endif
        </div>

        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="font-bold text-white text-sm leading-snug line-clamp-1">{{ $listing->title }}</span>
                <span class="shrink-0 flex items-center gap-1 text-[10px] font-bold bg-green-500/10 text-green-400 border border-green-500/20 rounded-full px-2 py-0.5 whitespace-nowrap">
                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Instant
                </span>
                @if($catLabel)
                <span class="shrink-0 text-[10px] font-semibold text-slate-400 border border-slate-600/50 rounded-full px-2 py-0.5 whitespace-nowrap">{{ $catLabel }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Format / description ──────────────────────────────────────────────── --}}
    @if($formatText || $listing->description)
    <p class="text-xs text-slate-400 leading-relaxed mb-3 line-clamp-2">{{ $formatText ?: $listing->description }}</p>
    @endif

    {{-- ── Stock + Price row ─────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-3">
        <p class="text-sm text-slate-300">
            Stock: <span class="font-bold {{ $listing->stock <= 0 ? 'text-red-400' : ($listing->stock <= 10 ? 'text-orange-400' : 'text-white') }}">{{ $listing->stock }}</span>
        </p>
        <p class="text-base font-extrabold text-brand tracking-wide">
            NGN {{ number_format($listing->price, 0) }}
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

        @if($listing->stock > 0)
            @auth
            <form method="POST" action="{{ route('dashboard.marketplace.buy', $listing->id) }}" class="flex-[1.6] flex items-center gap-2"
                data-local-purchase-form data-product-name="{{ $listing->title }}" data-unit-price="{{ $listing->price }}">
                @csrf
                <label class="sr-only" for="local-quantity-{{ $listing->id }}">Quantity for {{ $listing->title }}</label>
                <span class="text-[10px] font-semibold text-slate-400">Qty</span>
                <input id="local-quantity-{{ $listing->id }}" type="number" name="quantity" value="1" min="1" max="{{ $listing->stock }}" step="1" required
                    class="w-16 shrink-0 rounded-xl border border-slate-600 bg-slate-900 px-2 py-2 text-center text-sm font-semibold text-white focus:border-brand"
                    aria-label="Quantity, maximum {{ $listing->stock }}">
                <button type="submit" data-local-buy-button
                    class="flex-1 rounded-xl bg-slate-900 hover:bg-slate-950 text-white text-sm font-bold py-2.5 transition-all whitespace-nowrap">
                    Buy · NGN <span data-local-total>{{ number_format($listing->price, 2) }}</span>
                </button>
            </form>
            @else
            <a href="{{ route('login') }}" class="flex-1 flex items-center justify-center py-2.5 bg-slate-900 hover:bg-slate-950 text-white transition-all">Buy</a>
            @endauth
        @else
            <div class="flex-1 flex items-center justify-center py-2.5 bg-slate-900 text-red-400/60 cursor-not-allowed font-semibold">Out of Stock</div>
        @endif
    </div>
</div>

@once
<script>
function updateLocalPurchaseTotal(form) {
    const input = form.querySelector('input[name="quantity"]');
    const output = form.querySelector('[data-local-total]');
    const button = form.querySelector('[data-local-buy-button]');
    const quantity = Number(input.value);
    const valid = Number.isInteger(quantity) && quantity >= Number(input.min) && quantity <= Number(input.max);
    button.disabled = !valid;
    button.classList.toggle('opacity-50', !valid);
    output.textContent = valid
        ? new Intl.NumberFormat('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(form.dataset.unitPrice) * quantity)
        : '—';
}

document.addEventListener('input', event => {
    const form = event.target.closest('[data-local-purchase-form]');
    if (form && event.target.matches('input[name="quantity"]')) updateLocalPurchaseTotal(form);
});
document.addEventListener('change', event => {
    const form = event.target.closest('[data-local-purchase-form]');
    if (form && event.target.matches('input[name="quantity"]')) updateLocalPurchaseTotal(form);
});
document.addEventListener('submit', event => {
    const form = event.target.closest('[data-local-purchase-form]');
    if (!form) return;
    const input = form.querySelector('input[name="quantity"]');
    const quantity = Number(input.value);
    if (!Number.isInteger(quantity) || quantity < 1 || quantity > Number(input.max)) {
        event.preventDefault();
        input.focus();
        return;
    }
    const total = Number(form.dataset.unitPrice) * quantity;
    if (!window.confirm('Buy ' + quantity + ' × ' + form.dataset.productName + ' for NGN '
        + new Intl.NumberFormat('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(total) + '?')) {
        event.preventDefault();
    }
});
</script>
@endonce
