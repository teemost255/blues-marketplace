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

    private function client()
    {
        return Http::withOptions([
            'curl' => [CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1],
        ])->withHeaders([
            'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Accept'          => 'application/json, text/plain, */*',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Referer'         => 'https://www.smspool.net/',
        ])->timeout(20);
    }

    private function get(string $endpoint, array $params = []): array
    {
        $params['key'] = $this->apiKey;
        try {
            $response = $this->client()->get($this->baseUrl . $endpoint, $params);
            return $this->parseResponse($response, 'GET ' . $endpoint);
        } catch (\Exception $e) {
            Log::error('SmsPoolService GET [' . $endpoint . ']: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not reach SMSPool. Check your connection.'];
        }
    }

    private function post(string $endpoint, array $data = []): array
    {
        $data['key'] = $this->apiKey;
        try {
            $response = $this->client()->asForm()->post($this->baseUrl . $endpoint, $data);
            return $this->parseResponse($response, 'POST ' . $endpoint);
        } catch (\Exception $e) {
            Log::error('SmsPoolService POST [' . $endpoint . ']: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not reach SMSPool. Check your connection.'];
        }
    }

    private function parseResponse($response, string $context): array
    {
        $status = $response->status();
        $body   = $response->body();

        Log::info('SmsPool [' . $context . '] HTTP ' . $status . ' | ' . substr($body, 0, 300));

        if ($response->successful()) {
            // Balance endpoint returns a plain number string
            if (is_numeric(trim($body))) {
                return ['success' => true, 'data' => ['balance' => (float) trim($body)]];
            }

            $json = $response->json();

            if (!is_array($json)) {
                return ['success' => false, 'message' => 'Unexpected SMSPool response: ' . substr($body, 0, 100)];
            }

            // {success: 0, errors: [...], message: "..."}
            if (isset($json['success']) && $json['success'] === 0) {
                $msg = $json['message'] ?? '';
                if (!$msg && isset($json['errors']) && is_array($json['errors'])) {
                    $msg = implode(' ', array_column($json['errors'], 'message'));
                }
                return ['success' => false, 'message' => strip_tags($msg ?: 'SMSPool request failed.')];
            }

            // {error: "..."}
            if (isset($json['error']) && $json['error']) {
                return ['success' => false, 'message' => is_string($json['error']) ? $json['error'] : 'SMSPool error.'];
            }

            return ['success' => true, 'data' => $json];
        }

        Log::warning('SmsPool [' . $context . '] HTTP ' . $status . ' body: ' . $body);

        if ($status === 401 || $status === 403) {
            return ['success' => false, 'message' => 'SMSPool API key rejected (HTTP ' . $status . '). Please verify your key in Settings.'];
        }
        if ($status >= 500) {
            return ['success' => false, 'message' => 'SMSPool is temporarily unavailable. Try again shortly.'];
        }

        return ['success' => false, 'message' => 'SMSPool request failed (HTTP ' . $status . ').'];
    }

    // ── Public methods ─────────────────────────────────────────────────────────

    public function getBalance(): array
    {
        $result = $this->get('/request/balance');
        if ($result['success'] && isset($result['data']['balance'])) {
            return $result;
        }
        // Balance returns {"balance":"0.00"} JSON
        return $result;
    }

    public function getCountries(): array
    {
        return $this->get('/country/retrieve_all');
    }

    public function getServices(?string $country = null): array
    {
        // SMSPool services are global; country filter is not supported on this endpoint
        return $this->get('/service/retrieve_all');
    }

    public function orderNumber(string $country, string $service): array
    {
        // pool omitted → SMSPool auto-selects the best available pool
        return $this->post('/purchase/sms', [
            'country' => $country,
            'service' => $service,
        ]);
    }

    public function checkSms(string $orderId): array
    {
        return $this->get('/sms/check', ['orderid' => $orderId]);
    }

    public function cancelOrder(string $orderId): array
    {
        return $this->get('/sms/cancel', ['orderid' => $orderId]);
    }
}
