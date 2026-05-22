@extends('layouts.app')

@section('title', 'Admin — BluesMarketplace')

@section('content')
@php
    $section = $section ?? 'overview';
    $sections = [
        'overview' => 'Overview',
        'users' => 'Users',
        'listings' => 'Listings',
        'transactions' => 'Transactions',
        'tickets' => 'Tickets',
        'audit' => 'Audit',
        'settings' => 'Settings',
    ];
@endphp

<div class="flex min-h-screen bg-background text-foreground">
    <aside class="hidden w-72 shrink-0 border-r border-border bg-sidebar text-sidebar-foreground md:block">
        <div class="space-y-4 p-5">
            <a href="/" class="text-lg font-semibold">BluesMarketplace</a>
            <p class="text-xs uppercase tracking-[0.24em] text-sidebar-foreground/70">Admin panel</p>
        </div>
        <nav class="space-y-1 px-3 pb-4">
            @foreach($sections as $key => $label)
                <a href="/admin{{ $key === 'overview' ? '' : '/'.$key }}" class="flex items-center rounded-2xl px-3 py-2 text-sm transition {{ $section === $key ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'hover:bg-sidebar-accent/50' }}">{{ $label }}</a>
            @endforeach
        </nav>
    </aside>

    <div class="flex-1">
        <div class="border-b border-border bg-background/95 px-4 py-5 md:px-8">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ $sections[$section] }}</h1>
                    <p class="text-sm text-muted-foreground">Admin tools for managing listings, users, and transactions.</p>
                </div>
            </div>
        </div>

        <div class="px-4 py-8 md:px-8">
            @if ($section === 'overview')
                <div class="grid gap-6 lg:grid-cols-3">
                    @foreach(['Live listings' => '48', 'Active users' => '2,540', 'Pending tickets' => '6'] as $label => $value)
                        <div class="rounded-[1.5rem] border border-border bg-card p-6 shadow-elevated">
                            <p class="text-sm text-muted-foreground">{{ $label }}</p>
                            <p class="mt-4 text-3xl font-semibold">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>
            @elseif ($section === 'users')
                <div class="overflow-hidden rounded-[1.5rem] border border-border bg-card shadow-elevated">
                    <table class="min-w-full divide-y divide-border text-left text-sm text-foreground">
                        <thead class="bg-background/90">
                            <tr>
                                <th class="px-4 py-3">User</th>
                                <th class="px-4 py-3">Role</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach([['jane@example.com','user','active'], ['admin@example.com','admin','active']] as $user)
                                <tr>
                                    <td class="px-4 py-4">{{ $user[0] }}</td>
                                    <td class="px-4 py-4 capitalize">{{ $user[1] }}</td>
                                    <td class="px-4 py-4">{{ $user[2] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif ($section === 'listings')
                <div class="grid gap-4">
                    @foreach([['Logo pack','active'], ['UI kit','pending']] as $listing)
                        <div class="rounded-[1.5rem] border border-border bg-card p-5 shadow-elevated">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h2 class="text-lg font-semibold">{{ $listing[0] }}</h2>
                                    <p class="text-sm text-muted-foreground">Status: {{ $listing[1] }}</p>
                                </div>
                                <span class="rounded-full bg-muted px-3 py-1 text-sm">Manage</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif ($section === 'transactions')
                <div class="rounded-[1.5rem] border border-border bg-card p-6 shadow-elevated">
                    <h2 class="text-xl font-semibold">Recent transactions</h2>
                    <div class="mt-5 grid gap-4">
                        @foreach([['TXN-1001','₦12,000','Completed'], ['TXN-1002','₦8,500','Pending']] as $txn)
                            <div class="rounded-3xl border border-border bg-background/90 p-4">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="font-semibold">{{ $txn[0] }}</p>
                                        <p class="text-sm text-muted-foreground">{{ $txn[2] }}</p>
                                    </div>
                                    <p class="text-sm font-semibold">{{ $txn[1] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif ($section === 'tickets')
                <div class="space-y-4">
                    @foreach([['Order issue','Open'], ['Feature request','Closed']] as $ticket)
                        <div class="rounded-[1.5rem] border border-border bg-card p-5 shadow-elevated">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h2 class="text-lg font-semibold">{{ $ticket[0] }}</h2>
                                    <p class="text-sm text-muted-foreground">Status: {{ $ticket[1] }}</p>
                                </div>
                                <span class="rounded-full bg-muted px-3 py-1 text-sm">View</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif ($section === 'audit')
                <div class="rounded-[1.5rem] border border-border bg-card p-6 shadow-elevated">
                    <h2 class="text-xl font-semibold">Audit log</h2>
                    <div class="mt-5 space-y-3 text-sm text-muted-foreground">
                        <p>May 21 — Listing #42 updated</p>
                        <p>May 20 — User jane@example.com approved</p>
                    </div>
                </div>
            @elseif ($section === 'settings')
                <div class="rounded-[1.75rem] border border-border bg-card p-6 shadow-elevated">
                    <h2 class="text-xl font-semibold">Admin settings</h2>
                    <form action="#" method="POST" class="mt-6 space-y-4">
                        <input type="text" value="Marketplace Admin" class="w-full rounded-2xl border border-border bg-background px-4 py-3 text-sm text-foreground outline-none" />
                        <input type="email" value="admin@example.com" class="w-full rounded-2xl border border-border bg-background px-4 py-3 text-sm text-foreground outline-none" />
                        <button type="submit" class="rounded-2xl bg-primary px-5 py-3 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90">Save changes</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
