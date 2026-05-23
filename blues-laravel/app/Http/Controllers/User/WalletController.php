<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\{Wallet, WalletTransaction, Setting};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Http, Log};

class WalletController extends Controller
{
    public function index()
    {
        $user              = Auth::user();
        $wallet            = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
        $transactions      = WalletTransaction::where('user_id', $user->id)->latest()->paginate(20);
        $paystackPublicKey = Setting::get('paystack_public_key', '');
        $minDeposit        = (float) Setting::get('min_deposit', '500');
        $maxDeposit        = (float) Setting::get('max_deposit', '1000000');
        return view('dashboard.wallet', compact('wallet', 'transactions', 'paystackPublicKey', 'minDeposit', 'maxDeposit'));
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

        $secretKey = Setting::get('paystack_secret_key', '');
        if (!$secretKey) {
            return back()->with('error', 'Payment gateway is not configured. Please contact support.');
        }

        $user       = Auth::user();
        $amountKobo = (int) round((float) $request->amount * 100);
        $reference  = 'WALLET-' . strtoupper(uniqid());

        $response = Http::withToken($secretKey)
            ->post('https://api.paystack.co/transaction/initialize', [
                'email'        => $user->email,
                'amount'       => $amountKobo,
                'reference'    => $reference,
                'callback_url' => route('dashboard.wallet.callback'),
                'metadata'     => [
                    'user_id'       => $user->id,
                    'custom_fields' => [[
                        'display_name'  => 'Purpose',
                        'variable_name' => 'purpose',
                        'value'         => 'Wallet Top-up',
                    ]],
                ],
            ]);

        if (!$response->successful() || !$response->json('status')) {
            Log::error('Paystack init error', $response->json() ?? []);
            return back()->with('error', 'Could not initiate payment. Please try again.');
        }

        return redirect($response->json('data.authorization_url'));
    }

    public function callback(Request $request)
    {
        $reference = $request->query('reference') ?? $request->query('trxref');
        if (!$reference) {
            return redirect()->route('dashboard.wallet')->with('error', 'Invalid payment reference.');
        }

        if (WalletTransaction::where('reference', $reference)->exists()) {
            return redirect()->route('dashboard.wallet')->with('info', 'This payment has already been processed.');
        }

        $secretKey = Setting::get('paystack_secret_key', '');
        $response  = Http::withToken($secretKey)
            ->get("https://api.paystack.co/transaction/verify/{$reference}");

        if (!$response->successful()) {
            return redirect()->route('dashboard.wallet')->with('error', 'Payment verification failed.');
        }

        $data   = $response->json('data');
        $status = $data['status'] ?? '';

        if ($status !== 'success') {
            return redirect()->route('dashboard.wallet')
                ->with('error', 'Payment was not completed. Status: ' . $status);
        }

        $user   = Auth::user();
        $amount = $data['amount'] / 100;

        $this->creditWallet($user->id, $amount, $reference, 'Wallet top-up via Paystack');

        return redirect()->route('dashboard.wallet')
            ->with('success', '₦' . number_format($amount, 2) . ' has been added to your wallet!');
    }

    public function webhook(Request $request)
    {
        $secret    = Setting::get('paystack_webhook_secret', '');
        $signature = $request->header('x-paystack-signature');

        if ($secret && $signature !== hash_hmac('sha512', $request->getContent(), $secret)) {
            Log::warning('Paystack webhook signature mismatch');
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
                    $amount = $data['amount'] / 100;
                    $this->creditWallet($userId, $amount, $reference, 'Wallet top-up via Paystack (webhook)');
                }
            }
        }

        return response('OK', 200);
    }

    private function creditWallet(int $userId, float $amount, string $reference, string $description): void
    {
        $wallet = Wallet::firstOrCreate(['user_id' => $userId], ['balance' => 0]);
        $wallet->increment('balance', $amount);

        WalletTransaction::create([
            'user_id'     => $userId,
            'amount'      => $amount,
            'type'        => 'deposit',
            'reference'   => $reference,
            'description' => $description,
        ]);
    }
}
