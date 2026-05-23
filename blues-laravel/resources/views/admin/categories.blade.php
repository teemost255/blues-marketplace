@extends('layouts.admin')
@section('title','Categories')
@section('page-title','Categories')
@section('content')

<div class="flex justify-end mb-5">
    <button onclick="openModal('modal-add-category')" class="btn-primary flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Category
    </button>
</div>

<div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-700 text-slate-400 text-xs uppercase bg-slate-800/80">
                <th class="px-5 py-3 text-left">Category</th>
                <th class="px-5 py-3 text-left">Slug</th>
                <th class="px-5 py-3 text-left">Description</th>
                <th class="px-5 py-3 text-left">Listings</th>
                <th class="px-5 py-3 text-left">Status</th>
                <th class="px-5 py-3 text-left">Actions</th>
            </tr></thead>
            <tbody>
            @forelse($categories as $cat)
                <tr class="border-b border-slate-700/50 hover:bg-slate-700/20">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            @if($cat->icon)
                                <span class="text-lg">{{ $cat->icon }}</span>
                            @endif
                            <span class="text-white font-medium">{{ $cat->name }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-slate-400 font-mono text-xs">{{ $cat->slug ?? '—' }}</td>
                    <td class="px-5 py-3 text-slate-400">{{ Str::limit($cat->description, 50) ?: '—' }}</td>
                    <td class="px-5 py-3 text-white font-medium">{{ $cat->listings_count }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $cat->is_active ? 'bg-green-900/50 text-green-400' : 'bg-slate-700 text-slate-400' }}">
                            {{ $cat->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-1">
                            <button onclick="openModal('modal-edit-cat-{{ $cat->id }}')"
                                class="p-1.5 rounded text-slate-400 hover:text-sky-400 hover:bg-slate-700 transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button onclick="openModal('modal-delete-cat-{{ $cat->id }}')"
                                class="p-1.5 rounded text-slate-400 hover:text-red-400 hover:bg-slate-700 transition-colors" title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>

                {{-- Edit Modal --}}
                <div id="modal-edit-cat-{{ $cat->id }}" class="modal-overlay" style="display:none;">
                    <div class="modal-box">
                        <div class="flex items-center justify-between mb-5">
                            <h3 class="font-semibold text-white">Edit Category</h3>
                            <button onclick="closeModal('modal-edit-cat-{{ $cat->id }}')" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                        </div>
                        <form method="POST" action="{{ route('admin.categories.update', $cat) }}" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs text-slate-400 mb-1.5">Name *</label>
                                <input type="text" name="name" value="{{ $cat->name }}" required>
                            </div>
                            <div>
                                <label class="block text-xs text-slate-400 mb-1.5">Icon (emoji)</label>
                                <input type="text" name="icon" value="{{ $cat->icon }}" placeholder="e.g. 📘">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-400 mb-1.5">Description</label>
                                <textarea name="description" rows="2">{{ $cat->description }}</textarea>
                            </div>
                            <label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" {{ $cat->is_active ? 'checked' : '' }} class="w-4 h-4 rounded"> Active
                            </label>
                            <div class="flex gap-3 pt-2">
                                <button type="submit" class="btn-primary">Save</button>
                                <button type="button" onclick="closeModal('modal-edit-cat-{{ $cat->id }}')" class="btn-primary" style="background:#475569;">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Delete Modal --}}
                <div id="modal-delete-cat-{{ $cat->id }}" class="modal-overlay" style="display:none;">
                    <div class="modal-box">
                        <h3 class="font-semibold text-white mb-3">Delete Category</h3>
                        <p class="text-sm text-slate-300 mb-5">Delete "<strong>{{ $cat->name }}</strong>"? Listings in this category won't be deleted but will lose their category.</p>
                        <div class="flex gap-3">
                            <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger">Delete</button>
                            </form>
                            <button onclick="closeModal('modal-delete-cat-{{ $cat->id }}')" class="btn-primary" style="background:#475569;">Cancel</button>
                        </div>
                    </div>
                </div>
            @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-slate-500">No categories yet. Create one to get started.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-slate-700">{{ $categories->links() }}</div>
</div>

{{-- Add Category Modal --}}
<div id="modal-add-category" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-semibold text-white text-lg">New Category</h3>
            <button onclick="closeModal('modal-add-category')" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Name *</label>
                <input type="text" name="name" required placeholder="e.g. Facebook Accounts">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Icon (emoji)</label>
                <input type="text" name="icon" placeholder="e.g. 📘 🎵 📱">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Description</label>
                <textarea name="description" rows="2" placeholder="Brief description of this category…"></textarea>
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded"> Active
            </label>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Create Category</button>
                <button type="button" onclick="closeModal('modal-add-category')" class="btn-primary" style="background:#475569;">Cancel</button>
            </div>
        </form>
    </div>
</div>

@endsection
