@extends('layouts.admin')
@section('title', 'Referral Leaderboard')
@section('page-title', 'Referral Leaderboard')

@section('content')
{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Total Referrals</p>
        <p class="text-3xl font-bold text-white mt-1">{{ number_format($totalReferrals) }}</p>
        <p class="text-xs text-slate-500 mt-1">Users who joined via a referral link</p>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Active Referrers</p>
        <p class="text-3xl font-bold text-brand mt-1">{{ count($leaderboard) }}</p>
        <p class="text-xs text-slate-500 mt-1">Users with at least 1 referral</p>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Bonus Per Referral</p>
        <p class="text-3xl font-bold text-green-400 mt-1">${{ number_format($referralBonusRate, 2) }}</p>
        <p class="text-xs text-slate-500 mt-1">Configurable in Settings</p>
    </div>
</div>

{{-- Leaderboard --}}
<div class="bg-slate-800 border border-slate-700 rounded-xl">
    <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
        <h2 class="font-semibold text-white">Top Referrers</h2>
        <a href="{{ route('admin.settings') }}" class="text-xs text-brand hover:underline">Edit bonus rate →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-700 text-slate-400 text-xs uppercase">
                    <th class="px-6 py-3 text-left w-12">Rank</th>
                    <th class="px-6 py-3 text-left">User</th>
                    <th class="px-6 py-3 text-left">Referrals</th>
                    <th class="px-6 py-3 text-left">Total Bonus Earned</th>
                    <th class="px-6 py-3 text-left">Joined</th>
                </tr>
            </thead>
            <tbody>
            @forelse($leaderboard as $i => $entry)
                @php $u = $entry['user']; @endphp
                <tr class="border-b border-slate-700/50 hover:bg-slate-700/20 transition-colors">
                    <td class="px-6 py-3">
                        @if($i === 0)
                            <span class="text-yellow-400 font-bold text-lg">🥇</span>
                        @elseif($i === 1)
                            <span class="text-slate-300 font-bold text-lg">🥈</span>
                        @elseif($i === 2)
                            <span class="text-amber-600 font-bold text-lg">🥉</span>
                        @else
                            <span class="text-slate-500 font-semibold text-sm">{{ $i + 1 }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-brand flex items-center justify-center text-xs font-bold text-white flex-shrink-0">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-white font-medium">{{ $u->name }}</p>
                                <p class="text-slate-400 text-xs">{{ $u->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-3">
                        <span class="inline-flex items-center gap-1 bg-brand/10 text-brand text-xs font-bold px-2.5 py-1 rounded-full border border-brand/20">
                            {{ number_format($entry['referral_count']) }} referrals
                        </span>
                    </td>
                    <td class="px-6 py-3 text-green-400 font-semibold">${{ number_format($entry['total_bonus'], 2) }}</td>
                    <td class="px-6 py-3 text-slate-400 text-xs">{{ $u->created_at->format('M j, Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center text-slate-500">
                        <svg class="w-10 h-10 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        No referrals yet. Set a bonus amount in Settings to activate the referral program.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
