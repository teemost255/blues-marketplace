@extends('layouts.admin')
@section('title','Edit Listing')
@section('page-title','Edit Listing')
@section('content')

<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.listings') }}" class="text-slate-400 hover:text-white transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <h2 class="text-white font-semibold">Edit: {{ $listing->title }}</h2>
    </div>

    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
        <form method="POST" action="{{ route('admin.listings.update', $listing) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            @if($listing->image)
                <div>
                    <label class="block text-xs text-slate-400 mb-2">Current Image</label>
                    <img src="{{ $listing->image }}" alt="" class="w-40 h-32 object-cover rounded-lg border border-slate-600">
                </div>
            @endif

            <div class="grid grid-cols-2 gap-5">
                <div class="col-span-2">
                    <label class="block text-xs text-slate-400 mb-1.5">Title *</label>
                    <input type="text" name="title" value="{{ old('title', $listing->title) }}" required>
                </div>
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
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Stock *</label>
                    <input type="number" min="0" name="stock" value="{{ old('stock', $listing->stock) }}" required>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Replace Image</label>
                    <input type="file" name="image" accept="image/*" class="text-slate-300 file:bg-slate-700 file:border-0 file:text-slate-300 file:px-3 file:py-1 file:rounded file:mr-2 file:cursor-pointer">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs text-slate-400 mb-1.5">Description</label>
                    <textarea name="description" rows="4">{{ old('description', $listing->description) }}</textarea>
                </div>
                <div class="col-span-2 flex items-center gap-5">
                    <label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ $listing->is_active ? 'checked' : '' }} class="w-4 h-4 rounded"> Active
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer">
                        <input type="checkbox" name="featured" value="1" {{ $listing->featured ? 'checked' : '' }} class="w-4 h-4 rounded"> Featured
                    </label>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Save Changes</button>
                <a href="{{ route('admin.listings') }}" class="btn-primary" style="background:#475569;">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
