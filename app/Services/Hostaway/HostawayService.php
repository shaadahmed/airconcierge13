<?php

namespace App\Services\Hostaway;

use App\Models\HostawayAccessToken;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Hostaway HTTP API client (OAuth token refresh on demand — ADR-011).
 */
class HostawayService
{
    /**
     * @return array<string, mixed>
     */
    public function getReservations(?int $reservationId = null): array
    {
        if ($reservationId === null) {
            return ['error' => 'Reservation id is required.'];
        }

        return $this->getJson("reservations/{$reservationId}");
    }

    /**
     * @return array<string, mixed>
     */
    public function getListing(int $listingId): array
    {
        return $this->getJson("listings/{$listingId}");
    }

    /**
     * @return array<string, mixed>
     */
    public function getFinanceFields(int $reservationId): array
    {
        return $this->getJson("financeField/{$reservationId}");
    }

    /**
     * @return array<string, mixed>
     */
    public function getFinanceStandardFields(int $reservationId): array
    {
        return $this->getJson("financeStandardField/reservation/{$reservationId}");
    }

    /**
     * @return array<string, mixed>
     */
    public function getReviews(?int $reservationId = null): array
    {
        if ($reservationId !== null) {
            return $this->getJson('reviews', ['reservationId' => $reservationId]);
        }

        return $this->getJson('reviews', [
            'statuses' => ['published'],
            'sortBy' => 'departureDate',
            'sortOrder' => 'desc',
        ]);
    }

    /**
     * Request a new OAuth access token from Hostaway (does not persist).
     *
     * @return array<string, mixed>
     */
    public function getAccessToken(): array
    {
        $accountId = config('services.hostaway.account_id');
        $apiKey = config('services.hostaway.api_key');

        if (! is_string($accountId) || $accountId === '' || ! is_string($apiKey) || $apiKey === '') {
            return ['error' => 'Hostaway API credentials are not configured.'];
        }

        try {
            $response = $this->baseClient()
                ->asForm()
                ->post('accessTokens', [
                    'grant_type' => 'client_credentials',
                    'client_id' => $accountId,
                    'client_secret' => $apiKey,
                    'scope' => 'general',
                ]);
        } catch (ConnectionException $e) {
            return ['error' => $e->getMessage()];
        }

        if ($response->failed()) {
            return ['error' => $response->body() !== '' ? $response->body() : 'Hostaway token request failed.'];
        }

        /** @var array<string, mixed> $json */
        $json = $response->json() ?? [];

        return $json;
    }

    /**
     * Return a valid Bearer token, refreshing and persisting when missing/expired.
     */
    public function ensureAccessToken(): ?string
    {
        $existing = HostawayAccessToken::query()
            ->whereDate('expiry', '>', now()->toDateString())
            ->orderByDesc('id')
            ->first();

        if ($existing !== null && ! $existing->isExpired()) {
            return $existing->access_token;
        }

        $tokenResponse = $this->getAccessToken();

        if (isset($tokenResponse['error']) || ! isset($tokenResponse['access_token'], $tokenResponse['token_type'])) {
            return null;
        }

        $expiresInSeconds = isset($tokenResponse['expires_in']) && is_numeric($tokenResponse['expires_in'])
            ? (int) $tokenResponse['expires_in']
            : 63_072_000;

        $token = HostawayAccessToken::query()->create([
            'access_token' => (string) $tokenResponse['access_token'],
            'token_type' => (string) $tokenResponse['token_type'],
            'expiry' => now()->addSeconds(max($expiresInSeconds, 86400))->toDateString(),
        ]);

        return $token->access_token;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private function getJson(string $path, array $query = []): array
    {
        try {
            $client = $this->authenticatedClient();
        } catch (Throwable $e) {
            return ['error' => $e->getMessage()];
        }

        if ($client === null) {
            return ['error' => 'Unable to obtain Hostaway access token.'];
        }

        try {
            $response = $client->get($path, $query);
        } catch (ConnectionException $e) {
            return ['error' => $e->getMessage()];
        }

        if ($response->failed()) {
            return ['error' => $response->body() !== '' ? $response->body() : 'Hostaway API request failed.'];
        }

        /** @var array<string, mixed> $json */
        $json = $response->json() ?? [];

        return $json;
    }

    private function authenticatedClient(): ?PendingRequest
    {
        $token = $this->ensureAccessToken();

        if ($token === null) {
            return null;
        }

        return $this->baseClient()->withToken($token);
    }

    private function baseClient(): PendingRequest
    {
        $baseUrl = config('services.hostaway.base_url');
        $timeout = config('services.hostaway.timeout', 15);
        $connectTimeout = config('services.hostaway.connect_timeout', 5);

        return Http::baseUrl(is_string($baseUrl) ? $baseUrl : 'https://api.hostaway.com/v1/')
            ->acceptJson()
            ->timeout(is_int($timeout) ? $timeout : 15)
            ->connectTimeout(is_int($connectTimeout) ? $connectTimeout : 5);
    }
}
