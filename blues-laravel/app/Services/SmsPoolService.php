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
        $this->apiKey = trim(Setting::get('smspool_api_key', ''));
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    private function headers(): array
    {
        return [
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer ' . $this->apiKey,
        ];
    }

    private function get(string $endpoint, array $params = []): array
    {
        // Send key both as query param and as Bearer header for maximum compatibility
        $params['key'] = $this->apiKey;

        try {
            $response = Http::timeout(20)
                ->withHeaders($this->headers())
                ->get($this->baseUrl . $endpoint, $params);

            return $this->parseResponse($response, 'GET ' . $endpoint);
        } catch (\Exception $e) {
            Log::error('SmsPoolService GET error [' . $endpoint . ']: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not reach SMSPool. Please check your connection and try again.'];
        }
    }

    private function post(string $endpoint, array $data = []): array
    {
        // Send key in body AND as Bearer header
        $data['key'] = $this->apiKey;

        try {
            $response = Http::timeout(20)
                ->withHeaders($this->headers())
                ->asForm()
                ->post($this->baseUrl . $endpoint, $data);

            return $this->parseResponse($response, 'POST ' . $endpoint);
        } catch (\Exception $e) {
            Log::error('SmsPoolService POST error [' . $endpoint . ']: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not reach SMSPool. Please check your connection and try again.'];
        }
    }

    private function parseResponse($response, string $context): array
    {
        $status = $response->status();
        $body   = $response->body();

        Log::debug('SmsPool [' . $context . '] HTTP ' . $status . ': ' . substr($body, 0, 300));

        if ($response->successful()) {
            $json = $response->json();

            if (!is_array($json)) {
                return ['success' => false, 'message' => 'Unexpected response from SMSPool.'];
            }

            // SMSPool error formats: {error: "..."} or {success: 0, message: "..."}
            if (isset($json['error']) && $json['error']) {
                $msg = is_string($json['error']) ? $json['error'] : ($json['message'] ?? 'SMSPool error.');
                return ['success' => false, 'message' => $msg];
            }
            if (isset($json['success']) && $json['success'] === 0) {
                return ['success' => false, 'message' => $json['message'] ?? 'SMSPool request failed.'];
            }

            return ['success' => true, 'data' => $json];
        }

        if ($status === 401 || $status === 403) {
            Log::warning('SmsPool auth error [' . $context . ']: ' . $body);
            return ['success' => false, 'message' => 'SMSPool authentication failed. Please check your API key in Settings.'];
        }

        if ($status >= 500) {
            return ['success' => false, 'message' => 'SMSPool is temporarily unavailable. Please try again shortly.'];
        }

        return ['success' => false, 'message' => 'SMSPool request failed (HTTP ' . $status . '): ' . substr($body, 0, 100)];
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
            'country' => $country,
            'service' => $service,
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
