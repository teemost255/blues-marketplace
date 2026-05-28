<?php
namespace App\Http\Controllers;

use App\Models\{Listing, ListingCategory, ListingCredential, Purchase, Wallet, WalletTransaction, Wishlist, Notification};
use App\Services\ReferralService;
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
                $q->where('title', 'like', '%'.$request->search.'%')
                  ->orWhere('description', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->max_price);
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

        $listingCategory = $listing->category
            ? ListingCategory::where('slug', $listing->category)->first()
            : null;

        $reviews      = \App\Models\ListingReview::with('user')->where('listing_id', $id)->latest()->get();
        $avgRating    = $reviews->avg('rating') ? round($reviews->avg('rating'), 1) : null;
        $userReviewedPurchaseId = null;
        if (Auth::check()) {
            $bought = Purchase::where('user_id', Auth::id())
                ->where('listing_id', $id)
                ->where('status', 'completed')
                ->whereDoesntHave('review')
                ->first();
            $userReviewedPurchaseId = $bought?->id;
        }

        return view('marketplace.show', compact('listing', 'related', 'inWishlist', 'listingCategory', 'reviews', 'avgRating', 'userReviewedPurchaseId'));
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
        // Pick one credential from the pool (credential system), or fall back to login_details
        $credential    = null;
        $deliveryData  = null;

        if ($listing->usesCredentialSystem()) {
            $credential = $listing->availableCredentials()->lockForUpdate()->first();
            if (!$credential) {
                return back()->with('error', 'This listing just sold out. Please check back later.');
            }
            $deliveryData = $credential->details;
        } else {
            $deliveryData = $listing->login_details ?: null;
            $listing->decrement('stock');
        }

        $purchase = Purchase::create([
            'user_id'       => $user->id,
            'listing_id'    => $listing->id,
            'amount'        => $listing->price,
            'status'        => 'completed',
            'delivery_data' => $deliveryData,
        ]);

        // Mark credential as used and sync stock
        if ($credential) {
            $credential->update([
                'is_used'     => true,
                'used_at'     => now(),
                'purchase_id' => $purchase->id,
            ]);
            $listing->syncStock();
        }

        WalletTransaction::create([
            'user_id'     => $user->id,
            'amount'      => -$listing->price,
            'type'        => 'purchase',
            'reference'   => 'PUR-'.$purchase->id,
            'description' => 'Purchase: '.$listing->title,
        ]);

        $hasDetails = !empty($listing->login_details);
        Notification::create([
            'user_id' => $user->id,
            'title'   => 'Purchase Successful',
            'message' => 'Your purchase of "'.$listing->title.'" was successful. '.
                         ($hasDetails ? 'Your login details are ready — check My Orders.' : 'Check your orders for delivery details.'),
            'type'    => 'success',
        ]);

        if ($user->email_notifications) {
            try {
                $siteName    = \App\Models\Setting::get('site_name', 'Blues Marketplace');
                $fromAddress = \App\Models\Setting::get('mail_from_address', config('mail.from.address'));
                $fromName    = \App\Models\Setting::get('mail_from_name', $siteName);

                if (\App\Models\Setting::get('mail_host', '') !== '' && \App\Models\Setting::get('mail_mailer', 'log') !== 'log') {
                    $html = view('emails.purchase-confirmation', [
                        'user'        => $user,
                        'listing'     => $listing,
                        'purchase'    => $purchase,
                        'hasDetails'  => $hasDetails,
                        'siteName'    => $siteName,
                    ])->render();

                    \Illuminate\Support\Facades\Mail::html($html, function ($msg) use ($user, $siteName, $fromAddress, $fromName) {
                        $msg->to($user->email, $user->name)
                            ->from($fromAddress, $fromName)
                            ->subject("[{$siteName}] Purchase Confirmed — Check Your Orders");
                    });
                }
            } catch (\Throwable) {}
        }

        $lowBalanceThreshold = (float) \App\Models\Setting::get('low_balance_threshold', '5');
        $newBalance = (float) Wallet::where('user_id', $user->id)->value('balance');
        if ($lowBalanceThreshold > 0 && $newBalance < $lowBalanceThreshold) {
            Notification::create([
                'user_id' => $user->id,
                'title'   => 'Low Wallet Balance',
                'message' => 'Your wallet balance is ₦' . number_format($newBalance, 2) . '. Top up to keep shopping.',
                'type'    => 'warning',
            ]);
        }

        // Mark referral as purchased and check if bonus should be awarded
        ReferralService::markPurchased($user->fresh());

        return redirect()->route('dashboard.orders')->with('success',
            $hasDetails
                ? 'Purchase successful! Your login details are shown below.'
                : 'Purchase successful! Check your orders for details.'
        );
    }
}
