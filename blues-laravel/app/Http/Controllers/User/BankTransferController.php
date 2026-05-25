<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\{BankTransferPayment, Listing, Notification, Purchase, Setting, Wallet, WalletTransaction};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BankTransferController extends Controller
{
    // Initiate bank transfer for marketplace purchase
    public function marketplace(Request $request, int $listingId)
    {
        $listing = Listing::where('is_active', true)->where('stock', '>', 0)->findOrFail($listingId);
        $user    = Auth::user();

        $reference = 'BT-MKT-' . strtoupper(Str::random(8));

        $purchase = Purchase::create([
            'user_id'    => $user->id,
            'listing_id' => $listing->id,
            'amount'     => $listing->price,
            'status'     => 'pending',
        ]);

        $btp = BankTransferPayment::create([
            'user_id'     => $user->id,
            'type'        => 'marketplace',
            'listing_id'  => $listing->id,
            'purchase_id' => $purchase->id,
            'amount'      => $listing->price,
            'reference'   => $reference,
            'status'      => 'pending',
        ]);

        $this->notifyAdmin($user, $btp, $listing->title);

        return redirect()->route('dashboard.bank-transfer.pending', $btp->id)
            ->with('success', 'Bank transfer request submitted. Send the payment and click "I Have Paid".');
    }

    // Initiate bank transfer for wallet top-up
    public function walletTopup(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:' . Setting::get('min_deposit', 500)]);

        $user      = Auth::user();
        $reference = 'BT-WAL-' . strtoupper(Str::random(8));

        $btp = BankTransferPayment::create([
            'user_id'   => $user->id,
            'type'      => 'wallet_topup',
            'amount'    => $request->amount,
            'reference' => $reference,
            'status'    => 'pending',
        ]);

        $this->notifyAdmin($user, $btp, 'Wallet Top-up');

        return redirect()->route('dashboard.bank-transfer.pending', $btp->id)
            ->with('success', 'Bank transfer request submitted. Send ₦' . number_format($request->amount, 2) . ' and click "I Have Paid".');
    }

    // Show the pending transfer page (with bank details + "I have paid" button)
    public function pending(int $id)
    {
        $btp = BankTransferPayment::where('user_id', Auth::id())->findOrFail($id);
        return view('dashboard.bank-transfer-pending', compact('btp'));
    }

    // User clicks "I have paid"
    public function markPaid(Request $request, int $id)
    {
        $btp = BankTransferPayment::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($id);

        // Already notified on creation; send a follow-up "I have paid" email
        $this->notifyAdminPaid(Auth::user(), $btp);

        return back()->with('success', 'Payment confirmation sent! Admin will verify and credit your account shortly.');
    }

    private function notifyAdmin($user, BankTransferPayment $btp, string $itemName): void
    {
        $supportEmail = Setting::get('support_email', '');
        $siteName     = Setting::get('site_name', 'Blues Marketplace');

        try {
            if ($supportEmail && Setting::get('mail_host', '') !== '' && Setting::get('mail_mailer', 'log') !== 'log') {
                $subject = "[{$siteName}] New Bank Transfer Request — {$btp->reference}";
                $body    = "A new bank transfer request has been submitted.\n\n"
                    . "User: {$user->name} ({$user->email})\n"
                    . "Item: {$itemName}\n"
                    . "Amount: ₦" . number_format($btp->amount, 2) . "\n"
                    . "Reference: {$btp->reference}\n"
                    . "Type: " . ucfirst(str_replace('_', ' ', $btp->type)) . "\n\n"
                    . "Log in to the admin panel to confirm or reject this payment.";

                \Illuminate\Support\Facades\Mail::raw($body, function ($msg) use ($supportEmail, $subject, $siteName) {
                    $msg->to($supportEmail)->subject($subject);
                });
            }
        } catch (\Throwable) {}
    }

    private function notifyAdminPaid($user, BankTransferPayment $btp): void
    {
        $supportEmail = Setting::get('support_email', '');
        $siteName     = Setting::get('site_name', 'Blues Marketplace');

        try {
            if ($supportEmail && Setting::get('mail_host', '') !== '' && Setting::get('mail_mailer', 'log') !== 'log') {
                $subject = "[{$siteName}] User Confirms Payment — {$btp->reference}";
                $body    = "{$user->name} ({$user->email}) has clicked 'I Have Paid' for reference {$btp->reference}.\n\n"
                    . "Amount: ₦" . number_format($btp->amount, 2) . "\n"
                    . "Please verify the transfer in your bank and confirm in the admin panel.";

                \Illuminate\Support\Facades\Mail::raw($body, function ($msg) use ($supportEmail, $subject) {
                    $msg->to($supportEmail)->subject($subject);
                });
            }
        } catch (\Throwable) {}
    }
}
