<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\{Wishlist, Listing};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlists = Wishlist::with('listing')->where('user_id', Auth::id())->latest()->get();
        return view('dashboard.wishlist', compact('wishlists'));
    }

    public function store(Request $request)
    {
        $request->validate(['listing_id' => 'required|exists:listings,id']);
        Wishlist::firstOrCreate(['user_id' => Auth::id(), 'listing_id' => $request->listing_id]);
        if ($request->wantsJson()) return response()->json(['added' => true]);
        return back()->with('success', 'Added to wishlist.');
    }

    public function destroy(int $id)
    {
        Wishlist::where('user_id', Auth::id())->where('listing_id', $id)->delete();
        if (request()->wantsJson()) return response()->json(['removed' => true]);
        return back()->with('success', 'Removed from wishlist.');
    }
}
