@extends('layouts.admin')
@section('title','Edit Listing')
@section('page-title','Edit Listing')
@section('content')

<div class="max-w-4xl">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.listings') }}" class="text-slate-400 hover:text-white transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <h2 class="text-white font-semibold truncate">Edit: {{ $listing->title }}</h2>
        @php $avail = $listing->availableCredentials()->count(); $total = $listing->credentials->count(); @endphp
        <span class="ml-auto shrink-0 px-3 py-1 rounded-full text-xs font-bold border
            {{ $avail > 0 ? 'bg-green-900/30 text-green-400 border-green-700/40' : 'bg-red-900/30 text-red-400 border-red-700/40' }}">
            {{ $avail }} / {{ $total }} in stock
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        {{-- ── Left: Main listing form ─────────────────────────────────────── --}}
        <div class="lg:col-span-3">
            <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
                <h3 class="text-sm font-semibold text-white mb-5 flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Listing Details
                </h3>
                <form method="POST" action="{{ route('admin.listings.update', $listing) }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-xs text-slate-400 mb-1.5">Title *</label>
                        <input type="text" name="title" value="{{ old('title', $listing->title) }}" required>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-slate-400 mb-1.5">Category</label>
                            <select name="category">
                                <option value="">Select category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->slug }}" {{ $listing->category === $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-400 mb-1.5">Price (₦) *</label>
                            <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $listing->price) }}" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 mb-1.5">
                            Credential Format
                            <span class="text-slate-500 font-normal">(displayed on card — shows buyers what format they receive)</span>
                        </label>
                        <input type="text" name="login_details" value="{{ old('login_details', $listing->login_details) }}"
                            placeholder="e.g. UID | Password | 2FA | Email | Cookie">
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 mb-1.5">Public Description</label>
                        <textarea name="description" rows="3">{{ old('description', $listing->description) }}</textarea>
                    </div>

                    {{-- Image upload --}}
                    <div>
                        <label class="block text-xs text-slate-400 mb-2">Listing Image</label>
                        <div class="flex items-start gap-4">
                            {{-- Current image preview --}}
                            <div id="img-preview-wrap" class="shrink-0 {{ $listing->image ? '' : 'hidden' }}">
                                <img id="img-preview" src="{{ $listing->image }}" alt="Current"
                                    class="w-20 h-20 rounded-xl object-cover border border-slate-600">
                            </div>
                            <div id="img-placeholder" class="shrink-0 w-20 h-20 rounded-xl bg-slate-700 border border-slate-600 flex items-center justify-center {{ $listing->image ? 'hidden' : '' }}">
                                <svg class="w-7 h-7 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="flex-1">
                                <label class="flex flex-col items-center justify-center w-full h-20 border-2 border-dashed border-slate-600 hover:border-brand/60 rounded-xl cursor-pointer bg-slate-700/30 hover:bg-slate-700/50 transition-all">
                                    <svg class="w-5 h-5 text-slate-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    <span class="text-xs text-slate-400">Click to upload image</span>
                                    <span class="text-[10px] text-slate-500">JPG, PNG, WEBP · max 4 MB</span>
                                    <input type="file" name="image" accept="image/*" class="sr-only" onchange="previewImage(this)">
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-5 pt-1">
                        <label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ $listing->is_active ? 'checked' : '' }} class="w-4 h-4 rounded"> Active
                        </label>
                        <label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer">
                            <input type="checkbox" name="featured" value="1" {{ $listing->featured ? 'checked' : '' }} class="w-4 h-4 rounded"> Featured
                        </label>
                    </div>

                    <div class="flex gap-3 pt-2 border-t border-slate-700/50">
                        <button type="submit" class="btn-primary">Save Changes</button>
                        <a href="{{ route('admin.listings') }}" class="btn-primary" style="background:#475569;">Back to Listings</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── Right: Credentials panel ─────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Add credential form --}}
            <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Credentials
                    </h3>
                    <span class="text-xs text-slate-400 bg-slate-700 px-2 py-0.5 rounded-full">
                        {{ $avail }} available · {{ $total }} total
                    </span>
                </div>
                <p class="text-xs text-slate-500 mb-3">Each entry = one account. Buyers get one entry per purchase, then it's removed from the pool.</p>
                <form method="POST" action="{{ route('admin.listings.credentials.store', $listing) }}">
                    @csrf
                    <label class="block text-xs text-slate-400 mb-1.5">New Credential Set</label>
                    <textarea name="details" rows="4" required
                        placeholder="Email: user@mail.com&#10;Password: Secure@123&#10;2FA: disabled&#10;UID: 100012345&#10;Cookie: c_user=100012345;xs=abc123"
                        class="font-mono text-xs"
                        style="font-family:monospace;font-size:0.72rem;"></textarea>
                    <button type="submit"
                        class="mt-2 w-full flex items-center justify-center gap-2 py-2 bg-brand hover:bg-brand-dark text-white text-sm font-bold rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Credential
                    </button>
                </form>
            </div>

            {{-- Credential list --}}
            <div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-700 flex items-center justify-between">
                    <p class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Credential Pool</p>
                    @if($total > 0)
                    <p class="text-[10px] text-slate-500">{{ $total }} entr{{ $total === 1 ? 'y' : 'ies' }}</p>
                    @endif
                </div>

                @if($listing->credentials->isEmpty())
                <div class="px-5 py-8 text-center">
                    <svg class="w-8 h-8 text-slate-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <p class="text-slate-500 text-xs">No credentials yet — add the first one above.</p>
                </div>
                @else
                <ul class="divide-y divide-slate-700/50 max-h-[480px] overflow-y-auto">
                    @foreach($listing->credentials as $cred)
                    <li class="px-4 py-3 flex items-start gap-3 {{ $cred->is_used ? 'opacity-50' : '' }}">
                        <div class="flex-1 min-w-0">
                            {{-- Status badge --}}
                            @if($cred->is_used)
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="text-[10px] font-bold bg-slate-700 text-slate-400 px-1.5 py-0.5 rounded-full">Delivered</span>
                                @if($cred->used_at)
                                <span class="text-[10px] text-slate-600">{{ $cred->used_at->format('M d, H:i') }}</span>
                                @endif
                            </div>
                            @else
                            <span class="text-[10px] font-bold bg-green-900/30 text-green-400 px-1.5 py-0.5 rounded-full mb-1 inline-block">Available</span>
                            @endif
                            {{-- Credential preview (masked) --}}
                            <p class="font-mono text-[11px] text-slate-400 leading-relaxed whitespace-pre-wrap break-all line-clamp-3">{{ $cred->details }}</p>
                            <p class="text-[10px] text-slate-600 mt-1">#{{ $cred->id }} · Added {{ $cred->created_at->diffForHumans() }}</p>
                        </div>
                        @if(!$cred->is_used)
                        <form method="POST" action="{{ route('admin.listings.credentials.destroy', [$listing, $cred]) }}"
                            onsubmit="return confirm('Remove this credential? This cannot be undone.')" class="shrink-0">
                            @csrf @method('DELETE')
                            <button type="submit" title="Remove" class="p-1.5 rounded text-slate-500 hover:text-red-400 hover:bg-red-900/20 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                        @endif
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    if (!input.files?.length) return;
    const reader = new FileReader();
    reader.onload = e => {
        const preview = document.getElementById('img-preview');
        const wrap    = document.getElementById('img-preview-wrap');
        const placeholder = document.getElementById('img-placeholder');
        if (preview) { preview.src = e.target.result; wrap.classList.remove('hidden'); }
        if (placeholder) placeholder.classList.add('hidden');
    };
    reader.readAsDataURL(input.files[0]);
}
</script>
@endsection
