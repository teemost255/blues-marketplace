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
                <div class="col-span-2">
                    <label class="block text-xs text-slate-400 mb-1.5">Description</label>
                    <textarea name="description" rows="4">{{ old('description', $listing->description) }}</textarea>
                </div>
                <div class="col-span-2">
                    <label class="block text-xs text-slate-400 mb-1.5">
                        Login Details
                        <span class="text-yellow-400 ml-1">🔐 Shown to buyer after purchase</span>
                    </label>
                    <textarea name="login_details" rows="5" style="font-family:monospace;font-size:0.75rem;" placeholder="Email: example@mail.com&#10;Password: MyP@ss123&#10;Recovery: backup@mail.com">{{ old('login_details', $listing->login_details) }}</textarea>
                    <p class="text-xs text-slate-500 mt-1">Only the buyer can see this after a completed purchase.</p>
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
