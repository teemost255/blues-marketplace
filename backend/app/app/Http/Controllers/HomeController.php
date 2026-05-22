<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\ListingCategory;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featured = Listing::where('is_active', true)->orderBy('created_at', 'desc')->limit(6)->get();
        $recent = Listing::where('is_active', true)->orderBy('created_at', 'desc')->limit(5)->get();
        $stats = [
            'listings' => Listing::where('is_active', true)->count(),
            'users' => 0,
            'orders' => 0,
        ];
        $categories = ListingCategory::orderBy('name')->pluck('name')->toArray();

        return view('pages.index', compact('featured', 'recent', 'stats', 'categories'));
    }

    public function marketplaceIndex()
    {
        $listings = Listing::where('is_active', true)->paginate(12);
        return view('pages.marketplace', ['listings' => $listings, 'section' => 'index']);
    }

    public function marketplaceShow($id)
    {
        $listing = Listing::find($id);
        return view('pages.marketplace', ['listing' => $listing, 'section' => 'show', 'id' => $id]);
    }
}
