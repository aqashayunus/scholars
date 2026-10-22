<?php

namespace App\Services\Billplz;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BillplzService
{
    const SANDBOX_BASE_URL = 'https://www.billplz-sandbox.com/api/v3/';
    const LIVE_BASE_URL    = 'https://www.billplz.com/api/v3/';

    /**
     * Get API Key (Dynamic DB Setting has priority, falls back to config).
     */
    public function getApiKey(): ?string
    {
        return Setting::get('billplz_api_key', config('services.billplz.api_key'));
    }

    /**
     * Get X-Signature Key.
     */
    public function getXSignatureKey(): ?string
    {
        return Setting::get('billplz_x_signature_key', config('services.billplz.x_signature_key'));
    }

    /**
     * Get Collection ID.
     */
    public function getCollectionId(): ?string
    {
        return Setting::get('billplz_collection_id', config('services.billplz.collection_id'));
    }

    /**
     * Check if sandbox mode is active.
     */
    public function isSandbox(): bool
    {
        $dbSandbox = Setting::get('billplz_sandbox');
        if ($dbSandbox !== null) {
            return filter_var($dbSandbox, FILTER_VALIDATE_BOOLEAN);
        }

        return (bool) config('services.billplz.sandbox', true);
    }

    /**
     * Get Billplz base API URL.
     */
    public function getBaseUrl(): string
    {
        return $this->isSandbox() ? self::SANDBOX_BASE_URL : self::LIVE_BASE_URL;
    }

