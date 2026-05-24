<?php
namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsPoolService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.smspool.net';

    public function __construct()
    {
        $this->apiKey = Setting::get('smspool_api_key', '');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    private function get(string $endpoint, array $params = []): array
    {
        try {
            $params['key'] = $this->apiKey;
            $response = Http::timeout(15)->get($this->baseUrl . $endpoint, $params);

            if ($response->successful()) {
                $body = $response->json();
                if (is_array($body) && isset($body['error'])) {
                    return ['success' => false, 'message' => $body['error']];
                }
                return ['success' => true, 'data' => $body];
            }

            $status = $response->status();
            if ($status >= 500) {
                return ['success' => false, 'message' => 'SMSPool is temporarily unavailable (HTTP ' . $status . '). Please try again shortly.'];
            }
            return ['success' => false, 'message' => 'Request failed (' . $status . ')'];
        } catch (\Exception $e) {
            Log::error('SmsPoolService GET error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not reach SMSPool. Please check your connection and try again.'];
        }
    }

    private function post(string $endpoint, array $data = []): array
    {
        try {
            $data['key'] = $this->apiKey;
            $response = Http::timeout(15)->asForm()->post($this->baseUrl . $endpoint, $data);

            if ($response->successful()) {
                $body = $response->json();
                if (is_array($body) && isset($body['error'])) {
                    return ['success' => false, 'message' => $body['error']];
                }
                return ['success' => true, 'data' => $body];
            }

            $status = $response->status();
            if ($status >= 500) {
                return ['success' => false, 'message' => 'SMSPool is temporarily unavailable (HTTP ' . $status . '). Please try again shortly.'];
            }
            return ['success' => false, 'message' => 'Request failed (' . $status . ')'];
        } catch (\Exception $e) {
            Log::error('SmsPoolService POST error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not reach SMSPool. Please check your connection and try again.'];
        }
    }

    public function getBalance(): array
    {
        return $this->get('/request/balance');
    }

    public function getCountries(): array
    {
        return $this->get('/country');
    }

    public function getServices(?string $country = null): array
    {
        $params = [];
        if ($country !== null && $country !== '') {
            $params['country_id'] = $country;
        }
        return $this->get('/service', $params);
    }

    public function orderNumber(string $country, string $service): array
    {
        return $this->post('/order/sms', [
            'country'  => $country,
            'service'  => $service,
        ]);
    }

    public function checkSms(string $orderId): array
    {
        return $this->get('/pool/' . $orderId);
    }

    public function cancelOrder(string $orderId): array
    {
        return $this->post('/order/cancel', ['orderid' => $orderId]);
    }
}
