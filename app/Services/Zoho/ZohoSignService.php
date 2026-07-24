<?php

namespace App\Services\Zoho;

use App\Models\HelloSignDetail;
use App\Models\Owner;
use App\Models\ZohoCodeDetail;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ZohoSignService
{
    public function ensureAccessToken(): string
    {
        $detail = ZohoCodeDetail::query()
            ->where('is_deleted', 0)
            ->orderByDesc('id')
            ->first();

        if ($detail === null) {
            throw new RuntimeException('Zoho Sign credentials are not configured in zoho_code_details.');
        }

        $grace = (int) config('services.zoho.refresh_grace_minutes', 50);

        if (! $detail->isExpired($grace) && filled($detail->zoho_access_token)) {
            return (string) $detail->zoho_access_token;
        }

        return $this->refreshAccessToken($detail);
    }

    public function refreshAccessToken(ZohoCodeDetail $detail): string
    {
        $clientId = config('services.zoho.client_id');
        $clientSecret = config('services.zoho.client_secret');

        if (! filled($clientId) || ! filled($clientSecret) || ! filled($detail->zoho_refresh_token)) {
            throw new RuntimeException('Zoho Sign OAuth configuration is incomplete.');
        }

        $response = Http::asForm()
            ->timeout((int) config('services.zoho.timeout', 15))
            ->post(rtrim((string) config('services.zoho.account_url'), '/').'/oauth/v2/token', [
                'refresh_token' => $detail->zoho_refresh_token,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'grant_type' => 'refresh_token',
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Zoho Sign token refresh failed: '.$response->body());
        }

        $accessToken = (string) $response->json('access_token');

        $detail->update([
            'zoho_access_token' => $accessToken,
            'update_dtm' => now(),
        ]);

        return $accessToken;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createDocumentFromTemplate(string $templateId, array $payload): array
    {
        $token = $this->ensureAccessToken();

        $response = $this->apiClient($token)
            ->post('templates/'.$templateId.'/createdocument', $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Zoho Sign create document failed: '.$response->body());
        }

        /** @var array<string, mixed> $json */
        $json = $response->json() ?? [];

        return $json;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRequest(string $requestId): array
    {
        $token = $this->ensureAccessToken();

        $response = $this->apiClient($token)->get('requests/'.$requestId);

        if (! $response->successful()) {
            throw new RuntimeException('Zoho Sign get request failed: '.$response->body());
        }

        /** @var array<string, mixed> $json */
        $json = $response->json() ?? [];

        return $json;
    }

    public function downloadSignedPdf(string $requestId, string $documentId): string
    {
        $token = $this->ensureAccessToken();

        $response = $this->apiClient($token)
            ->get("requests/{$requestId}/documents/{$documentId}/pdf");

        if (! $response->successful()) {
            throw new RuntimeException('Zoho Sign PDF download failed: '.$response->body());
        }

        return $response->body();
    }

    /**
     * @param  array<string, mixed>  $zohoResponse
     */
    public function recordSignatureRequest(
        Owner $owner,
        string $templateId,
        array $zohoResponse,
        ?int $chronologyId = null,
        ?int $chronologyOrderId = null,
        ?string $filename = null,
    ): HelloSignDetail {
        $requests = $zohoResponse['requests'] ?? $zohoResponse['request'] ?? $zohoResponse;

        return HelloSignDetail::query()->create([
            'ownerid_id' => $owner->id,
            'ownerid_email' => $owner->owner_email,
            'filename' => $filename,
            'chronology_id' => $chronologyId,
            'chronologyorder_id' => $chronologyOrderId,
            'zoho_template_ids' => $templateId,
            'zoho_request_id' => is_array($requests) ? (string) ($requests['request_id'] ?? $requests['requestId'] ?? '') : null,
            'zoho_document_id' => is_array($requests) ? (string) ($requests['document_ids'][0] ?? $requests['document_id'] ?? '') : null,
            'zoho_sign_status' => 0,
            'is_opened' => 0,
            'created_date' => now(),
        ]);
    }

    /**
     * Process a single pending signature detail (idempotent if already completed).
     */
    public function processCompletedRequest(int $helloSignDetailId): HelloSignDetail
    {
        $detail = HelloSignDetail::query()->findOrFail($helloSignDetailId);

        if ($detail->isCompleted()) {
            return $detail;
        }

        if (! filled($detail->zoho_request_id)) {
            throw new RuntimeException("HelloSignDetail {$helloSignDetailId} has no Zoho request id.");
        }

        $request = $this->getRequest((string) $detail->zoho_request_id);
        $status = strtolower((string) data_get($request, 'requests.request_status', data_get($request, 'status', '')));

        if ($status !== 'completed') {
            return $detail;
        }

        $detail->update([
            'zoho_sign_status' => 1,
            'is_opened' => 1,
            'update_date' => now(),
        ]);

        return $detail->fresh() ?? $detail;
    }

    private function apiClient(string $token): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.zoho.api_base_url'), '/').'/')
            ->withToken($token)
            ->acceptJson()
            ->timeout((int) config('services.zoho.timeout', 15));
    }
}
