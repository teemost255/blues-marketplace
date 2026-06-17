<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\{HeroSmsService, GrizzlySmsService};
use Illuminate\Http\Request;

class VirtualNumberSettingsController extends Controller
{
    public function index()
    {
        // Auto-generate webhook secrets if none exist yet
        if (!Setting::get('herosms_webhook_secret', '')) {
            Setting::set('herosms_webhook_secret', bin2hex(random_bytes(20)));
        }
        if (!Setting::get('grizzly_webhook_secret', '')) {
            Setting::set('grizzly_webhook_secret', bin2hex(random_bytes(20)));
        }

        $settings = [
            'herosms_enabled'                      => Setting::get('herosms_enabled', '0'),
            'herosms_api_key'                      => Setting::get('herosms_api_key', ''),
            'herosms_exchange_rate'                => Setting::get('herosms_exchange_rate', '1600'),
            'herosms_commission_type'              => Setting::get('herosms_commission_type', 'flat'),
            'herosms_number_price'                 => Setting::get('herosms_number_price', '200'),
            'herosms_cancel_refund_pct'            => Setting::get('herosms_cancel_refund_pct', '50'),
            'herosms_expiry_minutes'               => Setting::get('herosms_expiry_minutes', '20'),
            'herosms_max_active'                   => Setting::get('herosms_max_active', '3'),
            'herosms_webhook_secret'               => Setting::get('herosms_webhook_secret', ''),
            'herosms_supplemental_wa_country_id'   => Setting::get('herosms_supplemental_wa_country_id', ''),
            'herosms_supplemental_wa_country_name' => Setting::get('herosms_supplemental_wa_country_name', ''),

            'grizzly_enabled'        => Setting::get('grizzly_enabled', '0'),
            'grizzly_api_key'        => Setting::get('grizzly_api_key', ''),
            'grizzly_webhook_secret' => Setting::get('grizzly_webhook_secret', ''),
        ];

        // Server balances (internal, admin-only)
        $server1Balance = null;
        $server2Balance = null;

        $s1 = new GrizzlySmsService();
        if ($s1->isConfigured()) {
            try { $server1Balance = $s1->getBalance(); } catch (\Exception $e) {}
        }

        $s2 = new HeroSmsService();
        if ($s2->isConfigured()) {
            try { $server2Balance = $s2->getBalance(); } catch (\Exception $e) {}
        }

        return view('admin.virtual-number-settings', compact('settings', 'server1Balance', 'server2Balance'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'herosms_api_key'           => 'nullable|string|max:255',
            'herosms_exchange_rate'     => 'nullable|numeric|min:1',
            'herosms_commission_type'   => 'nullable|in:flat,percentage',
            'herosms_number_price'      => 'nullable|numeric|min:0',
            'herosms_cancel_refund_pct' => 'nullable|integer|min:0|max:100',
            'herosms_expiry_minutes'    => 'nullable|integer|min:5|max:60',
            'herosms_max_active'        => 'nullable|integer|min:1|max:10',
            'grizzly_api_key'           => 'nullable|string|max:255',
        ]);

        Setting::set('herosms_enabled', $request->boolean('herosms_enabled') ? '1' : '0');
        Setting::set('grizzly_enabled', $request->boolean('grizzly_enabled') ? '1' : '0');

        $keys = [
            'herosms_api_key', 'herosms_exchange_rate', 'herosms_commission_type',
            'herosms_number_price', 'herosms_cancel_refund_pct',
            'herosms_expiry_minutes', 'herosms_max_active', 'herosms_webhook_secret',
            'herosms_supplemental_wa_country_id', 'herosms_supplemental_wa_country_name',
            'grizzly_api_key', 'grizzly_webhook_secret',
        ];
        foreach ($keys as $key) {
            if ($request->filled($key) || $request->has($key)) {
                Setting::set($key, $request->input($key, ''));
            }
        }

        return back()->with('success', 'Virtual number settings saved successfully.');
    }

    public function testConnection()
    {
        $sms = new HeroSmsService();
        if (!$sms->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'Server 2 API key not configured.']);
        }

        try {
            $balance = $sms->getBalance();
            return response()->json([
                'success' => true,
                'balance' => $balance,
                'message' => "Server 2 connection successful! Balance: \${$balance}",
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()]);
        }
    }

    public function testConnectionServer1()
    {
        $sms = new GrizzlySmsService();
        if (!$sms->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'Server 1 API key not configured.']);
        }

        try {
            $balance = $sms->getBalance();
            return response()->json([
                'success' => true,
                'balance' => $balance,
                'message' => "Server 1 connection successful! Balance: \${$balance}",
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()]);
        }
    }

    public function debugPrices(\Illuminate\Http\Request $request)
    {
        $sms = new HeroSmsService();
        if (!$sms->isConfigured()) {
            return response()->json(['error' => 'Server 2 API key not configured.'], 422);
        }

        $country    = (int) $request->query('country', 0);
        $raw        = $sms->getRawPricesResponse($country);
        $normalized = $sms->getPricesForCountry($country);
        $raw['normalized_count']  = count($normalized);
        $raw['normalized_sample'] = array_slice($normalized, 0, 5, true);

        return response()->json($raw);
    }
}
