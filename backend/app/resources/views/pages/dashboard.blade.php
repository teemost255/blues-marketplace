@extends('layouts.app')

@section('title', 'Dashboard — BluesMarketplace')

@section('content')
@php
    $section = $section ?? 'overview';
    $sections = [
        'overview' => 'Overview',
        'wallet' => 'Wallet',
        'orders' => 'Orders',
        'wishlist' => 'Wishlist',
        'notifications' => 'Notifications',
        'support' => 'Support',
        'profile' => 'Profile',
    ];
@endphp

<div class="flex min-h-screen bg-background text-foreground">
    <aside class="hidden w-72 shrink-0 border-r border-border bg-sidebar text-sidebar-foreground md:block">
        <div class="space-y-4 p-5">
            <a href="/" class="text-lg font-semibold">BluesMarketplace</a>
            <p class="text-xs uppercase tracking-[0.24em] text-sidebar-foreground/70">Dashboard</p>
        </div>
        <nav class="space-y-1 px-3 pb-4">
            @foreach($sections as $key => $label)
                <a href="/dashboard{{ $key === 'overview' ? '' : '/'.$key }}" class="flex items-center rounded-2xl px-3 py-2 text-sm transition {{ $section === $key ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'hover:bg-sidebar-accent/50' }}">{{ $label }}</a>
            @endforeach
        </nav>
        <div class="p-3">
            <a href="/admin" class="block rounded-2xl border border-sidebar-border bg-muted px-4 py-3 text-sm text-sidebar-foreground transition hover:bg-sidebar-accent/40">Switch to admin</a>
        </div>
    </aside>

    <div class="flex-1">
        <div class="border-b border-border bg-background/95 px-4 py-5 md:px-8">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ $sections[$section] }}</h1>
                    <p class="text-sm text-muted-foreground">Manage your profile, orders, wallet, and support tickets.</p>
                </div>
            </div>
        </div>

        <div class="px-4 py-8 md:px-8">
            @if ($section === 'overview')
                <div class="grid gap-6 lg:grid-cols-3">
                    @foreach(['Wallet balance' => '₦54,000', 'Orders' => '12', 'Wishlist' => '8'] as $label => $value)
                        <div class="rounded-[1.5rem] border border-border bg-card p-6 shadow-elevated">
                            <p class="text-sm text-muted-foreground">{{ $label }}</p>
                            <p class="mt-4 text-3xl font-semibold">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>
            @elseif ($section === 'wallet')
                <div class="space-y-6">
                    <div class="rounded-[1.75rem] border border-border bg-card p-6 shadow-elevated">
                        <h2 class="text-xl font-semibold">Wallet balance</h2>
                        <p class="mt-3 text-3xl font-bold">₦54,000</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-[1.75rem] border border-border bg-background/90 p-6">Recent transactions placeholder</div>
                        <div class="rounded-[1.75rem] border border-border bg-background/90 p-6">Payment methods placeholder</div>
                    </div>
                </div>
            @elseif ($section === 'orders')
                <div class="overflow-hidden rounded-[1.5rem] border border-border bg-card shadow-elevated">
                    <table class="min-w-full divide-y divide-border text-left text-sm text-foreground">
                        <thead class="bg-background/90">
                            <tr>
                                <th class="px-4 py-3">Order</th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach([['#1024','May 18','Completed','₦6,500'], ['#1025','May 20','Pending','₦3,200']] as $order)
                                <tr>
                                    <td class="px-4 py-4">{{ $order[0] }}</td>
                                    <td class="px-4 py-4">{{ $order[1] }}</td>
                                    <td class="px-4 py-4">{{ $order[2] }}</td>
                                    <td class="px-4 py-4">{{ $order[3] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif ($section === 'wishlist')
                <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach(range(1, 6) as $item)
                        <div class="group rounded-[1.5rem] border border-border bg-card p-5 shadow-elevated transition hover:-translate-y-1">
                            <div class="h-44 rounded-3xl bg-muted"></div>
                            <h3 class="mt-4 text-lg font-semibold">Wish item #{{ $item }}</h3>
                            <p class="mt-2 text-sm text-muted-foreground">Saved listing with quick access to purchase later.</p>
                        </div>
                    @endforeach
                </div>
            @elseif ($section === 'notifications')
                <div class="space-y-4">
                    @foreach([['New sale item available', '2 hours ago'], ['Your order has shipped', '1 day ago']] as $note)
                        <div class="rounded-[1.5rem] border border-border bg-card p-5">
                            <p class="font-semibold">{{ $note[0] }}</p>
                            <p class="mt-2 text-sm text-muted-foreground">{{ $note[1] }}</p>
                        </div>
                    @endforeach
                </div>
            @elseif ($section === 'support')
                <div class="rounded-[1.75rem] border border-border bg-card p-6 shadow-elevated">
                    <h2 class="text-xl font-semibold">Submit a support request</h2>
                    <form action="#" method="POST" class="mt-6 space-y-4">
                        <input type="text" placeholder="Subject" class="w-full rounded-2xl border border-border bg-background px-4 py-3 text-sm text-foreground outline-none" />
                        <textarea placeholder="Describe your issue" rows="5" class="w-full rounded-2xl border border-border bg-background px-4 py-3 text-sm text-foreground outline-none"></textarea>
                        <button type="submit" class="rounded-2xl bg-primary px-5 py-3 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90">Send request</button>
                    </form>
                </div>
            @elseif ($section === 'profile')
                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="rounded-[1.75rem] border border-border bg-card p-6 shadow-elevated">
                        <h2 class="text-xl font-semibold">Profile details</h2>
                        <form action="#" method="POST" class="mt-5 space-y-4">
                            <input type="text" value="Jane Doe" class="w-full rounded-2xl border border-border bg-background px-4 py-3 text-sm text-foreground outline-none" />
                            <input type="email" value="jane@example.com" class="w-full rounded-2xl border border-border bg-background px-4 py-3 text-sm text-foreground outline-none" />
                            <button type="submit" class="rounded-2xl bg-primary px-5 py-3 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90">Save profile</button>
                        </form>
                    </div>
                    <div class="rounded-[1.75rem] border border-border bg-background/90 p-6">Account overview placeholder</div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
