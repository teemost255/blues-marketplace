@extends('layouts.admin')
@section('title', 'Announcements')
@section('page-title', 'Announcements')

@section('content')
<div class="space-y-6">

    {{-- Compose form --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
        <div class="flex items-center justify-between gap-3 mb-6">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-brand/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-semibold text-white">Send Announcement</h2>
                    <p class="text-xs text-slate-400">Broadcast a message to all {{ $userCount }} registered users</p>
                </div>
            </div>
            {{-- SMTP status badge --}}
            @if($smtpConfigured)
                <a href="{{ route('admin.settings') }}" title="From: {{ $fromAddress }}"
                   class="flex items-center gap-1.5 bg-green-500/10 border border-green-500/25 text-green-400 text-xs font-medium px-3 py-1.5 rounded-lg hover:bg-green-500/20 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Email ready
                </a>
            @else
                <a href="{{ route('admin.settings') }}"
                   class="flex items-center gap-1.5 bg-amber-500/10 border border-amber-500/25 text-amber-400 text-xs font-medium px-3 py-1.5 rounded-lg hover:bg-amber-500/20 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    SMTP not set up
                </a>
            @endif
        </div>

        <form method="POST" action="{{ route('admin.announcements.store') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs text-slate-400 mb-1.5">Title <span class="text-red-400">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                        placeholder="e.g. Site Maintenance Tomorrow" maxlength="255"
                        class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-brand">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Type</label>
                    <select name="type" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-brand">
                        <option value="info"    {{ old('type','info') === 'info'    ? 'selected' : '' }}>Info (blue)</option>
                        <option value="success" {{ old('type') === 'success' ? 'selected' : '' }}>Success (green)</option>
                        <option value="warning" {{ old('type') === 'warning' ? 'selected' : '' }}>Warning (yellow)</option>
                        <option value="error"   {{ old('type') === 'error'   ? 'selected' : '' }}>Alert (red)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Message <span class="text-red-400">*</span></label>
                <textarea name="message" rows="4" required maxlength="2000"
                    placeholder="Write your announcement here. Users will see this in their notification panel."
                    class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-brand resize-none">{{ old('message') }}</textarea>
                <p class="text-xs text-slate-500 mt-1">Max 2,000 characters.</p>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-slate-700">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="send_email" value="1" class="w-4 h-4 rounded border-slate-600 bg-slate-700 text-brand focus:ring-brand">
                    <div>
                        <p class="text-sm text-white font-medium">Also send via email</p>
                        <p class="text-xs text-slate-400">Sends the announcement to every user's email address</p>
                    </div>
                </label>
                <button type="submit"
                    onclick="return confirm('Send this announcement to all {{ $userCount }} users?')"
                    class="flex items-center gap-2 bg-brand hover:bg-sky-600 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Send to All Users
                </button>
            </div>
        </form>
    </div>

    {{-- Past announcements --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl">
        <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
            <h2 class="font-semibold text-white">Announcement History</h2>
            <span class="text-xs text-slate-400">{{ $announcements->total() }} total</span>
        </div>

        @if($announcements->isEmpty())
            <div class="py-16 text-center text-slate-500">
                <svg class="w-10 h-10 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
                <p>No announcements sent yet</p>
            </div>
        @else
            <div class="divide-y divide-slate-700/50">
                @foreach($announcements as $ann)
                @php
                    $colors = [
                        'info'    => 'bg-brand/15 text-brand border-brand/30',
                        'success' => 'bg-green-500/15 text-green-400 border-green-500/30',
                        'warning' => 'bg-yellow-500/15 text-yellow-400 border-yellow-500/30',
                        'error'   => 'bg-red-500/15 text-red-400 border-red-500/30',
                    ];
                    $c = $colors[$ann->type] ?? $colors['info'];
                @endphp
                <div class="px-6 py-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $c }}">
                                    {{ ucfirst($ann->type) }}
                                </span>
                                @if($ann->email_sent)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-slate-700 text-slate-300">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        Email sent
                                    </span>
                                @endif
                            </div>
                            <p class="text-white font-medium text-sm">{{ $ann->title }}</p>
                            <p class="text-slate-400 text-sm mt-1 leading-relaxed">{{ $ann->message }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-xs text-slate-400">{{ $ann->created_at->diffForHumans() }}</p>
                            <p class="text-xs text-slate-500 mt-1">{{ $ann->recipients_count }} recipients</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @if($announcements->hasPages())
                <div class="px-6 py-4 border-t border-slate-700">{{ $announcements->links() }}</div>
            @endif
        @endif
    </div>

</div>
@endsection
