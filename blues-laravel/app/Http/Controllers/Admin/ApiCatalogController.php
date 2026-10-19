<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SameehaSocialHubService;
use Illuminate\Http\Request;

class ApiCatalogController extends Controller
{
    public function index(Request $request, SameehaSocialHubService $provider)
    {
        $products = $provider->isConfigured() ? $provider->getProducts() : [];
        $hasSavedSelection = Setting::get('api_catalog_product_ids') !== null;
        $savedIds = Setting::apiCatalogProductIds();
        $selectedIds = $hasSavedSelection
            ? ($savedIds ?? [])
            : array_map('intval', array_column($products, 'id'));

        $search = trim((string) $request->query('search', ''));
        $visibleProducts = $search === ''
            ? $products
            : array_values(array_filter($products, fn ($product) =>
                str_contains(mb_strtolower((string) ($product['name'] ?? '')), mb_strtolower($search))
                || str_contains(mb_strtolower((string) ($product['category'] ?? '')), mb_strtolower($search))
            ));

        return view('admin.api-catalog', [
            'products' => $visibleProducts,
            'totalProducts' => count($products),
            'selectedIds' => $selectedIds,
            'providerConfigured' => $provider->isConfigured(),
            'search' => $search,
        ]);
    }

    public function update(Request $request, SameehaSocialHubService $provider)
    {
        $validated = $request->validate([
            'product_ids' => ['sometimes', 'array', 'max:10000'],
            'product_ids.*' => ['required', 'integer', 'min:1'],
        ]);

        if (!$provider->isConfigured()) {
            return back()->with('error', 'Connect the supplier API in Settings before choosing products.');
        }

        $availableIds = array_map('intval', array_column($provider->getProducts(), 'id'));
        $selectedIds = array_values(array_unique(array_map('intval', $validated['product_ids'] ?? [])));

        if (array_diff($selectedIds, $availableIds)) {
            return back()->with('error', 'The catalog changed while you were choosing products. Refresh the page and try again.');
        }

        Setting::set('api_catalog_product_ids', json_encode($selectedIds));

        return redirect()->route('admin.api-catalog')
            ->with('success', count($selectedIds).' API product'.(count($selectedIds) === 1 ? '' : 's').' selected for sale.');
    }
}
