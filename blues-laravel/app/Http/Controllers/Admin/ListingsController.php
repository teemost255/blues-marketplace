<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ListingCategory;
use Illuminate\Http\Request;

class ListingsController extends Controller
{
    public function index(Request $request)
    {
        $query = Listing::query();
        if ($request->search)   $query->where('title', 'like', "%{$request->search}%");
        if ($request->category) $query->where('category', $request->category);
        if ($request->status !== null && $request->status !== '') {
            $query->where('is_active', (bool)$request->status);
        }
        $listings   = $query->latest()->paginate(20)->withQueryString();
        $categories = ListingCategory::where('is_active', true)->orderBy('name')->get();
        return view('admin.listings', compact('listings', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'price'    => 'required|numeric|min:0',
            'stock'    => 'required|integer|min:0',
            'category' => 'nullable|string|max:100',
        ]);

        $data = $request->only('title', 'description', 'category', 'price', 'stock');
        $data['is_active'] = $request->boolean('is_active');
        $data['featured']  = $request->boolean('featured');

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('listings', 'public');
            $data['image_path'] = $path;
        }

        Listing::create($data);
        return back()->with('success', 'Listing created successfully.');
    }

    public function edit(Listing $listing)
    {
        $categories = ListingCategory::where('is_active', true)->orderBy('name')->get();
        return view('admin.listings-edit', compact('listing', 'categories'));
    }

    public function update(Request $request, Listing $listing)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'price'    => 'required|numeric|min:0',
            'stock'    => 'required|integer|min:0',
            'category' => 'nullable|string|max:100',
        ]);

        $data = $request->only('title', 'description', 'category', 'price', 'stock');
        $data['is_active'] = $request->boolean('is_active');
        $data['featured']  = $request->boolean('featured');

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('listings', 'public');
            $data['image_path'] = $path;
        }

        $listing->update($data);
        return redirect()->route('admin.listings')->with('success', 'Listing updated successfully.');
    }

    public function destroy(Listing $listing)
    {
        $listing->delete();
        return back()->with('success', 'Listing deleted.');
    }
}
