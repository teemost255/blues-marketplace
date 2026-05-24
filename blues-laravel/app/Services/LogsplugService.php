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
        $this->baseUrl = rtrim(Setting::get('logsplug_api_url', 'https://v2.api.logsplug.com/api/v2'), '/');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->baseUrl);
    }

    private function get(string $endpoint, array $params = []): array
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders(['x-api-key' => $this->apiKey])
                ->get($this->baseUrl . $endpoint, $params);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            return ['success' => false, 'message' => $response->json('message') ?? 'Request failed (' . $response->status() . ')'];
        } catch (\Exception $e) {
            Log::error('LogsplugService GET error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Service unavailable. Please try again.'];
        }
    }

    private function post(string $endpoint, array $data = []): array
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders(['x-api-key' => $this->apiKey])
                ->post($this->baseUrl . $endpoint, $data);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            return ['success' => false, 'message' => $response->json('message') ?? 'Request failed (' . $response->status() . ')'];
        } catch (\Exception $e) {
            Log::error('LogsplugService POST error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Service unavailable. Please try again.'];
        }
    }

    public function getBalance(): array
    {
        return $this->get('/third-party/wallet/balance');
    }

    public function getServers(): array
    {
        return $this->get('/third-party/numbers/servers');
    }

    public function getCountries(string $server = 'server2'): array
    {
        return $this->get('/third-party/numbers/countries', ['server' => $server]);
    }

    public function getServices(string $server = 'server2', ?string $country = null): array
    {
        $params = ['server' => $server];
        if ($country !== null) {
            $params['country'] = $country;
        }
        return $this->get('/third-party/numbers/services', $params);
    }

    public function rentNumber(string $server, string $serviceId, string $country): array
    {
        return $this->post('/third-party/numbers/rent', [
            'server'    => $server,
            'serviceId' => $serviceId,
            'country'   => $country,
        ]);
    }

    public function getOtp(string $rentId): array
    {
        return $this->get('/third-party/numbers/rent/' . $rentId . '/otp');
    }

    public function cancelRental(string $rentId): array
    {
        return $this->post('/third-party/numbers/rent/' . $rentId . '/cancel');
    }
}
