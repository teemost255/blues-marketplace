<?php
namespace App\Http\Controllers;

use App\Models\{Listing, ListingCategory, User, Purchase};

class HomeController extends Controller
{
    public function index()
    {
        $categories      = ListingCategory::all();
        $featuredListings = Listing::where('is_active', true)->where('stock', '>', 0)->latest()->limit(8)->get();
        $stats = [
            'listings' => Listing::where('is_active', true)->count(),
            'users'    => User::count(),
            'sales'    => Purchase::where('status', 'completed')->count(),
        ];
        return view('home.index', compact('categories', 'featuredListings', 'stats'));
    }
}
