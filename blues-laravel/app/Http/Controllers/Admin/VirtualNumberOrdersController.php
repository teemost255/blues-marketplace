<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VirtualNumberOrder;
use App\Services\HeroSmsService;
use Illuminate\Http\Request;

class VirtualNumberOrdersController extends Controller
{
    public function index(Request $request)
    {
        $query = VirtualNumberOrder::with('user');

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function($q) use ($term) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term))
                ->orWhere('phone_number', 'like', $term)
                ->orWhere('service', 'like', $term);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('service')) {
            $query->where('service', 'like', '%' . $request->service . '%');
        }

        $orders = $query->latest()->paginate(25)->withQueryString();

        $stats = [
            'total'     => VirtualNumberOrder::count(),
            'active'    => VirtualNumberOrder::where('status', 'active')->count(),
            'completed' => VirtualNumberOrder::where('status', 'completed')->count(),
            'cancelled' => VirtualNumberOrder::where('status', 'cancelled')->count(),
            'revenue'   => VirtualNumberOrder::where('status', '!=', 'cancelled')->sum('cost'),
        ];

        return view('admin.virtual-numbers', compact('orders', 'stats'));
    }

    public function updateStatus(Request $request, VirtualNumberOrder $order)
    {
        $request->validate(['status' => 'required|in:pending,active,completed,cancelled,failed']);
        $order->update(['status' => $request->status]);
        return back()->with('success', 'Order status updated.');
    }

    public function destroy(VirtualNumberOrder $order)
    {
        $order->delete();
        return back()->with('success', 'Order deleted.');
    }

    public function heroSmsBalance()
    {
        $svc = new HeroSmsService();
        if (!$svc->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'HeroSMS API not configured. Add your key in Settings.']);
        }
        $result = $svc->getBalance();
        if ($result['success']) {
            $balance = $result['data']['balance'] ?? null;
            return response()->json(['success' => true, 'balance' => $balance]);
        }
        return response()->json(['success' => false, 'message' => $result['message'] ?? 'Could not fetch balance.']);
    }

    /**
     * Diagnostic: test what Hero-SMS returns for a given country+service combination.
     * Shows raw API response so admin can debug "No numbers available" issues.
     */
    public function heroSmsDiagnose(\Illuminate\Http\Request $request)
    {
        $svc = new HeroSmsService();
        if (!$svc->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'HeroSMS API not configured.']);
        }

        $country = trim($request->input('country', ''));
        $service = trim($request->input('service', ''));

        // Step 1: fetch services list for this country
        $servicesResult = $svc->getServices($country ?: null);

        // Step 2: if service given, check if it appears in the list
        $serviceInList = null;
        if ($service && $servicesResult['success']) {
            foreach ($servicesResult['data'] as $s) {
                if ($s['serviceId'] === $service) {
                    $serviceInList = $s;
                    break;
                }
            }
        }

        return response()->json([
            'success'          => true,
            'country_queried'  => $country ?: '(all)',
            'service_queried'  => $service ?: '(all)',
            'services_success' => $servicesResult['success'],
            'services_message' => $servicesResult['message'] ?? null,
            'services_count'   => $servicesResult['success'] ? count($servicesResult['data']) : 0,
            'services_list'    => $servicesResult['success'] ? $servicesResult['data'] : [],
            'target_service'   => $serviceInList,
        ]);
    }

    /**
     * Render the admin Services & Pricing Catalog page.
     */
    public function servicesCatalog()
    {
        $usdToNgn        = (float) \App\Models\Setting::get('usd_to_ngn_rate', '1600');
        $commissionType  = \App\Models\Setting::get('vn_commission_type', 'flat');
        $commissionValue = (float) \App\Models\Setting::get('vn_commission_value', '0');
        $configured      = (new HeroSmsService())->isConfigured();
        return view('admin.virtual-numbers-services', compact('usdToNgn', 'commissionType', 'commissionValue', 'configured'));
    }

    /**
     * JSON endpoint: fetch services & prices from HeroSMS for the admin catalog.
     * Supports optional ?country= and ?bust=1 (bypass cache).
     */
    public function servicesCatalogData(Request $request)
    {
        $svc = new HeroSmsService();
        if (!$svc->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'HeroSMS API key not configured. Add it in Admin → Settings.']);
        }

        $country   = trim((string) $request->get('country', ''));
        $bust      = (bool) $request->get('bust', false);
        $usdToNgn  = (float) \App\Models\Setting::get('usd_to_ngn_rate', '1600');
        $cacheKey  = 'admin.vn.services.' . md5($country . '_' . $usdToNgn);

        if ($bust) {
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
        }

        if (!$bust && \Illuminate\Support\Facades\Cache::has($cacheKey)) {
            $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
            if ($cached !== null) {
                return response()->json(['success' => true, 'data' => $cached, 'cached' => true]);
            }
        }

        $result = $svc->getServices($country ?: null);
        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['message'] ?? 'Could not fetch services from HeroSMS.']);
        }

        $data = array_map(function ($s) use ($usdToNgn) {
            $s['cost_ngn'] = round(($s['cost'] ?? 0) * $usdToNgn, 2);
            return $s;
        }, $result['data']);

        \Illuminate\Support\Facades\Cache::put($cacheKey, $data, 300);
        return response()->json(['success' => true, 'data' => $data, 'cached' => false]);
    }

    /**
     * JSON endpoint: fetch countries list for the admin catalog.
     */
    public function servicesCatalogCountries(Request $request)
    {
        $svc = new HeroSmsService();
        if (!$svc->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'HeroSMS API key not configured.']);
        }

        $bust     = (bool) $request->get('bust', false);
        $cacheKey = 'admin.vn.countries';

        if ($bust) {
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
        }

        if (!$bust && \Illuminate\Support\Facades\Cache::has($cacheKey)) {
            $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
            if ($cached !== null) {
                return response()->json(['success' => true, 'data' => $cached]);
            }
        }

        $result = $svc->getCountries();
        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['message'] ?? 'Could not fetch countries.']);
        }

        \Illuminate\Support\Facades\Cache::put($cacheKey, $result['data'], 600);
        return response()->json(['success' => true, 'data' => $result['data']]);
    }

    public function exportCsv(Request $request)
    {
        $query = VirtualNumberOrder::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="vn-orders-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'User', 'Email', 'Service', 'Country', 'Phone Number', 'Cost (NGN)', 'Status', 'SMS Code', 'Date']);
            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->id,
                    $order->user?->name ?? '—',
                    $order->user?->email ?? '—',
                    $order->service,
                    $order->country,
                    $order->phone_number,
                    number_format($order->cost, 2),
                    $order->status,
                    $order->sms_code ?? '',
                    $order->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
