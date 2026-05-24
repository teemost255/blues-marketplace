<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'paystack_public_key'      => Setting::get('paystack_public_key', ''),
            'paystack_secret_key'      => Setting::get('paystack_secret_key', ''),
            'paystack_webhook_secret'  => Setting::get('paystack_webhook_secret', ''),
            'site_name'                => Setting::get('site_name', 'Blues Marketplace'),
            'support_email'            => Setting::get('support_email', ''),
            'min_deposit'              => Setting::get('min_deposit', '500'),
            'max_deposit'              => Setting::get('max_deposit', '1000000'),
            'maintenance_mode'         => Setting::get('maintenance_mode', '0'),
            'logsplug_api_key'         => Setting::get('logsplug_api_key', ''),
            'logsplug_api_url'         => Setting::get('logsplug_api_url', 'https://logsplug.com/api'),
            'virtual_number_enabled'   => Setting::get('virtual_number_enabled', '1'),
        ];
        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'paystack_public_key'     => 'nullable|string',
            'paystack_secret_key'     => 'nullable|string',
            'paystack_webhook_secret' => 'nullable|string',
            'site_name'               => 'nullable|string|max:100',
            'support_email'           => 'nullable|email',
            'min_deposit'             => 'nullable|numeric|min:1',
            'max_deposit'             => 'nullable|numeric|min:1',
            'maintenance_mode'        => 'nullable|in:0,1',
            'logsplug_api_key'        => 'nullable|string',
            'logsplug_api_url'        => 'nullable|url',
            'virtual_number_enabled'  => 'nullable|in:0,1',
        ]);

        $keys = [
            'paystack_public_key', 'paystack_secret_key', 'paystack_webhook_secret',
            'site_name', 'support_email', 'min_deposit', 'max_deposit',
            'logsplug_api_key', 'logsplug_api_url',
        ];

        foreach ($keys as $key) {
            Setting::set($key, $request->input($key, ''));
        }
        Setting::set('maintenance_mode', $request->boolean('maintenance_mode') ? '1' : '0');
        Setting::set('virtual_number_enabled', $request->boolean('virtual_number_enabled') ? '1' : '0');

        return back()->with('success', 'Settings saved successfully.');
    }
}
