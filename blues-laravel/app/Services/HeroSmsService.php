<?php
namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\{Http, Log};

class HeroSmsService
{
    private string $apiKey;
    private string $baseUrl = 'https://hero-sms.com/stubs/handler_api.php';

    public function __construct()
    {
        $this->apiKey = Setting::get('herosms_api_key', '');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    public function getBalance(): float
    {
        $raw = $this->call(['action' => 'getBalance']);
        if (str_starts_with($raw, 'ACCESS_BALANCE:')) {
            return (float) substr($raw, strlen('ACCESS_BALANCE:'));
        }
        return 0.0;
    }

    public function getCountries(): array
    {
        try {
            $response = Http::timeout(15)->get($this->baseUrl, [
                'api_key' => $this->apiKey,
                'action'  => 'getCountries',
            ]);
            $data = $response->json();
            if (!is_array($data)) return [];
            return $data;
        } catch (\Exception $e) {
            Log::error('HeroSMS getCountries error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function getServicesForCountry(int $country): array
    {
        try {
            $response = Http::timeout(15)->get($this->baseUrl, [
                'api_key' => $this->apiKey,
                'action'  => 'getNumbersStatus',
                'country' => $country,
            ]);
            $data = $response->json();
            if (!is_array($data)) return [];
            return $data;
        } catch (\Exception $e) {
            Log::error('HeroSMS getNumbersStatus error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function getNumber(string $service, int $country): array
    {
        $raw = $this->call([
            'action'  => 'getNumber',
            'service' => $service,
            'country' => $country,
        ]);

        if (str_starts_with($raw, 'ACCESS_NUMBER:')) {
            $parts = explode(':', $raw);
            return [
                'success'       => true,
                'activation_id' => $parts[1] ?? null,
                'phone_number'  => $parts[2] ?? null,
            ];
        }

        return ['success' => false, 'error' => $raw];
    }

    public function getStatus(string $activationId): array
    {
        $raw = $this->call([
            'action' => 'getStatus',
            'id'     => $activationId,
        ]);

        if (str_starts_with($raw, 'STATUS_OK:')) {
            return ['status' => 'received', 'code' => substr($raw, strlen('STATUS_OK:'))];
        }
        if ($raw === 'STATUS_WAIT_CODE') {
            return ['status' => 'waiting', 'code' => null];
        }
        if ($raw === 'STATUS_CANCEL') {
            return ['status' => 'cancelled', 'code' => null];
        }
        return ['status' => 'unknown', 'code' => null, 'raw' => $raw];
    }

    public function setStatusReady(string $activationId): bool
    {
        $raw = $this->call(['action' => 'setStatus', 'id' => $activationId, 'status' => 1]);
        return $raw === 'ACCESS_READY';
    }

    public function setStatusComplete(string $activationId): bool
    {
        $raw = $this->call(['action' => 'setStatus', 'id' => $activationId, 'status' => 6]);
        return in_array($raw, ['ACCESS_ACTIVATION', '1']);
    }

    public function setStatusCancel(string $activationId): bool
    {
        $raw = $this->call(['action' => 'setStatus', 'id' => $activationId, 'status' => 8]);
        return in_array($raw, ['ACCESS_CANCEL', '1']);
    }

    private function call(array $params): string
    {
        try {
            $params['api_key'] = $this->apiKey;
            $response = Http::timeout(20)->get($this->baseUrl, $params);
            return trim($response->body());
        } catch (\Exception $e) {
            Log::error('HeroSMS API error', ['params' => $params, 'error' => $e->getMessage()]);
            return 'ERROR';
        }
    }
}
