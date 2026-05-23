@extends('layouts.admin')
@section('title','Support Tickets')
@section('page-title','Support Tickets')
@section('content')
<div class="space-y-4">
@forelse($tickets as $ticket)
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
        <div class="flex items-start justify-between gap-4 mb-3">
            <div>
                <h3 class="font-semibold text-white">{{ $ticket->subject }}</h3>
                <p class="text-xs text-slate-400 mt-0.5">{{ $ticket->user->email ?? '—' }} · {{ $ticket->created_at->diffForHumans() }}</p>
            </div>
            <div class="flex gap-2">
                <span class="px-2 py-1 rounded-full text-xs {{ ['open'=>'bg-yellow-900/50 text-yellow-400','in_progress'=>'bg-blue-900/50 text-blue-400','resolved'=>'bg-green-900/50 text-green-400','closed'=>'bg-slate-700 text-slate-400'][$ticket->status] ?? '' }}">{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</span>
                <span class="px-2 py-1 rounded-full text-xs {{ ['low'=>'bg-slate-700 text-slate-400','medium'=>'bg-orange-900/50 text-orange-400','high'=>'bg-red-900/50 text-red-400'][$ticket->priority] ?? '' }}">{{ ucfirst($ticket->priority) }}</span>
            </div>
        </div>
        <p class="text-sm text-slate-300 mb-4">{{ $ticket->message }}</p>
        @if($ticket->admin_reply)
            <div class="bg-slate-700/50 rounded-lg p-3 mb-4 border-l-2 border-sky-500">
                <p class="text-xs text-sky-400 mb-1 font-medium">Admin reply</p>
                <p class="text-sm text-slate-300">{{ $ticket->admin_reply }}</p>
            </div>
        @endif
        <form method="POST" action="{{ route('admin.tickets.reply', $ticket) }}" class="flex gap-3 items-end">
            @csrf
            <div class="flex-1"><label class="block text-xs text-slate-400 mb-1">Reply</label>
                <textarea name="admin_reply" rows="2" required class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-sky-500">{{ $ticket->admin_reply }}</textarea>
            </div>
            <div><label class="block text-xs text-slate-400 mb-1">Status</label>
                <select name="status" class="bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-sky-500">
                    @foreach(['open','in_progress','resolved','closed'] as $s)
                        <option value="{{ $s }}" {{ $ticket->status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
            <button class="bg-sky-500 hover:bg-sky-600 text-white px-4 py-2 rounded-lg text-sm font-medium h-[38px]">Send</button>
        </form>
    </div>
@empty
    <div class="text-center py-16 text-slate-500">No support tickets</div>
@endforelse
</div>
<div class="mt-4">{{ $tickets->links() }}</div>
@endsection
