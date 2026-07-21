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
            'kora_public_key'      => Setting::get('kora_public_key', ''),
            'kora_secret_key'      => Setting::get('kora_secret_key', ''),
            'kora_encryption_key'  => Setting::get('kora_encryption_key', ''),
            'site_name'                => Setting::get('site_name', 'Blues Marketplace'),
            'support_email'            => Setting::get('support_email', ''),
            'min_deposit'              => Setting::get('min_deposit', '500'),
            'max_deposit'              => Setting::get('max_deposit', '1000000'),
            'maintenance_mode'         => Setting::get('maintenance_mode', '0'),
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
            'bank_transfer_enabled'    => Setting::get('bank_transfer_enabled', '0'),
            'bank_name'                => Setting::get('bank_name', ''),
            'bank_account_number'      => Setting::get('bank_account_number', ''),
            'bank_account_name'        => Setting::get('bank_account_name', ''),
            'sujan_api_key'            => Setting::get('sujan_api_key', ''),
            'api_commission_percent'   => Setting::get('api_commission_percent', '0'),
        ];
        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'kora_public_key'     => 'nullable|string',
            'kora_secret_key'     => 'nullable|string',
            'kora_encryption_key' => 'nullable|string',
            'site_name'               => 'nullable|string|max:100',
            'support_email'           => 'nullable|email',
            'min_deposit'             => 'nullable|numeric|min:1',
            'max_deposit'             => 'nullable|numeric|min:1',
            'maintenance_mode'        => 'nullable|in:0,1',

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
            'bank_name'               => 'nullable|string|max:100',
            'bank_account_number'     => 'nullable|string|max:50',
            'bank_account_name'       => 'nullable|string|max:100',
            'sujan_api_key'             => 'nullable|string',
            'api_commission_percent'    => 'nullable|numeric|min:0|max:200',
        ]);

        $keys = [
            'kora_public_key', 'kora_secret_key', 'kora_encryption_key',
            'site_name', 'support_email', 'min_deposit', 'max_deposit',
            'whatsapp_number',
            'mail_mailer', 'mail_host', 'mail_port', 'mail_username',
            'mail_password', 'mail_encryption', 'mail_from_address', 'mail_from_name',
            'referral_bonus', 'referral_bonus_tier2', 'referral_bonus_tier3',
            'referral_bonus_tier2_threshold', 'referral_bonus_tier3_threshold',
            'promo_banner_text', 'promo_banner_color', 'low_balance_threshold',
            'bank_name', 'bank_account_number', 'bank_account_name',
            'sujan_api_key', 'api_commission_percent',
        ];
        Setting::set('bank_transfer_enabled', $request->boolean('bank_transfer_enabled') ? '1' : '0');
        Setting::set('promo_banner_enabled', $request->boolean('promo_banner_enabled') ? '1' : '0');

        foreach ($keys as $key) {
            Setting::set($key, $request->input($key, ''));
        }
        Setting::set('maintenance_mode', $request->boolean('maintenance_mode') ? '1' : '0');

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
