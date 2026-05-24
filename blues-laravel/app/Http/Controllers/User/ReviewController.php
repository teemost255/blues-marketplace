<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\{ListingReview, Purchase};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, Purchase $purchase)
    {
        if ($purchase->user_id !== Auth::id() || $purchase->status !== 'completed') {
            abort(403);
        }

        $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        ListingReview::updateOrCreate(
            ['user_id' => Auth::id(), 'purchase_id' => $purchase->id],
            [
                'listing_id' => $purchase->listing_id,
                'rating'     => $request->rating,
                'comment'    => $request->comment,
            ]
        );

        return back()->with('success', 'Thank you for your review!');
    }
}
