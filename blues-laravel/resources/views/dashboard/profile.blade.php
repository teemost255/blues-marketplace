@extends('layouts.dashboard')
@section('title', 'Profile')
@section('page-title', 'My Profile')

@section('content')
<div class="max-w-2xl space-y-6">

{{-- ── Profile card ─────────────────────────────────────────────────── --}}
<div class="bg-slate-800 border border-slate-700 rounded-xl p-8">
    {{-- Avatar + info --}}
    <div class="flex items-center gap-4 mb-8 pb-6 border-b border-slate-700">
        <div class="w-16 h-16 rounded-2xl bg-brand flex items-center justify-center text-white text-2xl font-bold flex-shrink-0">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div>
            <h2 class="text-lg font-bold text-white">{{ $profile->display_name ?? $user->name }}</h2>
            <p class="text-slate-400 text-sm">{{ $user->email }}</p>
            <span class="inline-flex items-center gap-1 mt-1 text-xs px-2 py-0.5 rounded-full {{ $user->isActive() ? 'bg-green-900/50 text-green-400' : 'bg-yellow-900/50 text-yellow-400' }}">
                {{ ucfirst($user->status ?? 'active') }}
            </span>
        </div>
    </div>

    <form method="POST" action="{{ route('dashboard.profile.update') }}" class="space-y-5">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">Full Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                    class="w-full bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-brand text-sm @error('name') border-red-500 @enderror">
                @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">Display Name</label>
                <input type="text" name="display_name" value="{{ old('display_name', $profile->display_name) }}"
                    class="w-full bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-brand text-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1.5">Email Address</label>
            <input type="email" value="{{ $user->email }}" disabled
                class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-slate-500 text-sm cursor-not-allowed">
            <p class="text-xs text-slate-500 mt-1">Email cannot be changed.</p>
        </div>

        <div class="pt-4 border-t border-slate-700">
            <h3 class="text-sm font-semibold text-white mb-4">Change Password</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Current Password</label>
                    <input type="password" name="current_password"
                        class="w-full bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-brand text-sm @error('current_password') border-red-500 @enderror"
                        placeholder="Leave blank to keep current password">
                    @error('current_password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1.5">New Password</label>
                        <input type="password" name="password"
                            class="w-full bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-brand text-sm @error('password') border-red-500 @enderror"
                            placeholder="Min. 8 characters">
                        @error('password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1.5">Confirm New Password</label>
                        <input type="password" name="password_confirmation"
                            class="w-full bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-brand text-sm"
                            placeholder="Repeat new password">
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="bg-brand hover:bg-brand-dark text-white font-semibold px-6 py-3 rounded-lg text-sm transition-colors">Save Changes</button>
    </form>
</div>

{{-- ── Referral card ────────────────────────────────────────────────── --}}
@if($profile->referral_code)
<div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="font-bold text-white text-base">Referral Program</h3>
            <p class="text-slate-400 text-sm mt-0.5">Share your link — earn a bonus when friends join.</p>
        </div>
        <div class="w-10 h-10 rounded-xl bg-brand/10 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
    </div>

    {{-- Stats row --}}
    <div class="grid grid-cols-2 gap-3 mb-5">
        <div class="bg-slate-900 rounded-lg px-4 py-3 text-center">
            <p class="text-2xl font-bold text-brand">{{ $referralCount }}</p>
            <p class="text-slate-400 text-xs mt-0.5">Friends Referred</p>
        </div>
        <div class="bg-slate-900 rounded-lg px-4 py-3 text-center">
            <p class="text-2xl font-bold text-green-400">{{ $user->referred_by ? 'Yes' : 'No' }}</p>
            <p class="text-slate-400 text-xs mt-0.5">You Were Referred</p>
        </div>
    </div>

    {{-- Referral code display --}}
    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Your Referral Code</p>
    <div class="flex items-center gap-2 mb-4">
        <code class="flex-1 bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-brand font-mono font-bold text-sm tracking-widest select-all">{{ $profile->referral_code }}</code>
        <button onclick="copyCode('{{ $profile->referral_code }}')" class="flex-shrink-0 bg-brand/10 hover:bg-brand/20 border border-brand/30 text-brand px-4 py-3 rounded-lg text-sm font-semibold transition-colors flex items-center gap-1.5">
            <svg id="copy-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
            Copy
        </button>
    </div>

    {{-- Referral link display --}}
    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Your Referral Link</p>
    <div class="flex items-center gap-2">
        <input id="referral-link" type="text" readonly
            value="{{ url('/r/' . $profile->referral_code) }}"
            class="flex-1 bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-slate-300 text-xs font-mono select-all focus:outline-none focus:border-brand">
        <button onclick="copyLink()" class="flex-shrink-0 bg-slate-700 hover:bg-slate-600 text-white px-4 py-3 rounded-lg text-sm font-semibold transition-colors flex items-center gap-1.5">
            <svg id="link-copy-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
            Copy Link
        </button>
    </div>

    <p id="copy-feedback" class="text-green-400 text-xs mt-2 hidden">✓ Copied to clipboard!</p>
</div>
@endif

{{-- ── Email notification preferences ──────────────────────────────── --}}
<div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
    <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 rounded-xl bg-purple-500/10 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        </div>
        <div>
            <h3 class="font-bold text-white text-base">Email Notifications</h3>
            <p class="text-slate-400 text-sm">Control which emails we send you.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('dashboard.profile.notifications') }}" class="space-y-4">
        @csrf
        {{-- Master toggle --}}
        <label class="flex items-start justify-between gap-4 bg-slate-900 rounded-xl p-4 cursor-pointer group">
            <div>
                <p class="font-semibold text-white text-sm">All Email Notifications</p>
                <p class="text-slate-400 text-xs mt-0.5">Includes purchase confirmations, referral bonuses, and platform announcements.</p>
            </div>
            <div class="flex-shrink-0 pt-0.5">
                <input type="hidden" name="email_notifications" value="0">
                <input type="checkbox" name="email_notifications" value="1"
                    id="email-notif-toggle"
                    class="sr-only peer"
                    {{ $user->email_notifications ? 'checked' : '' }}
                    onchange="this.form.submit()">
                <label for="email-notif-toggle"
                    class="relative inline-flex w-11 h-6 cursor-pointer rounded-full bg-slate-700 peer-checked:bg-brand transition-colors duration-200
                           after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:rounded-full after:bg-white after:transition-transform after:duration-200
                           peer-checked:after:translate-x-5">
                </label>
            </div>
        </label>

        {{-- Specific types (informational) --}}
        <div class="space-y-2">
            @foreach([
                ['Purchase Confirmations', 'Receive a confirmation email every time you complete a purchase.', 'text-green-400', 'bg-green-400/10'],
                ['Referral Bonuses', 'Be notified when someone joins using your referral link.', 'text-brand', 'bg-brand/10'],
                ['Platform Announcements', 'Receive important updates, new listings, and platform news.', 'text-purple-400', 'bg-purple-400/10'],
            ] as [$label, $desc, $color, $bg])
            <div class="flex items-start gap-3 px-4 py-3 bg-slate-900/50 rounded-lg border border-slate-700/50">
                <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0 {{ $color }} opacity-70" style="background:currentColor"></div>
                <div>
                    <p class="text-sm font-medium text-slate-300">{{ $label }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $desc }}</p>
                </div>
                <span class="ml-auto text-xs {{ $user->email_notifications ? 'text-green-400' : 'text-slate-500' }} flex-shrink-0 font-medium">
                    {{ $user->email_notifications ? 'On' : 'Off' }}
                </span>
            </div>
            @endforeach
        </div>

        <p class="text-xs text-slate-500">Toggle above updates all preferences at once. You can change this at any time.</p>
    </form>
</div>

</div>

@push('scripts')
<script>
function copyCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        document.getElementById('copy-feedback').classList.remove('hidden');
        const icon = document.getElementById('copy-icon');
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>';
        setTimeout(() => {
            document.getElementById('copy-feedback').classList.add('hidden');
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>';
        }, 2500);
    });
}
function copyLink() {
    const val = document.getElementById('referral-link').value;
    navigator.clipboard.writeText(val).then(() => {
        document.getElementById('copy-feedback').classList.remove('hidden');
        const icon = document.getElementById('link-copy-icon');
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>';
        setTimeout(() => {
            document.getElementById('copy-feedback').classList.add('hidden');
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>';
        }, 2500);
    });
}
</script>
@endpush
@endsection
