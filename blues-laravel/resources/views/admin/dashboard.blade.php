@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('content')

{{-- Top stats row --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 sm:p-5 overflow-hidden">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Total Users</p>
        <p class="text-xl sm:text-3xl font-bold text-white mt-1 truncate">{{ number_format($stats['total_users']) }}</p>
        <div class="flex gap-2 mt-2 text-xs flex-wrap">
            <span class="text-green-400">{{ $stats['active_users'] }} active</span>
            <span class="text-yellow-400">{{ $stats['suspended_users'] }} susp.</span>
            <span class="text-red-400">{{ $stats['banned_users'] }} ban.</span>
        </div>
        <p class="text-xs text-slate-500 mt-1 truncate">+{{ $stats['new_users_today'] }} today</p>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 sm:p-5 overflow-hidden">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Listings</p>
        <p class="text-xl sm:text-3xl font-bold text-white mt-1 truncate">{{ number_format($stats['total_listings']) }}</p>
        <p class="text-xs text-green-400 mt-2">{{ $stats['active_listings'] }} active</p>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 sm:p-5 overflow-hidden">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Total Purchases</p>
        <p class="text-xl sm:text-3xl font-bold text-white mt-1 truncate">{{ number_format($stats['total_purchases']) }}</p>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 sm:p-5 overflow-hidden">
        <p class="text-slate-400 text-xs font-medium uppercase tracking-wider">Revenue</p>
        <p class="text-xl sm:text-2xl font-bold text-white mt-1 truncate">₦{{ number_format($stats['total_revenue'], 2) }}</p>
        <p class="text-xs text-slate-400 mt-1 truncate">+₦{{ number_format($stats['revenue_today'], 2) }} today</p>
        <p class="text-xs text-slate-500 truncate">Vol: ₦{{ number_format($stats['wallet_volume'], 2) }}</p>
    </div>
</div>

{{-- Secondary stats --}}
<div class="grid grid-cols-2 lg:grid-cols-2 gap-4 mb-6">
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 flex items-center gap-3 overflow-hidden">
        <div class="w-9 h-9 rounded-lg bg-brand/10 flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs text-slate-400 truncate">Referrals</p>
            <p class="text-lg sm:text-xl font-bold text-white">{{ $stats['qualified_referrals'] }}</p>
            <p class="text-xs text-yellow-400">{{ $stats['pending_referrals'] }} pending</p>
        </div>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 flex items-center gap-3 overflow-hidden">
        <div class="w-9 h-9 rounded-lg bg-yellow-500/10 flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs text-slate-400">Open Tickets</p>
            <p class="text-lg sm:text-xl font-bold text-yellow-400">{{ $stats['open_tickets'] }}</p>
            <a href="{{ route('admin.tickets') }}" class="text-xs text-brand hover:underline">View →</a>
        </div>
    </div>
</div>


<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-2 bg-slate-800 border border-slate-700 rounded-xl p-5">
        <p class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-3">New Users</p>
        <div class="space-y-2">
            @foreach($stats['recent_users'] as $u)
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-sky-700 flex items-center justify-center text-xs font-bold text-white">{{ strtoupper(substr($u->name,0,1)) }}</div>
                    <div>
                        <p class="text-sm text-white font-medium">{{ $u->name }}</p>
                        <p class="text-xs text-slate-400">{{ $u->email }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @if($u->last_login_at)
                        <span class="text-xs text-slate-500 hidden sm:block">Last login {{ $u->last_login_at->diffForHumans() }}</span>
                    @endif
                    <span class="status-{{ $u->status }}">{{ ucfirst($u->status) }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
        <p class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-4">Quick Links</p>
        <div class="space-y-2">
            @foreach([
                ['label' => 'Manage Users',        'route' => 'admin.users',         'color' => 'text-brand'],
                ['label' => 'Transactions',         'route' => 'admin.transactions',  'color' => 'text-green-400'],
                ['label' => 'Support Tickets',      'route' => 'admin.tickets',       'color' => 'text-yellow-400'],
                ['label' => 'Referrals',            'route' => 'admin.referrals',     'color' => 'text-purple-400'],
                ['label' => 'Settings',             'route' => 'admin.settings',      'color' => 'text-slate-300'],
            ] as $link)
            <a href="{{ route($link['route']) }}" class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-slate-700/50 transition-colors group">
                <span class="text-sm {{ $link['color'] }} font-medium">{{ $link['label'] }}</span>
                <svg class="w-4 h-4 text-slate-600 group-hover:text-slate-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endforeach
        </div>
    </div>
</div>

{{-- Revenue Chart --}}
<div class="bg-slate-800 border border-slate-700 rounded-xl p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="font-semibold text-white">Revenue — Last 30 Days</h3>
            <p class="text-xs text-slate-400 mt-0.5">₦{{ number_format($stats['revenue_week'], 2) }} this week</p>
        </div>
        <div class="flex items-center gap-4 text-xs text-slate-400">
            <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-brand rounded inline-block"></span>Revenue</span>
            <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-purple-400 rounded inline-block"></span>Orders</span>
        </div>
    </div>
    <div class="h-48">
        <canvas id="revenueChart"></canvas>
    </div>
</div>

{{-- Recent Purchases --}}
<div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
        <h3 class="font-semibold text-white">Recent Purchases</h3>
        <a href="{{ route('admin.transactions') }}" class="text-xs text-brand hover:underline">View all →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-700 text-slate-400 text-xs uppercase">
                <th class="px-6 py-3 text-left">User</th>
                <th class="px-6 py-3 text-left">Listing</th>
                <th class="px-6 py-3 text-left">Amount</th>
                <th class="px-6 py-3 text-left">Date</th>
            </tr></thead>
            <tbody>
            @forelse($stats['recent_purchases'] as $p)
                <tr class="border-b border-slate-700/50 hover:bg-slate-700/20 transition-colors">
                    <td class="px-6 py-3 text-slate-300">{{ $p->user?->name ?? '—' }}</td>
                    <td class="px-6 py-3 text-slate-300 max-w-[180px] truncate">{{ $p->listing?->title ?? '—' }}</td>
                    <td class="px-6 py-3 text-green-400 font-semibold">₦{{ number_format($p->amount, 2) }}</td>
                    <td class="px-6 py-3 text-slate-400 text-xs">{{ $p->created_at->format('M j, H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-6 py-8 text-center text-slate-500">No purchases yet</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
const labels = @json($chartLabels);
const revenue = @json($chartRevenue);
const orders  = @json($chartOrders);
new Chart(ctx, {
    type: 'line',
    data: {
        labels,
        datasets: [
            {
                label: 'Revenue (₦)',
                data: revenue,
                borderColor: '#0ea5e9',
                backgroundColor: 'rgba(14,165,233,0.08)',
                tension: 0.4,
                fill: true,
                pointRadius: 2,
                yAxisID: 'y',
            },
            {
                label: 'Orders',
                data: orders,
                borderColor: '#a78bfa',
                backgroundColor: 'rgba(167,139,250,0.06)',
                tension: 0.4,
                fill: false,
                pointRadius: 2,
                yAxisID: 'y1',
            },
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { display: false } },
        scales: {
            x:  { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8', font: { size: 10 }, maxRotation: 0, autoSkip: true, maxTicksLimit: 8 } },
            y:  { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8', font: { size: 10 }, callback: v => '₦' + v.toLocaleString() }, position: 'left' },
            y1: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10 } }, position: 'right' },
        }
    }
});
</script>
@endpush
