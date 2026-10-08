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
    $catLabel = $product['category'] ?? $product['type'] ?? null;

    $previewData = json_encode([
        'title'       => $name,
        'category'    => $catLabel,
        'description' => $desc,
        'format'      => null,
        'stock'       => $stock,
        'price'       => number_format($price, 0),
        'image'       => null,
        'buyUrl'      => auth()->check() ? route('dashboard.marketplace.buy-api', $pid) : null,
        'iconBg'      => $iconBg,
        'letter'      => $letter,
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
                @if($catLabel)
                <span class="shrink-0 text-[10px] font-semibold text-slate-400 border border-slate-600/50 rounded-full px-2 py-0.5 whitespace-nowrap">{{ $catLabel }}</span>
                @endif
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

    <div class="flex gap-2">
        <button type="button"
            onclick="openPreviewModal({{ $previewData }})"
            class="flex-1 rounded-xl border border-brand/40 bg-brand/10 hover:bg-brand/20 text-brand text-sm font-bold py-2.5 transition-all select-none">
            Preview
        </button>

        @if($stock > 0)
            @auth
            <form method="POST" action="{{ route('dashboard.marketplace.buy-api', $pid) }}"
                class="flex-[1.6] flex items-center gap-2"
                data-api-purchase-form data-product-name="{{ $name }}" data-unit-price="{{ $price }}">
                @csrf
                <label class="sr-only" for="api-quantity-{{ $pid }}">Quantity for {{ $name }}</label>
                <span class="text-[10px] font-semibold text-slate-400">Qty</span>
                <input id="api-quantity-{{ $pid }}" type="number" name="quantity" value="1" min="1" max="{{ $stock }}" step="1" required
                    class="w-16 shrink-0 rounded-xl border border-slate-600 bg-slate-900 px-2 py-2.5 text-center text-sm font-semibold text-white focus:border-brand"
                    aria-label="Quantity, maximum {{ $stock }}">
                <button type="submit" data-api-buy-button
                    class="flex-1 rounded-xl bg-brand hover:bg-brand-dark text-white text-sm font-bold py-2.5 transition-all whitespace-nowrap">
                    Buy · NGN <span data-api-total>{{ number_format($price, 2) }}</span>
                </button>
            </form>
            @else
            <a href="{{ route('login') }}" class="flex-[1.6] flex items-center justify-center rounded-xl bg-slate-900 hover:bg-slate-950 text-white text-sm font-bold py-2.5 transition-all">Log in to buy</a>
            @endauth
        @else
            <div class="flex-[1.6] flex items-center justify-center rounded-xl bg-slate-900 text-red-400/60 cursor-not-allowed text-sm font-semibold">Out of Stock</div>
        @endif
    </div>
</div>

@once
<script>
function updateApiPurchaseTotal(form) {
    const quantityInput = form.querySelector('input[name="quantity"]');
    const totalOutput = form.querySelector('[data-api-total]');
    const buyButton = form.querySelector('[data-api-buy-button]');
    const quantity = Number(quantityInput.value);
    const isValid = Number.isInteger(quantity)
        && quantity >= Number(quantityInput.min)
        && quantity <= Number(quantityInput.max);

    buyButton.disabled = !isValid;
    buyButton.classList.toggle('opacity-50', !isValid);
    totalOutput.textContent = isValid
        ? new Intl.NumberFormat('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(form.dataset.unitPrice) * quantity)
        : '—';
}

document.addEventListener('input', function(event) {
    const form = event.target.closest('[data-api-purchase-form]');
    if (form && event.target.matches('input[name="quantity"]')) updateApiPurchaseTotal(form);
});

document.addEventListener('change', function(event) {
    const form = event.target.closest('[data-api-purchase-form]');
    if (form && event.target.matches('input[name="quantity"]')) updateApiPurchaseTotal(form);
});

document.addEventListener('submit', function(event) {
    const form = event.target.closest('[data-api-purchase-form]');
    if (!form) return;

    const quantityInput = form.querySelector('input[name="quantity"]');
    const quantity = Number(quantityInput.value);
    const total = Number(form.dataset.unitPrice) * quantity;
    if (!Number.isInteger(quantity) || quantity < 1 || quantity > Number(quantityInput.max)) {
        event.preventDefault();
        quantityInput.focus();
        return;
    }
    if (!window.confirm('Buy ' + quantity + ' × ' + form.dataset.productName + ' for NGN '
        + new Intl.NumberFormat('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(total) + '?')) {
        event.preventDefault();
    }
});
</script>
@endonce
