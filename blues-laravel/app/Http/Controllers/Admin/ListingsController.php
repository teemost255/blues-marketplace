<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\Request;

class ListingsController extends Controller
{
    public function index(Request $request)
    {
        $query = Listing::query();
        if ($request->search) $query->where('title', 'like', "%{$request->search}%");
        $listings = $query->latest()->paginate(20);
        return view('admin.listings', compact('listings'));
    }

    public function store(Request $request)
    {
        $request->validate(['title' => 'required', 'price' => 'required|numeric', 'stock' => 'required|integer']);
        Listing::create($request->only('title', 'description', 'category', 'price', 'stock', 'is_active'));
        return back()->with('success', 'Listing created.');
    }

    public function destroy(Listing $listing)
    {
        $listing->delete();
        return back()->with('success', 'Listing deleted.');
    }
}
