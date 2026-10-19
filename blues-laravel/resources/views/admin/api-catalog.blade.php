@extends('layouts.admin')
@section('title', 'API Catalog')
@section('page-title', 'API Catalog')
@section('content')
<div class="max-w-5xl space-y-5">
    @if(session('success'))
        <div class="rounded-lg border border-green-700 bg-green-900/30 px-4 py-3 text-sm text-green-300">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-lg border border-red-700 bg-red-900/30 px-4 py-3 text-sm text-red-300">{{ session('error') }}</div>
    @endif

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-slate-300">Choose which supplier products appear in your marketplace and can be purchased.</p>
            <p class="mt-1 text-xs text-slate-500">
                {{ count($selectedIds) }} selected out of {{ $totalProducts }} available.
                @if(\App\Models\Setting::get('api_catalog_product_ids') === null)
                    All products are currently selected. Save to keep your selection.
                @endif
            </p>
        </div>
        <a href="{{ route('admin.settings') }}" class="text-sm text-brand hover:underline">Supplier connection settings</a>
    </div>

    @if(!$providerConfigured)
        <div class="rounded-xl border border-amber-700/50 bg-amber-900/20 p-5 text-sm text-amber-200">
            Connect your supplier API key in Settings to load and choose products.
        </div>
    @else
        <input type="search" id="api-product-search" value="{{ $search }}" placeholder="Search product or category" class="w-full">

        <form method="POST" action="{{ route('admin.api-catalog.update') }}" class="space-y-4">
            @csrf
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-700 bg-slate-800 p-4">
                <div class="flex gap-2">
                    <button type="button" id="select-visible" class="text-sm text-brand hover:underline">Select visible</button>
                    <button type="button" id="clear-visible" class="text-sm text-slate-400 hover:text-white">Clear visible</button>
                </div>
                <button type="submit" class="btn-primary">Save products for sale</button>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-700 bg-slate-800">
                @forelse($products as $product)
                    @php
                        $id = (int) ($product['id'] ?? 0);
                        $image = $product['image'] ?? '';
                    @endphp
                    <label data-api-product-row data-search="{{ mb_strtolower(($product['name'] ?? '').' '.($product['category'] ?? '')) }}" class="flex cursor-pointer items-center gap-3 border-b border-slate-700/70 p-4 last:border-b-0 hover:bg-slate-700/30">
                        <input class="api-product-checkbox h-4 w-4 shrink-0 rounded" type="checkbox" name="product_ids[]" value="{{ $id }}"
                            {{ in_array($id, $selectedIds, true) ? 'checked' : '' }}>
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-slate-700 text-lg">
                            @if($image !== '')
                                <img src="{{ $image }}" alt="" class="h-full w-full object-cover" loading="lazy" onerror="this.remove()">
                            @else
                                {{ $product['category_icon'] ?? strtoupper(mb_substr($product['name'] ?? '?', 0, 1)) }}
                            @endif
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-semibold text-white">{{ $product['name'] ?? 'Unnamed product' }}</span>
                            <span class="mt-0.5 block text-xs text-slate-400">{{ $product['category'] ?? 'Other' }} · {{ (int) ($product['stock'] ?? 0) }} in stock</span>
                        </span>
                        <span class="shrink-0 text-sm font-bold text-brand">₦{{ number_format((float) ($product['price'] ?? 0) + (float) \App\Models\Setting::get('api_commission_amount', '0'), 2) }}</span>
                    </label>
                @empty
                    <p class="p-8 text-center text-sm text-slate-400">No supplier products were returned. Check your API key and try again.</p>
                @endforelse
            </div>
            <p id="api-product-search-empty" class="hidden rounded-lg bg-slate-800 p-6 text-center text-sm text-slate-400">No products match your search.</p>
            <div class="flex justify-end">
                <button type="submit" class="btn-primary">Save products for sale</button>
            </div>
        </form>
    @endif
</div>

<script>
document.getElementById('select-visible')?.addEventListener('click', () => {
    document.querySelectorAll('[data-api-product-row]:not([hidden]) .api-product-checkbox').forEach((checkbox) => { checkbox.checked = true; });
});
document.getElementById('clear-visible')?.addEventListener('click', () => {
    document.querySelectorAll('[data-api-product-row]:not([hidden]) .api-product-checkbox').forEach((checkbox) => { checkbox.checked = false; });
});
document.getElementById('api-product-search')?.addEventListener('input', event => {
    const term = event.target.value.trim().toLocaleLowerCase();
    let visible = 0;
    document.querySelectorAll('[data-api-product-row]').forEach(row => {
        row.hidden = !row.dataset.search.includes(term);
        if (!row.hidden) visible++;
    });
    document.getElementById('api-product-search-empty')?.classList.toggle('hidden', visible !== 0);
});
document.getElementById('api-product-search')?.dispatchEvent(new Event('input'));
</script>
@endsection
