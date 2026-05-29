<?php
namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\VirtualNumberOrder;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\GrizzlySmsService;
use App\Services\HeroSmsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireVirtualNumberOrders extends Command
{
    protected $signature   = 'vn:expire';
    protected $description = 'Cancel active virtual number orders that have exceeded the expiry timeout and refund users.';

    public function handle(): int
    {
        $minutes = (int) Setting::get('vn_auto_expire_minutes', '20');
        if ($minutes <= 0) {
            $this->info('Auto-expiry disabled (timeout = 0).');
            return self::SUCCESS;
        }

        $cutoff = now()->subMinutes($minutes);

        $orders = VirtualNumberOrder::where('status', 'active')
            ->where('created_at', '<=', $cutoff)
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No expired virtual number orders found.');
            return self::SUCCESS;
        }

        $cancelled = 0;
        $failed    = 0;

        foreach ($orders as $order) {
            try {
                $apiSuccess = $this->cancelWithProvider($order);

                if ($apiSuccess) {
                    $order->update(['status' => 'cancelled']);
                    $this->processRefund($order);
                    $cancelled++;
                    $this->line("  ✓ Order #{$order->id} cancelled & refunded (₦{$order->cost})");
                } else {
                    $failed++;
                    $this->warn("  ✗ Order #{$order->id} — provider rejected cancel, skipped.");
                    Log::warning("vn:expire — provider rejected cancel for order #{$order->id}");
                }
            } catch (\Throwable $e) {
                $failed++;
                $this->warn("  ✗ Order #{$order->id} — exception: {$e->getMessage()}");
                Log::error("vn:expire — exception on order #{$order->id}: {$e->getMessage()}");
            }
        }

        $this->info("Done — {$cancelled} cancelled, {$failed} failed.");
        return self::SUCCESS;
    }

    private function cancelWithProvider(VirtualNumberOrder $order): bool
    {
        if (!$order->external_order_id) {
            return true;
        }

        if ($order->provider === 'herosms') {
            $svc    = new HeroSmsService();
            $result = $svc->cancelOrder($order->external_order_id);
        } else {
            $svc    = new GrizzlySmsService();
            $result = $svc->cancelOrder($order->external_order_id);
        }

        return $result['success'] ?? false;
    }

    private function processRefund(VirtualNumberOrder $order): void
    {
        if ($order->cost <= 0) return;

        $wallet = Wallet::where('user_id', $order->user_id)->first();
        if (!$wallet) return;

        $wallet->increment('balance', $order->cost);

        WalletTransaction::create([
            'user_id'     => $order->user_id,
            'type'        => 'refund',
            'amount'      => $order->cost,
            'description' => 'Auto-refund: virtual number #' . $order->id . ' expired after ' . Setting::get('vn_auto_expire_minutes', '20') . ' min',
            'reference'   => 'AUTOREFUND-VN-' . $order->id . '-' . time(),
        ]);
    }
}