    /**
     * Create a Bill via Billplz API v3.
     *
     * @param  array $params {
     *     collection_id: string (optional, defaults to configured collection)
     *     email:         string
     *     mobile:        string (optional)
     *     name:          string
     *     amount:        float (in Ringgit Malaysia, e.g. 150.00)
     *     callback_url:  string (webhook URL)
     *     redirect_url:  string (return URL)
     *     description:   string
     *     reference_1:   string (optional)
     *     reference_2:   string (optional)
     * }
     * @return array Returned bill data from Billplz
     */
    public function createBill(array $params): array
    {
        $apiKey = $this->getApiKey();
        if (empty($apiKey)) {
            throw new \RuntimeException('Billplz API Key is not configured. Please configure it in Admin Settings > Finance.');
        }

        $collectionId = $params['collection_id'] ?? $this->getCollectionId();
        if (empty($collectionId)) {
            throw new \RuntimeException('Billplz Collection ID is not configured. Please configure it in Admin Settings > Finance.');
        }

        // Convert RM to cents (Billplz requires amount in cents integer)
        $amountInCents = (int) round(((float) $params['amount']) * 100);

        $payload = [
            'collection_id'      => $collectionId,
            'email'              => $params['email'],
            'mobile'             => $params['mobile'] ?? null,
            'name'               => $params['name'],
            'amount'             => $amountInCents,
            'callback_url'       => $params['callback_url'],
            'redirect_url'       => $params['redirect_url'] ?? null,
            'description'        => substr($params['description'] ?? 'Tuition Fee Payment', 0, 200),
            'reference_1_label'  => !empty($params['reference_1']) ? 'Invoice Ref' : null,
            'reference_1'        => $params['reference_1'] ?? null,
            'reference_2_label'  => !empty($params['reference_2']) ? 'Payment Ref' : null,
            'reference_2'        => $params['reference_2'] ?? null,
        ];

        // Filter out null values
        $payload = array_filter($payload, fn($val) => $val !== null);

        $url = rtrim($this->getBaseUrl(), '/') . '/bills';

        try {
            $response = Http::withBasicAuth($apiKey, '')
                ->timeout(20)
                ->asForm()
                ->post($url, $payload);

            if (!$response->successful()) {
                Log::error('[BillplzService] createBill failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                    'payload'=> $payload,
                ]);
                throw new \RuntimeException('Billplz API Error (' . $response->status() . '): ' . $response->body());
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::error('[BillplzService] Exception in createBill: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get Bill details by Bill ID.
     */
    public function getBill(string $billId): array
    {
        $apiKey = $this->getApiKey();
        if (empty($apiKey)) {
            throw new \RuntimeException('Billplz API Key is missing.');
        }

        $url = rtrim($this->getBaseUrl(), '/') . '/bills/' . urlencode($billId);

        $response = Http::withBasicAuth($apiKey, '')
            ->timeout(15)
            ->get($url);

        if (!$response->successful()) {
            throw new \RuntimeException('Failed to fetch bill from Billplz: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Test connection to Billplz API with provided or configured credentials.
     */
    public function testConnection(?string $apiKey = null, ?string $collectionId = null, ?bool $isSandbox = null): array
    {
        $key        = $apiKey ?? $this->getApiKey();
        $collection = $collectionId ?? $this->getCollectionId();
        $sandbox    = $isSandbox ?? $this->isSandbox();
        $baseUrl    = $sandbox ? self::SANDBOX_BASE_URL : self::LIVE_BASE_URL;

        if (empty($key)) {
            return ['success' => false, 'message' => 'API Key is empty.'];
        }

        try {
            // Verify Collection if provided
            if (!empty($collection)) {
                $url = rtrim($baseUrl, '/') . '/collections/' . urlencode($collection);
                $resp = Http::withBasicAuth($key, '')->timeout(10)->get($url);

                if ($resp->successful()) {
                    $data = $resp->json();
                    return [
                        'success'         => true,
                        'message'         => 'Connected successfully to Billplz! Collection: ' . ($data['title'] ?? $collection),
                        'collection_title'=> $data['title'] ?? null,
                        'collection_id'   => $collection,
                        'mode'            => $sandbox ? 'Sandbox' : 'Production',
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'Billplz API replied: ' . $resp->body(),
                    'status'  => $resp->status(),
                ];
            }

            // If no collection ID, test general API connectivity
            $url = rtrim($baseUrl, '/') . '/collections';
            $resp = Http::withBasicAuth($key, '')->timeout(10)->get($url, ['page' => 1]);

            if ($resp->successful()) {
                return [
                    'success' => true,
                    'message' => 'API Key authenticated successfully with Billplz (' . ($sandbox ? 'Sandbox' : 'Production') . ').',
                    'mode'    => $sandbox ? 'Sandbox' : 'Production',
                ];
            }

            return [
                'success' => false,
                'message' => 'Authentication failed: ' . $resp->body(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify Billplz X-Signature for Webhook or Return Redirect.
     *
     * Billplz signs the request with HMAC SHA256 using the X-Signature Key.
     */
    public function verifySignature(array $data, ?string $signature = null): bool
    {
        $xSignatureKey = $this->getXSignatureKey();
        if (empty($xSignatureKey)) {
            Log::warning('[BillplzService] X-Signature key not configured; skipping signature verification.');
            return true; // If admin hasn't configured X-Signature yet, allow fallback
        }

        $sig = $signature ?? ($data['x_signature'] ?? null);
        if (empty($sig)) {
            return false;
        }

        // Exclude x_signature from the payload
        unset($data['x_signature']);

        // Handle redirect format if data is wrapped in billplz[...]
        if (isset($data['billplz']) && is_array($data['billplz'])) {
            $data = $data['billplz'];
            unset($data['x_signature']);
        }

        // Sort keys alphabetically
        ksort($data);

        // Build source string: key + value concatenation
        $source = '';
        foreach ($data as $k => $v) {
            $source .= $k . $v;
        }

        $expected = hash_hmac('sha256', $source, $xSignatureKey);

        return hash_equals($expected, $sig);
    }

    /**
     * Generate Billplz X-Signature HMAC SHA256 string for verification or testing.
     */
    public function generateSignature(array $data, ?string $key = null): string
    {
        $xSignatureKey = $key ?? $this->getXSignatureKey();

        unset($data['x_signature']);
        if (isset($data['billplz']) && is_array($data['billplz'])) {
            $data = $data['billplz'];
            unset($data['x_signature']);
        }

        ksort($data);

        $source = '';
        foreach ($data as $k => $v) {
            $source .= $k . $v;
        }

        return hash_hmac('sha256', $source, (string) $xSignatureKey);
    }
}
