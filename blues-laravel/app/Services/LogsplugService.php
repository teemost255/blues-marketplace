<?php
namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LogsplugService
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey  = Setting::get('logsplug_api_key', '');
        $this->baseUrl = rtrim(Setting::get('logsplug_api_url', 'https://logsplug.com/api'), '/');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->baseUrl);
    }

    private function get(string $endpoint, array $params = []): array
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders(['Authorization' => 'Bearer ' . $this->apiKey])
                ->get($this->baseUrl . $endpoint, $params);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            return ['success' => false, 'message' => $response->json('message') ?? 'Request failed (' . $response->status() . ')'];
        } catch (\Exception $e) {
            Log::error('LogsplugService error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Service unavailable. Please try again.'];
        }
    }

    private function post(string $endpoint, array $data = []): array
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders(['Authorization' => 'Bearer ' . $this->apiKey])
                ->post($this->baseUrl . $endpoint, $data);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            return ['success' => false, 'message' => $response->json('message') ?? 'Request failed (' . $response->status() . ')'];
        } catch (\Exception $e) {
            Log::error('LogsplugService error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Service unavailable. Please try again.'];
        }
    }

    public function getBalance(): array
    {
        return $this->get('/balance');
    }

    public function getCountries(): array
    {
        return $this->get('/countries');
    }

    public function getServices(?string $country = null): array
    {
        $params = $country ? ['country' => $country] : [];
        return $this->get('/services', $params);
    }

    public function getServicePrice(string $service, string $country = 'ng'): array
    {
        return $this->get('/price', ['service' => $service, 'country' => $country]);
    }

    public function orderNumber(string $service, string $country = 'ng'): array
    {
        return $this->post('/order', ['service' => $service, 'country' => $country]);
    }

    public function getOrderStatus(string $orderId): array
    {
        return $this->get('/order/' . $orderId);
    }

    public function cancelOrder(string $orderId): array
    {
        return $this->post('/order/' . $orderId . '/cancel');
    }

    public function finishOrder(string $orderId): array
    {
        return $this->post('/order/' . $orderId . '/finish');
    }
}
