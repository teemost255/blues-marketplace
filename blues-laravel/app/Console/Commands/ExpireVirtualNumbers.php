<?php
namespace App\Console\Commands;

use App\Models\VirtualNumberOrder;
use Illuminate\Console\Command;

class ExpireVirtualNumbers extends Command
{
    protected $signature   = 'vn:expire';
    protected $description = 'Expire virtual number orders that have passed their expiry time';

    public function handle(): void
    {
        $expired = VirtualNumberOrder::where('status', 'waiting')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expired as $order) {
            $order->update(['status' => 'expired']);
        }

        if ($expired->count()) {
            $this->info("Expired {$expired->count()} virtual number order(s).");
        }
    }
}
