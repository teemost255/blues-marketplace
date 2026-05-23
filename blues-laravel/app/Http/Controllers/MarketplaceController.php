<?php
namespace App\Http\Controllers;

use App\Models\{Listing, ListingCategory, Purchase, Wallet, WalletTransaction, Wishlist, Notification};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $categories = ListingCategory::all();
        $query = Listing::where('is_active', true)->where('stock', '>', 0);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'ilike', '%'.$request->search.'%')
                  ->orWhere('description', 'ilike', '%'.$request->search.'%');
            });
        }
        if ($request->filled('sort')) {
            match($request->sort) {
                'price_asc'  => $query->orderBy('price', 'asc'),
                'price_desc' => $query->orderBy('price', 'desc'),
                default      => $query->latest(),
            };
        } else {
            $query->latest();
        }

        $listings = $query->paginate(12)->withQueryString();
        $wishlistIds = Auth::check()
            ? Wishlist::where('user_id', Auth::id())->pluck('listing_id')->toArray()
            : [];

        return view('marketplace.index', compact('listings', 'categories', 'wishlistIds'));
    }

    public function show(int $id)
    {
        $listing = Listing::where('is_active', true)->findOrFail($id);
        $related = Listing::where('is_active', true)
            ->where('category', $listing->category)
            ->where('id', '!=', $listing->id)
            ->limit(4)->get();
        $inWishlist = Auth::check()
            ? Wishlist::where('user_id', Auth::id())->where('listing_id', $id)->exists()
            : false;
        return view('marketplace.show', compact('listing', 'related', 'inWishlist'));
    }

    public function buy(Request $request, int $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to purchase.');
        }

        $listing = Listing::where('is_active', true)->where('stock', '>', 0)->findOrFail($id);
        $user    = Auth::user();
        $wallet  = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);

        if ($wallet->balance < $listing->price) {
            return back()->with('error', 'Insufficient wallet balance. Please top up your wallet.');
        }

        $wallet->decrement('balance', $listing->price);
        $listing->decrement('stock');

        $purchase = Purchase::create([
            'user_id'    => $user->id,
            'listing_id' => $listing->id,
            'amount'     => $listing->price,
            'status'     => 'completed',
        ]);

        WalletTransaction::create([
            'user_id'     => $user->id,
            'amount'      => -$listing->price,
            'type'        => 'purchase',
            'reference'   => 'PUR-'.$purchase->id,
            'description' => 'Purchase: '.$listing->title,
        ]);

        Notification::create([
            'user_id' => $user->id,
            'title'   => 'Purchase Successful',
            'message' => 'Your purchase of "'.$listing->title.'" was successful. Check your orders for delivery details.',
            'type'    => 'success',
        ]);

        return redirect()->route('dashboard.orders')->with('success', 'Purchase successful! Check your orders for details.');
    }
}
