<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ListingReview;

class ReviewsController extends Controller
{
    public function index()
    {
        $reviews = ListingReview::with(['user', 'listing'])
            ->latest()
            ->paginate(30);

        $stats = [
            'total'   => ListingReview::count(),
            'average' => round(ListingReview::avg('rating') ?? 0, 1),
            'five_star' => ListingReview::where('rating', 5)->count(),
        ];

        return view('admin.reviews', compact('reviews', 'stats'));
    }

    public function destroy(ListingReview $review)
    {
        $review->delete();
        return back()->with('success', 'Review deleted.');
    }
}
