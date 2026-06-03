<?php
namespace App\Console\Commands;

use App\Models\{VirtualNumberOrder, Notification};
use App\Services\HeroSmsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PollVirtualNumbers extends Command
{
    protected $signature   = 'vn:poll';
    protected $description = 'Poll HeroSMS API for SMS codes on all waiting virtual number orders';

    public function handle(): void
    {
        $orders = VirtualNumberOrder::where('status', 'waiting')
            ->whereNotNull('activation_id')
            ->get();

        if ($orders->isEmpty()) return;

        $sms     = new HeroSmsService();
        $updated = 0;

        foreach ($orders as $order) {
            try {
                $result = $sms->getStatus((string) $order->activation_id);

                if ($result['status'] === 'received' && !empty($result['code'])) {
                    $order->update(['status' => 'received', 'sms_code' => $result['code']]);

                    try {
                        Notification::create([
                            'user_id' => $order->user_id,
                            'title'   => 'SMS Code Received',
                            'message' => "Your {$order->service_name} verification code: {$result['code']}",
                            'type'    => 'success',
                        ]);
                    } catch (\Throwable) {}

                    Log::info('vn:poll — code received', [
                        'order_id'      => $order->id,
                        'activation_id' => $order->activation_id,
                        'code'          => $result['code'],
                    ]);
                    $updated++;
                } elseif ($result['status'] === 'cancelled') {
                    $order->update(['status' => 'cancelled']);
                    Log::info('vn:poll — order cancelled by API', ['order_id' => $order->id]);
                }
            } catch (\Throwable $e) {
                Log::error('vn:poll — error polling order', [
                    'order_id' => $order->id,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        if ($updated) {
            $this->info("vn:poll: {$updated} code(s) received.");
        }
    }
}
