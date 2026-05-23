@extends('layouts.dashboard')
@section('title', 'Support')
@section('page-title', 'Support Tickets')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- New ticket --}}
    <div class="lg:col-span-1">
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
            <h2 class="font-semibold text-white mb-4">Open New Ticket</h2>
            <form method="POST" action="{{ route('dashboard.support.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Subject</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" required maxlength="200"
                        class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:border-brand text-sm @error('subject') border-red-500 @enderror"
                        placeholder="Brief description of your issue">
                    @error('subject')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Priority</label>
                    <select name="priority" class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2.5 text-white focus:outline-none focus:border-brand text-sm">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Message</label>
                    <textarea name="message" required rows="5" maxlength="2000"
                        class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:border-brand text-sm resize-none @error('message') border-red-500 @enderror"
                        placeholder="Describe your issue in detail...">{{ old('message') }}</textarea>
                    @error('message')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="w-full bg-brand hover:bg-brand-dark text-white font-semibold py-2.5 rounded-lg text-sm transition-colors">Submit Ticket</button>
            </form>
        </div>
    </div>

    {{-- Ticket list --}}
    <div class="lg:col-span-2">
        <div class="bg-slate-800 border border-slate-700 rounded-xl">
            <div class="px-6 py-4 border-b border-slate-700"><h2 class="font-semibold text-white">Your Tickets</h2></div>
            <div class="divide-y divide-slate-700/50">
                @forelse($tickets as $ticket)
                    <div class="px-6 py-4">
                        <div class="flex items-start justify-between gap-4 mb-2">
                            <h3 class="font-medium text-white text-sm">{{ $ticket->subject }}</h3>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ match($ticket->priority) { 'high'=>'bg-red-900/50 text-red-400', 'medium'=>'bg-yellow-900/50 text-yellow-400', default=>'bg-slate-700 text-slate-400' } }}">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ match($ticket->status) { 'open'=>'bg-brand/20 text-brand', 'in_progress'=>'bg-purple-900/50 text-purple-400', 'resolved'=>'bg-green-900/50 text-green-400', default=>'bg-slate-700 text-slate-400' } }}">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                            </div>
                        </div>
                        <p class="text-slate-400 text-sm line-clamp-2">{{ $ticket->message }}</p>
                        @if($ticket->admin_reply)
                            <div class="mt-3 bg-slate-700/50 rounded-lg p-3">
                                <p class="text-xs text-brand font-medium mb-1">Support Response:</p>
                                <p class="text-slate-300 text-sm">{{ $ticket->admin_reply }}</p>
                            </div>
                        @endif
                        <p class="text-xs text-slate-500 mt-2">{{ $ticket->created_at->format('M j, Y g:ia') }}</p>
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-500">
                        <p>No tickets yet. Use the form to contact support.</p>
                    </div>
                @endforelse
            </div>
            @if($tickets->hasPages())
                <div class="px-6 py-4 border-t border-slate-700">{{ $tickets->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
