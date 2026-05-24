@extends('layouts.admin')
@section('title','Settings')
@section('page-title','Settings')
@section('content')

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

    {{-- Virtual Numbers / Logsplug --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-9 h-9 rounded-lg bg-purple-900/50 flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            </div>
            <div>
                <h2 class="font-semibold text-white">Virtual Numbers — Logsplug</h2>
                <p class="text-xs text-slate-400">API credentials for virtual number provisioning</p>
            </div>
        </div>
        <div class="space-y-4">
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">API Base URL</label>
                <input type="url" name="logsplug_api_url" value="{{ $settings['logsplug_api_url'] }}"
                    placeholder="https://logsplug.com/api" class="font-mono text-xs">
                <p class="text-xs text-slate-500 mt-1">The root endpoint of the Logsplug API (no trailing slash).</p>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">API Key</label>
                <div class="relative">
                    <input type="password" name="logsplug_api_key" id="logsplug-key-input" value="{{ $settings['logsplug_api_key'] }}"
                        placeholder="Paste your Logsplug API key here" class="font-mono text-xs pr-10">
                    <button type="button" onclick="toggleLogsplugKey()" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
                <p class="text-xs text-slate-500 mt-1">Keep this secret. Used to authenticate all virtual number requests.</p>
            </div>
            <div class="pt-2 border-t border-slate-700">
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

    <button type="submit" class="btn-primary px-8 py-3 text-base">Save All Settings</button>
</form>

<script>
document.getElementById('maintenance-toggle').addEventListener('change', function() {
    document.getElementById('toggle-bg').style.background = this.checked ? '#dc2626' : '#475569';
    document.getElementById('toggle-dot').style.transform = this.checked ? 'translateX(1rem)' : 'translateX(0)';
});
function toggleSecret() {
    const inp = document.getElementById('secret-key-input');
    inp.type = inp.type === 'password' ? 'text' : 'password';
}
function toggleLogsplugKey() {
    const inp = document.getElementById('logsplug-key-input');
    inp.type = inp.type === 'password' ? 'text' : 'password';
}
document.getElementById('vn-toggle').addEventListener('change', function() {
    document.getElementById('vn-toggle-bg').style.background = this.checked ? '#0ea5e9' : '#475569';
    document.getElementById('vn-toggle-dot').style.transform = this.checked ? 'translateX(1rem)' : 'translateX(0)';
});
</script>
@endsection
