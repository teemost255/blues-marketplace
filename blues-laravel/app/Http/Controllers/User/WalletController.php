<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\{Wallet, WalletTransaction, Setting, Notification};
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Http, Log};

class WalletController extends Controller
{
    public function index(Request $request)
    {
        $user           = Auth::user();
        $wallet         = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
        $koraPublicKey  = Setting::get('kora_public_key', '');
        $minDeposit     = (float) Setting::get('min_deposit', '500');
        $maxDeposit     = (float) Setting::get('max_deposit', '1000000');

        $txQuery = WalletTransaction::where('user_id', $user->id)->latest();
        if ($request->filled('type') && $request->type !== 'all') {
            $txQuery->where('type', $request->type);
        }
        $transactions = $txQuery->paginate(20)->withQueryString();

        $summary = [
            'total_deposited' => WalletTransaction::where('user_id', $user->id)->where('type', 'deposit')->sum('amount'),
            'referral_earned' => WalletTransaction::where('user_id', $user->id)->where('type', 'referral_bonus')->sum('amount'),
        ];

        $activeType = $request->get('type', 'all');

        return view('dashboard.wallet', compact('wallet', 'transactions', 'koraPublicKey', 'minDeposit', 'maxDeposit', 'summary', 'activeType'));
    }

    public function initiate(Request $request)
    {
        $minDeposit = (float) Setting::get('min_deposit', '500');
        $maxDeposit = (float) Setting::get('max_deposit', '1000000');

        $request->validate([
            'amount' => "required|numeric|min:{$minDeposit}|max:{$maxDeposit}",
        ], [
            'amount.min' => 'Minimum deposit is ₦' . number_format($minDeposit, 2),
            'amount.max' => 'Maximum deposit is ₦' . number_format($maxDeposit, 2),
        ]);

        $secretKey = Setting::get('kora_secret_key', '');
        if (!$secretKey) {
            return back()->with('error', 'Payment gateway is not configured. Please contact support.');
        }

        $user      = Auth::user();
        $amount    = (float) $request->amount; // Kora uses Naira, not kobo
        $reference = 'WALLET-' . strtoupper(uniqid());

        $response = Http::withToken($secretKey)
            ->post('https://api.korapay.com/merchant/api/v1/charges/initialize', [
                'reference'    => $reference,
                'amount'       => $amount,
                'currency'     => 'NGN',
                'redirect_url' => route('dashboard.wallet.callback'),
                'customer'     => [
                    'email' => $user->email,
                    'name'  => $user->name,
                ],
                'metadata'     => [
                    'user_id' => $user->id,
                    'purpose' => 'Wallet Top-up',
                ],
            ]);

        if (!$response->successful() || !$response->json('status')) {
            Log::error('Kora init error', $response->json() ?? []);
            return back()->with('error', 'Could not initiate payment. Please try again.');
        }

        $checkoutUrl = $response->json('data.checkout_url');
        if (!$checkoutUrl) {
            Log::error('Kora init: missing checkout_url', $response->json() ?? []);
            return back()->with('error', 'Could not initiate payment. Please try again.');
        }

        return redirect($checkoutUrl);
    }

