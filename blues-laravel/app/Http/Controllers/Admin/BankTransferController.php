<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{BankTransferPayment, Notification, Purchase, Wallet, WalletTransaction};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankTransferController extends Controller
{
    public function index()
    {
        $pending   = BankTransferPayment::with('user','listing')->where('status','pending')->latest()->get();
        $processed = BankTransferPayment::with('user','listing')->whereIn('status',['confirmed','rejected'])->latest()->limit(50)->get();
        return view('admin.bank-transfers', compact('pending','processed'));
    }

    public function confirm(int $id)
    {
        $btp = BankTransferPayment::where('status','pending')->findOrFail($id);

        DB::transaction(function () use ($btp) {
            $btp->update(['status' => 'confirmed', 'confirmed_at' => now()]);

            if ($btp->type === 'marketplace' && $btp->purchase_id) {
                $purchase = Purchase::findOrFail($btp->purchase_id);
                $listing  = $purchase->listing;

                $purchase->update([
                    'status'        => 'completed',
                    'delivery_data' => $listing?->login_details ?: null,
                ]);

                if ($listing) $listing->decrement('stock');

                WalletTransaction::create([
                    'user_id'     => $btp->user_id,
                    'amount'      => -$btp->amount,
                    'type'        => 'purchase',
                    'reference'   => $btp->reference,
                    'description' => 'Bank Transfer Purchase: ' . ($listing?->title ?? 'Listing'),
                ]);

                $hasDetails = !empty($listing?->login_details);
                Notification::create([
                    'user_id' => $btp->user_id,
                    'title'   => 'Payment Confirmed — Purchase Complete',
                    'message' => 'Your bank transfer for "' . ($listing?->title ?? 'your order') . '" has been confirmed. '
                        . ($hasDetails ? 'Login details are ready — check My Orders.' : 'Check your orders for details.'),
                    'type'    => 'success',
                ]);

            } elseif ($btp->type === 'wallet_topup') {
                $wallet = Wallet::firstOrCreate(['user_id' => $btp->user_id], ['balance' => 0]);
                $wallet->increment('balance', $btp->amount);

                WalletTransaction::create([
                    'user_id'     => $btp->user_id,
                    'amount'      => $btp->amount,
                    'type'        => 'deposit',
                    'reference'   => $btp->reference,
                    'description' => 'Bank Transfer Deposit',
                ]);

                Notification::create([
                    'user_id' => $btp->user_id,
                    'title'   => 'Bank Transfer Confirmed',
                    'message' => '₦' . number_format($btp->amount, 2) . ' has been credited to your wallet via bank transfer.',
                    'type'    => 'success',
                ]);
            }
        });

        return back()->with('success', 'Payment confirmed successfully and user notified.');
    }

    public function reject(Request $request, int $id)
    {
        $btp = BankTransferPayment::where('status','pending')->findOrFail($id);

        $btp->update(['status' => 'rejected', 'admin_note' => $request->input('note', '')]);

        // If marketplace, cancel the pending purchase
        if ($btp->purchase_id) {
            Purchase::where('id', $btp->purchase_id)->where('status','pending')->update(['status' => 'refunded']);
        }

        Notification::create([
            'user_id' => $btp->user_id,
            'title'   => 'Bank Transfer Rejected',
            'message' => 'Your bank transfer reference ' . $btp->reference . ' could not be verified.'
                . ($btp->admin_note ? ' Note: ' . $btp->admin_note : '') . ' Please contact support.',
            'type'    => 'error',
        ]);

        return back()->with('success', 'Payment rejected and user notified.');
    }
}
