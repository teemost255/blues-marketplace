<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
            'logsplug_api_url'         => Setting::get('logsplug_api_url', 'https://v2.api.logsplug.com/api/v2'),
            'herosms_api_key'          => Setting::get('herosms_api_key', ''),
            'fivesim_api_key'          => Setting::get('fivesim_api_key', ''),
            'usd_to_ngn_rate'          => Setting::get('usd_to_ngn_rate', '1600'),
            'virtual_number_enabled'   => Setting::get('virtual_number_enabled', '1'),
            'whatsapp_number'          => Setting::get('whatsapp_number', ''),
            'mail_mailer'              => Setting::get('mail_mailer', 'smtp'),
            'mail_host'                => Setting::get('mail_host', ''),
            'mail_port'                => Setting::get('mail_port', '587'),
            'mail_username'            => Setting::get('mail_username', ''),
            'mail_password'            => Setting::get('mail_password', ''),
            'mail_encryption'          => Setting::get('mail_encryption', 'tls'),
            'mail_from_address'        => Setting::get('mail_from_address', ''),
            'mail_from_name'           => Setting::get('mail_from_name', 'Blues Marketplace'),
            'referral_bonus'                    => Setting::get('referral_bonus', '0'),
            'referral_bonus_tier2'              => Setting::get('referral_bonus_tier2', '0'),
            'referral_bonus_tier3'              => Setting::get('referral_bonus_tier3', '0'),
            'referral_bonus_tier2_threshold'    => Setting::get('referral_bonus_tier2_threshold', '6'),
            'referral_bonus_tier3_threshold'    => Setting::get('referral_bonus_tier3_threshold', '16'),
            'promo_banner_enabled'     => Setting::get('promo_banner_enabled', '0'),
            'promo_banner_text'        => Setting::get('promo_banner_text', ''),
            'promo_banner_color'       => Setting::get('promo_banner_color', 'brand'),
            'low_balance_threshold'    => Setting::get('low_balance_threshold', '5'),
            'vn_commission_type'       => Setting::get('vn_commission_type', 'flat'),
            'vn_commission_value'      => Setting::get('vn_commission_value', '0'),
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
            'herosms_api_key'         => 'nullable|string',
            'fivesim_api_key'         => 'nullable|string',
            'usd_to_ngn_rate'         => 'nullable|numeric|min:1',
            'virtual_number_enabled'  => 'nullable|in:0,1',
            'mail_mailer'             => 'nullable|string|in:smtp,sendmail,log',
            'mail_host'               => 'nullable|string|max:255',
            'mail_port'               => 'nullable|integer|min:1|max:65535',
            'mail_username'           => 'nullable|string|max:255',
            'mail_password'           => 'nullable|string|max:255',
            'mail_encryption'         => 'nullable|string|in:tls,ssl,',
            'mail_from_address'       => 'nullable|email',
            'mail_from_name'          => 'nullable|string|max:100',
            'referral_bonus'                 => 'nullable|numeric|min:0',
            'referral_bonus_tier2'           => 'nullable|numeric|min:0',
            'referral_bonus_tier3'           => 'nullable|numeric|min:0',
            'referral_bonus_tier2_threshold' => 'nullable|integer|min:2',
            'referral_bonus_tier3_threshold' => 'nullable|integer|min:2',
            'promo_banner_text'       => 'nullable|string|max:300',
            'promo_banner_color'      => 'nullable|string|in:brand,green,yellow,red,purple',
            'low_balance_threshold'   => 'nullable|numeric|min:0',
            'vn_commission_type'      => 'nullable|in:flat,percent',
            'vn_commission_value'     => 'nullable|numeric|min:0',
        ]);

        $keys = [
            'paystack_public_key', 'paystack_secret_key', 'paystack_webhook_secret',
            'site_name', 'support_email', 'min_deposit', 'max_deposit',
            'logsplug_api_key', 'logsplug_api_url', 'herosms_api_key', 'fivesim_api_key', 'usd_to_ngn_rate', 'whatsapp_number',
            'mail_mailer', 'mail_host', 'mail_port', 'mail_username',
            'mail_password', 'mail_encryption', 'mail_from_address', 'mail_from_name',
            'referral_bonus', 'referral_bonus_tier2', 'referral_bonus_tier3',
            'referral_bonus_tier2_threshold', 'referral_bonus_tier3_threshold',
            'promo_banner_text', 'promo_banner_color', 'low_balance_threshold',
            'vn_commission_type', 'vn_commission_value',
        ];
        Setting::set('promo_banner_enabled', $request->boolean('promo_banner_enabled') ? '1' : '0');

        foreach ($keys as $key) {
            Setting::set($key, $request->input($key, ''));
        }
        Setting::set('maintenance_mode', $request->boolean('maintenance_mode') ? '1' : '0');
        Setting::set('virtual_number_enabled', $request->boolean('virtual_number_enabled') ? '1' : '0');

        return back()->with('success', 'Settings saved successfully.');
    }

    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            $fromAddress = Setting::get('mail_from_address', config('mail.from.address'));
            $fromName    = Setting::get('mail_from_name', config('mail.from.name', 'Blues Marketplace'));
            $siteName    = Setting::get('site_name', 'Blues Marketplace');

            Mail::raw(
                "This is a test email from {$siteName}.\n\nYour SMTP settings are configured correctly.",
                function ($message) use ($request, $fromAddress, $fromName, $siteName) {
                    $message->to($request->test_email)
                            ->from($fromAddress, $fromName)
                            ->subject("Test Email from {$siteName}");
                }
            );

            return back()->with('success', "Test email sent successfully to {$request->test_email}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }
}