    public function callback(Request $request)
    {
        $reference = $request->query('reference') ?? $request->query('trxref');
        if (!$reference) {
            return redirect()->route('dashboard.wallet')->with('error', 'Invalid payment reference.');
        }

        $secretKey = Setting::get('kora_secret_key', '');
        if (!$secretKey) {
            return redirect()->route('dashboard.wallet')->with('error', 'Payment gateway not configured.');
        }

        // Verify the transaction with Kora
        $response = Http::withToken($secretKey)
            ->get("https://api.korapay.com/merchant/api/v1/charges/{$reference}");

        if (!$response->successful()) {
            return redirect()->route('dashboard.wallet')->with('error', 'Payment verification failed. Please contact support.');
        }

        $data   = $response->json('data');
        $status = $data['status'] ?? '';
        $amount = isset($data['amount']) ? (float) $data['amount'] : 0; // Kora returns Naira

        if ($status !== 'success') {
            return redirect()->route('dashboard.wallet')
                ->with('error', 'Payment was not completed. Please try again.');
        }

        // If webhook already credited this reference, show success without double-crediting
        if (WalletTransaction::where('reference', $reference)->exists()) {
            return redirect()->route('dashboard.wallet')
                ->with('success', '₦' . number_format($amount, 2) . ' has been added to your wallet!');
        }

        // Resolve user — prefer Auth session, fall back to metadata or email from Kora response
        $user = Auth::user();
        if (!$user) {
            $metaUserId = $data['metadata']['user_id'] ?? null;
            if ($metaUserId) {
                $user = \App\Models\User::find($metaUserId);
            }
            if (!$user) {
                $email = $data['customer']['email'] ?? null;
                $user  = $email ? \App\Models\User::where('email', $email)->first() : null;
            }
        }

        if (!$user) {
            Log::error('Kora callback: could not resolve user', ['reference' => $reference, 'data' => $data]);
            return redirect()->route('login')
                ->with('error', 'Session expired. Your payment was received — please log in and check your wallet, or contact support with reference: ' . $reference);
        }

        $this->creditWallet($user->id, $amount, $reference, 'Wallet top-up via Kora');

        ReferralService::markDeposited($user->fresh());

        return redirect()->route('dashboard.wallet')
            ->with('success', '₦' . number_format($amount, 2) . ' has been added to your wallet!');
    }

    public function webhook(Request $request)
    {
        $encryptionKey = Setting::get('kora_encryption_key', '');
        $signature     = $request->header('x-korapay-signature');

        if ($encryptionKey && $signature !== hash_hmac('sha256', $request->getContent(), $encryptionKey)) {
            Log::warning('Kora webhook signature mismatch');
            return response('Unauthorized', 401);
        }

        $event = $request->json('event');
        $data  = $request->json('data');

        if ($event === 'charge.success' && ($data['status'] ?? '') === 'success') {
            $reference = $data['reference'] ?? null;
            if ($reference && !WalletTransaction::where('reference', $reference)->exists()) {
                $userId = $data['metadata']['user_id'] ?? null;
                if (!$userId) {
                    $email  = $data['customer']['email'] ?? null;
                    $userId = $email ? \App\Models\User::where('email', $email)->value('id') : null;
                }
                if ($userId) {
                    $amount = (float) ($data['amount'] ?? 0); // Kora sends Naira
                    $this->creditWallet($userId, $amount, $reference, 'Wallet top-up via Kora (webhook)');
                    $user = \App\Models\User::find($userId);
                    if ($user) ReferralService::markDeposited($user);
                }
            }
        }

        return response('OK', 200);
    }

    public function creditWallet(int $userId, float $amount, string $reference, string $description): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($userId, $amount, $reference, $description) {
            // Guard against race conditions: check again inside the transaction with a lock
            $alreadyProcessed = WalletTransaction::where('reference', $reference)->lockForUpdate()->exists();
            if ($alreadyProcessed) {
                return;
            }

            $wallet = Wallet::firstOrCreate(['user_id' => $userId], ['balance' => 0]);
            // Lock the wallet row to prevent concurrent balance updates
            Wallet::where('id', $wallet->id)->lockForUpdate()->first();
            $wallet->increment('balance', round($amount, 2));

            WalletTransaction::create([
                'user_id'     => $userId,
                'amount'      => round($amount, 2),
                'type'        => 'deposit',
                'reference'   => $reference,
                'description' => $description,
            ]);

            Notification::create([
                'user_id' => $userId,
                'title'   => 'Wallet Funded',
                'message' => '₦' . number_format($amount, 2) . ' has been added to your wallet successfully.',
                'type'    => 'success',
            ]);
        });
    }
}
