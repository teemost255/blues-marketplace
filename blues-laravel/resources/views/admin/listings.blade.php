@extends('layouts.admin')
@section('title','Listings')
@section('page-title','Listings')
@section('content')

<div class="flex flex-col sm:flex-row gap-3 mb-5">
    <form method="GET" class="flex gap-2 flex-1 max-w-2xl">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search listings…" class="flex-1">
        <select name="category" class="w-44">
            <option value="">All categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <select name="status" class="w-32">
            <option value="">All status</option>
            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
        </select>
        <button class="btn-primary">Filter</button>
        @if(request()->hasAny(['search','category','status']))
            <a href="{{ route('admin.listings') }}" class="btn-primary" style="background:#475569;">Clear</a>
        @endif
    </form>
    <button onclick="openModal('modal-add-listing')" class="btn-primary flex items-center gap-2 shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Listing
    </button>
</div>

<div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-700 text-slate-400 text-xs uppercase bg-slate-800/80">
                <th class="px-5 py-3 text-left">Listing</th>
                <th class="px-5 py-3 text-left">Category</th>
                <th class="px-5 py-3 text-left">Price</th>
                <th class="px-5 py-3 text-left">Stock</th>
                <th class="px-5 py-3 text-left">Status</th>
                <th class="px-5 py-3 text-left">Actions</th>
            </tr></thead>
            <tbody>
            @forelse($listings as $l)
                <tr class="border-b border-slate-700/50 hover:bg-slate-700/20">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            @if($l->image)
                                <img src="{{ $l->image }}" alt="" class="w-10 h-10 rounded-lg object-cover shrink-0 border border-slate-600">
                            @else
                                <div class="w-10 h-10 rounded-lg bg-slate-700 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                            <div>
                                <p class="text-white font-medium">{{ $l->title }}</p>
                                @if($l->featured)<span class="text-xs bg-yellow-900/50 text-yellow-400 px-1.5 py-0.5 rounded">Featured</span>@endif
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-slate-400">{{ $l->category ?? '—' }}</td>
                    <td class="px-5 py-3 text-white font-medium">₦{{ number_format($l->price, 2) }}</td>
                    <td class="px-5 py-3 text-slate-300">{{ $l->stock }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $l->is_active ? 'bg-green-900/50 text-green-400' : 'bg-slate-700 text-slate-400' }}">
                            {{ $l->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-1">
                            <a href="{{ route('admin.listings.edit', $l) }}"
                               class="p-1.5 rounded text-slate-400 hover:text-sky-400 hover:bg-slate-700 transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button onclick="openModal('modal-delete-listing-{{ $l->id }}')"
                                class="p-1.5 rounded text-slate-400 hover:text-red-400 hover:bg-slate-700 transition-colors" title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                {{-- Delete Listing Modal --}}
                <div id="modal-delete-listing-{{ $l->id }}" class="modal-overlay" style="display:none;">
                    <div class="modal-box">
                        <h3 class="font-semibold text-white mb-3">Delete Listing</h3>
                        <p class="text-sm text-slate-300 mb-5">Delete "<strong>{{ $l->title }}</strong>"? This cannot be undone.</p>
                        <div class="flex gap-3">
                            <form method="POST" action="{{ route('admin.listings.destroy', $l) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger">Delete</button>
                            </form>
                            <button onclick="closeModal('modal-delete-listing-{{ $l->id }}')" class="btn-primary" style="background:#475569;">Cancel</button>
                        </div>
                    </div>
                </div>
            @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-slate-500">No listings yet</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-slate-700">{{ $listings->links() }}</div>
</div>

{{-- Add Listing Modal --}}
<div id="modal-add-listing" class="modal-overlay" style="display:none;">
    <div class="modal-box" style="max-width:600px;">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-semibold text-white text-lg">Add New Listing</h3>
            <button onclick="closeModal('modal-add-listing')" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.listings.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs text-slate-400 mb-1.5">Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Facebook PVA Account">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Category</label>
                    <select name="category">
                        <option value="">Select category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->slug }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Price (₦) *</label>
                    <input type="number" step="0.01" min="0" name="price" required placeholder="0.00">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Stock *</label>
                    <input type="number" min="0" name="stock" required placeholder="0">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Image</label>
                    <input type="file" name="image" accept="image/*" class="text-slate-300 file:bg-slate-700 file:border-0 file:text-slate-300 file:px-3 file:py-1 file:rounded file:mr-2 file:cursor-pointer">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs text-slate-400 mb-1.5">Description</label>
                    <textarea name="description" rows="3" placeholder="Describe this listing…"></textarea>
                </div>
                <div class="col-span-2 flex items-center gap-5">
                    <label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded"> Active
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer">
                        <input type="checkbox" name="featured" value="1" class="w-4 h-4 rounded"> Featured
                    </label>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Create Listing</button>
                <button type="button" onclick="closeModal('modal-add-listing')" class="btn-primary" style="background:#475569;">Cancel</button>
            </div>
        </form>
    </div>
</div>

@endsection
