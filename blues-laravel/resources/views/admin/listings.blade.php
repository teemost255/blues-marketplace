@extends('layouts.admin')
@section('title','Listings')
@section('page-title','Listings')
@section('content')
<div class="bg-slate-800 border border-slate-700 rounded-xl p-6 mb-6">
    <h2 class="font-semibold text-white mb-4">Add New Listing</h2>
    <form method="POST" action="{{ route('admin.listings.store') }}" class="grid grid-cols-2 gap-4">
        @csrf
        <div><label class="block text-xs text-slate-400 mb-1">Title</label>
            <input type="text" name="title" required class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-sky-500"></div>
        <div><label class="block text-xs text-slate-400 mb-1">Category</label>
            <input type="text" name="category" class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-sky-500"></div>
        <div><label class="block text-xs text-slate-400 mb-1">Price ($)</label>
            <input type="number" step="0.01" name="price" required class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-sky-500"></div>
        <div><label class="block text-xs text-slate-400 mb-1">Stock</label>
            <input type="number" name="stock" required class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-sky-500"></div>
        <div class="col-span-2"><label class="block text-xs text-slate-400 mb-1">Description</label>
            <textarea name="description" rows="2" class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-sky-500"></textarea></div>
        <div class="col-span-2 flex items-center gap-3">
            <label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" checked class="rounded"> Active</label>
            <button class="bg-sky-500 hover:bg-sky-600 text-white px-5 py-2 rounded-lg text-sm font-medium">Add Listing</button>
        </div>
    </form>
</div>

<div class="bg-slate-800 border border-slate-700 rounded-xl">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-700 text-slate-400 text-xs uppercase">
                <th class="px-6 py-3 text-left">Title</th>
                <th class="px-6 py-3 text-left">Category</th>
                <th class="px-6 py-3 text-left">Price</th>
                <th class="px-6 py-3 text-left">Stock</th>
                <th class="px-6 py-3 text-left">Status</th>
                <th class="px-6 py-3 text-left">Actions</th>
            </tr></thead>
            <tbody>
            @forelse($listings as $l)
                <tr class="border-b border-slate-700/50 hover:bg-slate-700/30">
                    <td class="px-6 py-3 text-white font-medium">{{ $l->title }}</td>
                    <td class="px-6 py-3 text-slate-400">{{ $l->category ?? '—' }}</td>
                    <td class="px-6 py-3 text-white">${{ number_format($l->price,2) }}</td>
                    <td class="px-6 py-3 text-slate-300">{{ $l->stock }}</td>
                    <td class="px-6 py-3"><span class="px-2 py-0.5 rounded-full text-xs {{ $l->is_active ? 'bg-green-900/50 text-green-400' : 'bg-slate-700 text-slate-400' }}">{{ $l->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td class="px-6 py-3">
                        <form method="POST" action="{{ route('admin.listings.destroy', $l) }}" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="text-red-400 hover:text-red-300 text-xs">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-8 text-center text-slate-500">No listings yet</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-slate-700">{{ $listings->links() }}</div>
</div>
@endsection
