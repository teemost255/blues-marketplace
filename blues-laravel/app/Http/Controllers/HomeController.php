<?php
namespace App\Http\Controllers;

use App\Models\{Listing, ListingCategory, User, Purchase};

class HomeController extends Controller
{
    public function index()
    {
        $categories       = ListingCategory::all();
        $featuredListings = Listing::where('is_active', true)->where('stock', '>', 0)->latest()->limit(8)->get();
        $recentActivity   = Purchase::with(['listing', 'user'])
            ->where('status', 'completed')
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn($p) => [
                'user'    => $p->user ? substr($p->user->name, 0, 1).'***' : 'Someone',
                'listing' => $p->listing?->title ?? 'an item',
                'price'   => number_format($p->amount, 2),
                'ago'     => $p->created_at->diffForHumans(),
            ]);
        $stats = [
            'listings'   => Listing::where('is_active', true)->count(),
            'users'      => User::count(),
            'sales'      => Purchase::where('status', 'completed')->count(),
            'categories' => ListingCategory::count(),
        ];
        $latestListings = Listing::where('is_active', true)->where('stock', '>', 0)->latest()->limit(6)->get();
        return view('home.index', compact('categories', 'featuredListings', 'latestListings', 'stats', 'recentActivity'));
    }
}
