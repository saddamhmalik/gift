<?php

namespace App\Services;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WoohooClient
{
    public function __construct(
        protected string $baseUrl,
        protected string $clientId,
        protected string $clientSecret,
        protected string $username,
        protected string $password,
        protected array $endpoints,
        protected int $tokenCacheTtl
    ) {}

    public static function fromConfig(): self
    {
        $config = config('woohoo');
        return new self(
            baseUrl: rtrim($config['base_url'], '/'),
            clientId: $config['oauth']['client_id'] ?? '',
            clientSecret: $config['oauth']['client_secret'] ?? '',
            username: $config['oauth']['username'] ?? '',
            password: $config['oauth']['password'] ?? '',
            endpoints: $config['endpoints'] ?? [],
            tokenCacheTtl: $config['token_cache_ttl'] ?? 604800
        );
    }

    protected ?array $lastVerifyResponse = null;

    public function getAuthorizationCode(): ?string
    {
        $this->lastVerifyResponse = null;
        $url = $this->baseUrl . ($this->endpoints['verify'] ?? '/oauth2/verify');
        $response = Http::acceptJson()
            ->contentType('application/json')
            ->withHeaders($this->getOAuthHeaders())
            ->timeout(15)
            ->post($url, [
                'clientId' => $this->clientId,
                'username' => $this->username,
                'password' => $this->password,
            ]);

        $this->lastVerifyResponse = [
            'status' => $response->status(),
            'body' => $response->body(),
            'success' => $response->successful(),
        ];

        if (! $response->successful()) {
            Log::warning('Woohoo OAuth2 verify failed', $this->lastVerifyResponse);
            return null;
        }

        $data = $response->json();
        $code = $data['authorizationCode'] ?? $data['authorization_code'] ?? null;
        if ($code !== null) {
            $this->lastVerifyResponse['authorizationCode'] = $code;
        }
        return $code;
    }

    /** @return array{status: int, body: string, success: bool}|null */
    public function getLastVerifyResponse(): ?array
    {
        return $this->lastVerifyResponse;
    }

    public function getTokenFromAuthorizationCode(string $authorizationCode): ?string
    {
        $url = $this->baseUrl . ($this->endpoints['token'] ?? '/oauth2/token');
        $response = Http::acceptJson()
            ->contentType('application/json')
            ->withHeaders($this->getOAuthHeaders())
            ->post($url, [
                'clientId' => $this->clientId,
                'clientSecret' => $this->clientSecret,
                'authorizationCode' => $authorizationCode,
            ]);

        if (! $response->successful()) {
            Log::warning('Woohoo OAuth2 token exchange failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        $data = $response->json();
        return $data['token'] ?? $data['access_token'] ?? null;
    }

    /** @return array<string, mixed>|null */
    public function getTokenResponse(string $authorizationCode): ?array
    {
        $url = $this->baseUrl . ($this->endpoints['token'] ?? '/oauth2/token');
        $response = Http::acceptJson()
            ->contentType('application/json')
            ->withHeaders($this->getOAuthHeaders())
            ->post($url, [
                'clientId' => $this->clientId,
                'clientSecret' => $this->clientSecret,
                'authorizationCode' => $authorizationCode,
            ]);

        if (! $response->successful()) {
            return null;
        }
        $data = $response->json();
        return is_array($data) ? $data : null;
    }

    public function getBearerToken(): ?string
    {
        $cacheKey = 'woohoo_bearer_token';
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        $authCode = $this->getAuthorizationCode();
        if (! $authCode) {
            return null;
        }
        $token = $this->getTokenFromAuthorizationCode($authCode);
        if ($token !== null) {
            Cache::put($cacheKey, $token, $this->tokenCacheTtl);
        }
        return $token;
    }

    public function clearCachedToken(): void
    {
        Cache::forget('woohoo_bearer_token');
    }

    /** @return array<string, string> */
    protected function getOAuthHeaders(): array
    {
        $ua = config('woohoo.user_agent', 'GiftBox/1.0 (QwikGift API Client)');
        return ['User-Agent' => $ua];
    }

    public function get(string $path, array $query = [])
    {
        $token = $this->getBearerToken();
        if (! $token) {
            return Http::response(null, 401);
        }

        $url = $this->baseUrl . $path;
        if (! empty($query)) {
            ksort($query);
            $url .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }

        $encodedUrl = rawurlencode($url);
        $baseString = 'GET&' . $encodedUrl;
        $signature = hash_hmac('sha512', $baseString, $this->clientSecret);

        $result = Http::withHeaders(array_merge($this->getOAuthHeaders(), [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'Accept' => '*/*',
            'dateAtClient' => now()->utc()->isoFormat('YYYY-MM-DDTHH:mm:ss[Z]'),
            'signature' => $signature,
        ]))->get($url);

        return $result instanceof PromiseInterface ? $result->wait() : $result;
    }
}
