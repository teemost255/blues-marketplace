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
</script>
@endsection
