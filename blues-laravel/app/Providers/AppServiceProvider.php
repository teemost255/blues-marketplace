<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Setting;
use Illuminate\Support\Facades\Config;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        try {
            $mailer = Setting::get('mail_mailer', '');
            if ($mailer) {
                Config::set('mail.default', $mailer);
            }

            $host = Setting::get('mail_host', '');
            if ($host) {
                Config::set('mail.mailers.smtp.host', $host);
            }

            $port = Setting::get('mail_port', '');
            if ($port) {
                Config::set('mail.mailers.smtp.port', (int) $port);
            }

            $encryption = Setting::get('mail_encryption', '');
            Config::set('mail.mailers.smtp.encryption', $encryption ?: null);

            $username = Setting::get('mail_username', '');
            if ($username) {
                Config::set('mail.mailers.smtp.username', $username);
            }

            $password = Setting::get('mail_password', '');
            if ($password) {
                Config::set('mail.mailers.smtp.password', $password);
            }

            $fromAddress = Setting::get('mail_from_address', '');
            if ($fromAddress) {
                Config::set('mail.from.address', $fromAddress);
            }

            $fromName = Setting::get('mail_from_name', '');
            if ($fromName) {
                Config::set('mail.from.name', $fromName);
            }
        } catch (\Exception $e) {
            // DB may not be ready yet (e.g. during migrations); silently skip
        }
    }
}
