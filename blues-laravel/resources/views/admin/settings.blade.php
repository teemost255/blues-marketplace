@extends('layouts.admin')
@section('title','Settings')
@section('page-title','Settings')
@section('content')

@if(session('error'))
<div class="mb-4 bg-red-900/40 border border-red-700 text-red-300 text-sm rounded-lg px-4 py-3">
    {{ session('error') }}
</div>
@endif

<form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6 max-w-2xl">
    @csrf

    {{-- Paystack --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-9 h-9 rounded-lg bg-green-900/50 flex items-center justify-center">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
            <div>
                <h2 class="font-semibold text-white">Paystack Integration</h2>
                <p class="text-xs text-slate-400">API keys for payment processing</p>
            </div>
        </div>
        <div class="space-y-4">
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Public Key</label>
                <input type="text" name="paystack_public_key" value="{{ $settings['paystack_public_key'] }}"
                    placeholder="pk_live_… or pk_test_…" class="font-mono text-xs">
                <p class="text-xs text-slate-500 mt-1">Starts with pk_live_ (production) or pk_test_ (sandbox)</p>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Secret Key</label>
                <div class="relative">
                    <input type="password" name="paystack_secret_key" id="secret-key-input" value="{{ $settings['paystack_secret_key'] }}"
                        placeholder="sk_live_… or sk_test_…" class="font-mono text-xs pr-10">
                    <button type="button" onclick="toggleSecret()" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white">
                        <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
                <p class="text-xs text-slate-500 mt-1">Keep this secret. Never expose it to the client.</p>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Webhook Secret</label>
                <input type="text" name="paystack_webhook_secret" value="{{ $settings['paystack_webhook_secret'] }}"
                    placeholder="Your webhook signature secret" class="font-mono text-xs">
            </div>
        </div>
    </div>

    {{-- General --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-9 h-9 rounded-lg bg-sky-900/50 flex items-center justify-center">
                <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
            </div>
            <div>
                <h2 class="font-semibold text-white">General Settings</h2>
                <p class="text-xs text-slate-400">Site configuration</p>
            </div>
        </div>
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Site Name</label>
                    <input type="text" name="site_name" value="{{ $settings['site_name'] }}" placeholder="Blues Marketplace">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Support Email</label>
                    <input type="email" name="support_email" value="{{ $settings['support_email'] }}" placeholder="support@example.com">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Min Deposit (₦)</label>
                    <input type="number" name="min_deposit" value="{{ $settings['min_deposit'] }}" min="1" placeholder="500">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Max Deposit (₦)</label>
                    <input type="number" name="max_deposit" value="{{ $settings['max_deposit'] }}" min="1" placeholder="1000000">
                </div>
                <div class="sm:col-span-2 border-t border-slate-700 pt-4 mt-1">
                    <p class="text-xs font-semibold text-slate-300 uppercase tracking-wider mb-3">Referral Bonus Milestones</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs text-slate-400 mb-1.5">🥉 Bronze Bonus (₦)</label>
                            <input type="number" name="referral_bonus" value="{{ $settings['referral_bonus'] }}" min="0" step="0.01" placeholder="0">
                            <p class="text-xs text-slate-600 mt-1">Paid from referral #1 to #{{ $settings['referral_bonus_tier2_threshold'] ?? 5 }}</p>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-400 mb-1.5">🥈 Silver Bonus (₦)</label>
                            <input type="number" name="referral_bonus_tier2" value="{{ $settings['referral_bonus_tier2'] ?? '' }}" min="0" step="0.01" placeholder="0">
                            <p class="text-xs text-slate-600 mt-1">Paid from referral #{{ $settings['referral_bonus_tier2_threshold'] ?? 6 }}+</p>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-400 mb-1.5">🥇 Gold Bonus (₦)</label>
                            <input type="number" name="referral_bonus_tier3" value="{{ $settings['referral_bonus_tier3'] ?? '' }}" min="0" step="0.01" placeholder="0">
                            <p class="text-xs text-slate-600 mt-1">Paid from referral #{{ $settings['referral_bonus_tier3_threshold'] ?? 16 }}+</p>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-400 mb-1.5">Silver Threshold (referral #)</label>
                            <input type="number" name="referral_bonus_tier2_threshold" value="{{ $settings['referral_bonus_tier2_threshold'] ?? 6 }}" min="2" step="1" placeholder="6">
                            <p class="text-xs text-slate-600 mt-1">Silver unlocks at this referral count</p>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-400 mb-1.5">Gold Threshold (referral #)</label>
                            <input type="number" name="referral_bonus_tier3_threshold" value="{{ $settings['referral_bonus_tier3_threshold'] ?? 16 }}" min="2" step="1" placeholder="16">
                            <p class="text-xs text-slate-600 mt-1">Gold unlocks at this referral count</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="pt-2 border-t border-slate-700">
                <label class="flex items-center gap-3 cursor-pointer">
                    <div class="relative">
                        <input type="checkbox" name="maintenance_mode" value="1" id="maintenance-toggle"
                            {{ $settings['maintenance_mode'] === '1' ? 'checked' : '' }} class="sr-only">
                        <div class="w-10 h-6 rounded-full transition-colors" id="toggle-bg"
                             style="background: {{ $settings['maintenance_mode'] === '1' ? '#dc2626' : '#475569' }}"></div>
                        <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full transition-transform" id="toggle-dot"
                             style="transform: {{ $settings['maintenance_mode'] === '1' ? 'translateX(1rem)' : 'translateX(0)' }}"></div>
                    </div>
                    <div>
                        <p class="text-sm text-white font-medium">Maintenance Mode</p>
                        <p class="text-xs text-slate-400">Temporarily disable the site for users</p>
                    </div>
                </label>
            </div>
        </div>
    </div>

    {{-- Email / SMTP --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-9 h-9 rounded-lg bg-blue-900/50 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <h2 class="font-semibold text-white">Email / SMTP</h2>
                <p class="text-xs text-slate-400">Configure outgoing email delivery for announcements and notifications</p>
            </div>
        </div>
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Mail Driver</label>
                    <select name="mail_mailer" class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-sky-500">
                        <option value="smtp"    {{ $settings['mail_mailer'] === 'smtp'    ? 'selected' : '' }}>SMTP</option>
                        <option value="log"     {{ $settings['mail_mailer'] === 'log'     ? 'selected' : '' }}>Log (testing only)</option>
                        <option value="sendmail"{{ $settings['mail_mailer'] === 'sendmail'? 'selected' : '' }}>Sendmail</option>
                    </select>
                    <p class="text-xs text-slate-500 mt-1">Use SMTP for real delivery; Log to write emails to the log file instead.</p>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Encryption</label>
                    <select name="mail_encryption" class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-sky-500">
                        <option value="tls" {{ $settings['mail_encryption'] === 'tls' ? 'selected' : '' }}>TLS (port 587)</option>
                        <option value="ssl" {{ $settings['mail_encryption'] === 'ssl' ? 'selected' : '' }}>SSL (port 465)</option>
                        <option value=""    {{ $settings['mail_encryption'] === ''    ? 'selected' : '' }}>None (port 25)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">SMTP Host</label>
                    <input type="text" name="mail_host" value="{{ $settings['mail_host'] }}"
                        placeholder="e.g. smtp.gmail.com" class="font-mono text-xs">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">SMTP Port</label>
                    <input type="number" name="mail_port" value="{{ $settings['mail_port'] }}"
                        placeholder="587" min="1" max="65535">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">SMTP Username</label>
                    <input type="text" name="mail_username" value="{{ $settings['mail_username'] }}"
                        placeholder="your@email.com" class="font-mono text-xs">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">SMTP Password</label>
                    <div class="relative">
                        <input type="password" name="mail_password" id="mail-password-input" value="{{ $settings['mail_password'] }}"
                            placeholder="App password or SMTP password" class="font-mono text-xs pr-10">
                        <button type="button" onclick="toggleMailPassword()" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">For Gmail, use an App Password, not your account password.</p>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">From Address</label>
                    <input type="email" name="mail_from_address" value="{{ $settings['mail_from_address'] }}"
                        placeholder="noreply@yourdomain.com">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">From Name</label>
                    <input type="text" name="mail_from_name" value="{{ $settings['mail_from_name'] }}"
                        placeholder="Blues Marketplace">
                </div>
            </div>
        </div>
    </div>

    {{-- Virtual Numbers / GrizzlySMS --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6" id="virtual-numbers">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-9 h-9 rounded-lg bg-green-900/50 flex items-center justify-center">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            </div>
            <div>
                <h2 class="font-semibold text-white">Virtual Numbers — GrizzlySMS</h2>
                <p class="text-xs text-slate-400">API credentials for GrizzlySMS virtual number provisioning. Prices in USD, auto-converted to NGN.</p>
            </div>
        </div>
        <div class="space-y-4">
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">GrizzlySMS API Key</label>
                <div class="relative">
                    <input type="password" name="grizzlysms_api_key" id="grizzlysms-key-input" value="{{ $settings['grizzlysms_api_key'] }}"
                        placeholder="Paste your GrizzlySMS API key here" class="font-mono text-xs pr-10">
                    <button type="button" onclick="toggleGrizzlyKey()" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
                <p class="text-xs text-slate-500 mt-1">Get your API key from <a href="https://grizzlysms.com/profile-settings" target="_blank" class="text-green-400 hover:underline">GrizzlySMS → Profile Settings</a>. Numbers from $0.04.</p>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">USD → NGN Exchange Rate</label>
                <input type="number" name="usd_to_ngn_rate" value="{{ $settings['usd_to_ngn_rate'] }}"
                    min="1" step="1" placeholder="e.g. 1600" class="w-full">
                <p class="text-xs text-slate-500 mt-1">Used to convert USD prices to Naira for display and billing. Update when the exchange rate changes.</p>
            </div>
        </div>
    </div>

    {{-- Virtual Numbers — Commission & Toggle --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-9 h-9 rounded-lg bg-sky-900/50 flex items-center justify-center">
                <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h2 class="font-semibold text-white">Virtual Numbers — Commission & Controls</h2>
                <p class="text-xs text-slate-400">Applies to all virtual number providers</p>
            </div>
        </div>
        <div class="space-y-4" id="virtual-numbers">
            <p class="text-xs font-semibold text-slate-300 uppercase tracking-wider mb-3">Platform Commission</p>
            <p class="text-xs text-slate-400 mb-4">Added on top of the API price and deducted from the user's wallet. The API is always charged its original rate from the API wallet.</p>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Commission Type</label>
                    <select name="vn_commission_type" id="vn-commission-type"
                        class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-sky-500">
                        <option value="flat"    {{ $settings['vn_commission_type'] === 'flat'    ? 'selected' : '' }}>Flat amount (₦)</option>
                        <option value="percent" {{ $settings['vn_commission_type'] === 'percent' ? 'selected' : '' }}>Percentage (%)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5" id="vn-comm-label">
                        {{ $settings['vn_commission_type'] === 'percent' ? 'Commission (%)' : 'Commission (₦)' }}
                    </label>
                    <input type="number" name="vn_commission_value" id="vn-commission-value"
                        value="{{ $settings['vn_commission_value'] }}"
                        min="0" step="0.01"
                        placeholder="{{ $settings['vn_commission_type'] === 'percent' ? 'e.g. 10' : 'e.g. 50' }}">
                    <p class="text-xs text-slate-500 mt-1">Set to 0 to charge no commission.</p>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-700">
                <label class="flex items-center gap-3 cursor-pointer">
                    <div class="relative">
                        <input type="checkbox" name="virtual_number_enabled" value="1" id="vn-toggle"
                            {{ $settings['virtual_number_enabled'] === '1' ? 'checked' : '' }} class="sr-only">
                        <div class="w-10 h-6 rounded-full transition-colors" id="vn-toggle-bg"
                             style="background: {{ $settings['virtual_number_enabled'] === '1' ? '#0ea5e9' : '#475569' }}"></div>
                        <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full transition-transform" id="vn-toggle-dot"
                             style="transform: {{ $settings['virtual_number_enabled'] === '1' ? 'translateX(1rem)' : 'translateX(0)' }}"></div>
                    </div>
                    <div>
                        <p class="text-sm text-white font-medium">Enable Virtual Numbers</p>
                        <p class="text-xs text-slate-400">Toggle the Virtual Numbers section in the user dashboard</p>
                    </div>
                </label>
            </div>
        </div>
    </div>

    {{-- WhatsApp Support --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-9 h-9 rounded-lg bg-green-900/50 flex items-center justify-center">
                <svg class="w-5 h-5 text-green-400" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                    <path d="M12 0C5.373 0 0 5.373 0 12c0 2.123.554 4.118 1.528 5.847L.057 23.882l6.204-1.448A11.945 11.945 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.891 0-3.667-.5-5.208-1.378l-.374-.217-3.872.904.951-3.768-.243-.389A9.956 9.956 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
                </svg>
            </div>
            <div>
                <h2 class="font-semibold text-white">WhatsApp Support</h2>
                <p class="text-xs text-slate-400">Floating chat button shown to all users on the site</p>
            </div>
        </div>
        <div>
            <label class="block text-xs text-slate-400 mb-1.5">WhatsApp Number</label>
            <div class="flex gap-2 items-start">
                <div class="flex-1">
                    <input type="text" name="whatsapp_number" value="{{ $settings['whatsapp_number'] }}"
                        placeholder="e.g. 2348012345678 (include country code, no + or spaces)"
                        class="font-mono text-sm">
                    <p class="text-xs text-slate-500 mt-1.5">Include country code without + or spaces — e.g. <span class="text-slate-300 font-mono">2348012345678</span> for a Nigerian number. Leave blank to hide the button.</p>
                </div>
                @if($settings['whatsapp_number'])
                <a href="https://wa.me/{{ $settings['whatsapp_number'] }}" target="_blank"
                   class="shrink-0 flex items-center gap-1.5 bg-green-600 hover:bg-green-500 text-white text-xs font-semibold px-3 py-2 rounded-lg transition-colors mt-0.5">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.123.554 4.118 1.528 5.847L.057 23.882l6.204-1.448A11.945 11.945 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.891 0-3.667-.5-5.208-1.378l-.374-.217-3.872.904.951-3.768-.243-.389A9.956 9.956 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
                    Test
                </a>
                @endif
            </div>
        </div>
    </div>

    {{-- System Info --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
        <h2 class="font-semibold text-white mb-4">System Info</h2>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between py-2 border-b border-slate-700/50">
                <span class="text-slate-400">Framework</span>
                <span class="text-white">Laravel {{ app()->version() }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-slate-700/50">
                <span class="text-slate-400">PHP Version</span>
                <span class="text-white">{{ phpversion() }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-slate-700/50">
                <span class="text-slate-400">Database</span>
                <span class="text-white">PostgreSQL</span>
            </div>
            <div class="flex justify-between py-2">
                <span class="text-slate-400">App Environment</span>
                <span class="text-white">{{ config('app.env') }}</span>
            </div>
        </div>
    </div>

    {{-- Promo Banner --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 mb-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-9 h-9 rounded-lg bg-yellow-900/50 flex items-center justify-center">
                <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            <div>
                <h2 class="font-semibold text-white">Homepage Promo Banner</h2>
                <p class="text-xs text-slate-400">Show a dismissible announcement bar at the top of the homepage</p>
            </div>
        </div>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-white font-medium">Enable Banner</p>
                    <p class="text-xs text-slate-400 mt-0.5">Show the banner to all visitors</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="promo_banner_enabled" id="promo-banner-toggle" value="1" {{ ($settings['promo_banner_enabled'] ?? '0') === '1' ? 'checked' : '' }} class="sr-only peer">
                    <div id="promo-toggle-bg" class="w-11 h-6 rounded-full transition-all duration-200 relative" style="background:{{ ($settings['promo_banner_enabled'] ?? '0') === '1' ? '#0ea5e9' : '#475569' }}">
                        <div id="promo-toggle-dot" class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200" style="transform:{{ ($settings['promo_banner_enabled'] ?? '0') === '1' ? 'translateX(1.25rem)' : 'translateX(0)' }}"></div>
                    </div>
                </label>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Banner Message</label>
                <input type="text" name="promo_banner_text" value="{{ $settings['promo_banner_text'] ?? '' }}" placeholder="e.g. 🎉 Summer sale! Use code SUMMER20 for 20% off all listings." class="w-full">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Banner Color</label>
                <select name="promo_banner_color" class="w-full max-w-xs bg-slate-800 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-white focus:outline-none focus:border-brand">
                    <option value="brand"  {{ ($settings['promo_banner_color'] ?? 'brand') === 'brand'  ? 'selected' : '' }}>Blue (Brand)</option>
                    <option value="green"  {{ ($settings['promo_banner_color'] ?? '')       === 'green'  ? 'selected' : '' }}>Green</option>
                    <option value="yellow" {{ ($settings['promo_banner_color'] ?? '')       === 'yellow' ? 'selected' : '' }}>Yellow</option>
                    <option value="red"    {{ ($settings['promo_banner_color'] ?? '')       === 'red'    ? 'selected' : '' }}>Red</option>
                    <option value="purple" {{ ($settings['promo_banner_color'] ?? '')       === 'purple' ? 'selected' : '' }}>Purple</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Low Balance Alert --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 mb-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-9 h-9 rounded-lg bg-orange-900/50 flex items-center justify-center">
                <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h2 class="font-semibold text-white">Low Wallet Balance Alert</h2>
                <p class="text-xs text-slate-400">Send a notification when a user's balance drops below this amount after a purchase</p>
            </div>
        </div>
        <div class="max-w-xs">
            <label class="block text-xs text-slate-400 mb-1.5">Threshold Amount (₦)</label>
            <input type="number" name="low_balance_threshold" value="{{ $settings['low_balance_threshold'] ?? '5' }}" min="0" step="0.01" placeholder="5.00">
            <p class="text-xs text-slate-500 mt-1">Set to 0 to disable. Users will be notified when their balance falls below this value.</p>
        </div>
    </div>

    <button type="submit" class="btn-primary px-8 py-3 text-base">Save All Settings</button>
</form>

{{-- Test Email --}}
<div class="max-w-2xl mt-4">
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-9 h-9 rounded-lg bg-indigo-900/50 flex items-center justify-center">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            </div>
            <div>
                <h2 class="font-semibold text-white">Send Test Email</h2>
                <p class="text-xs text-slate-400">Verify your SMTP settings are working by sending a test message</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.settings.test-email') }}" class="flex gap-3 items-end">
            @csrf
            <div class="flex-1">
                <label class="block text-xs text-slate-400 mb-1.5">Recipient Email</label>
                <input type="email" name="test_email" placeholder="you@example.com" required
                    class="w-full">
            </div>
            <button type="submit"
                class="shrink-0 flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Send Test
            </button>
        </form>
        <p class="text-xs text-slate-500 mt-3">Make sure you save your SMTP settings above before sending a test email.</p>
    </div>
</div>

<script>
document.getElementById('maintenance-toggle').addEventListener('change', function() {
    document.getElementById('toggle-bg').style.background = this.checked ? '#dc2626' : '#475569';
    document.getElementById('toggle-dot').style.transform = this.checked ? 'translateX(1rem)' : 'translateX(0)';
});
document.getElementById('promo-banner-toggle').addEventListener('change', function() {
    document.getElementById('promo-toggle-bg').style.background  = this.checked ? '#0ea5e9' : '#475569';
    document.getElementById('promo-toggle-dot').style.transform  = this.checked ? 'translateX(1.25rem)' : 'translateX(0)';
});
function toggleSecret() {
    const inp = document.getElementById('secret-key-input');
    inp.type = inp.type === 'password' ? 'text' : 'password';
}
function toggleGrizzlyKey() {
    const inp = document.getElementById('grizzlysms-key-input');
    inp.type = inp.type === 'password' ? 'text' : 'password';
}
function toggleMailPassword() {
    const inp = document.getElementById('mail-password-input');
    inp.type = inp.type === 'password' ? 'text' : 'password';
}
document.getElementById('vn-toggle').addEventListener('change', function() {
    document.getElementById('vn-toggle-bg').style.background = this.checked ? '#0ea5e9' : '#475569';
    document.getElementById('vn-toggle-dot').style.transform = this.checked ? 'translateX(1rem)' : 'translateX(0)';
});
document.getElementById('vn-commission-type').addEventListener('change', function() {
    const label = document.getElementById('vn-comm-label');
    const input = document.getElementById('vn-commission-value');
    if (this.value === 'percent') {
        label.textContent = 'Commission (%)';
        input.placeholder = 'e.g. 10';
    } else {
        label.textContent = 'Commission (₦)';
        input.placeholder = 'e.g. 50';
    }
});
</script>
@endsection
