<?php

namespace App\Services;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Factory as HttpClientFactory;
use Illuminate\Http\Client\Response;
use Psr\Http\Message\ResponseInterface;
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
            ->timeout((int) config('woohoo.http_timeout.oauth', 15))
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
            ->timeout((int) config('woohoo.http_timeout.oauth', 15))
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
            ->timeout((int) config('woohoo.http_timeout.oauth', 15))
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

    /**
     * Woohoo may invalidate the cached bearer token (e.g. server-side expiry).
     * Response body often contains oauth_problem=token_rejected or JSON with oauth_problem.
     */
    protected function responseIndicatesTokenRejected(Response $response): bool
    {
        $body = $response->body();
        if ($body === '') {
            return false;
        }
        if (stripos($body, 'token_rejected') !== false) {
            return true;
        }
        $json = json_decode($body, true);
        if (is_array($json)) {
            $problem = $json['oauth_problem'] ?? $json['error'] ?? null;
            if (is_string($problem) && stripos($problem, 'token_rejected') !== false) {
                return true;
            }
        }

        return false;
    }

    protected function awaitResponse(mixed $pending): Response
    {
        $guard = 0;
        while ($guard++ < 32) {
            if ($pending instanceof Response) {
                return $pending;
            }
            if ($pending instanceof PromiseInterface) {
                $pending = $pending->wait(true);
                continue;
            }
            if (is_object($pending) && method_exists($pending, 'wait')) {
                $pending = $pending->wait(true);
                continue;
            }
            if ($pending instanceof ResponseInterface) {
                return new Response($pending);
            }
            break;
        }

        throw new \RuntimeException('Unexpected HTTP client result: '.(is_object($pending) ? $pending::class : gettype($pending)));
    }

    /** @return array<string, string> */
    protected function getOAuthHeaders(): array
    {
        $ua = config('woohoo.user_agent', 'GiftBox/1.0 (QwikGift API Client)');
        return ['User-Agent' => $ua];
    }

    public function get(string $path, array $query = []): Response
    {
        // Per Woohoo OAuth2.0 docs (GET with query params):
        //   Step B: sort query params alphabetically
        //   Step C: rawurlencode the COMPLETE URL (including sorted query params)
        //   Step D: base string = "GET&{C}"
        //   Signature = HMAC-SHA512(D, clientSecret)
        //
        // Extract any query string embedded directly in $path (e.g. /foo?a=1&b=2)
        [$cleanPath, $inlineQuery] = array_pad(explode('?', $path, 2), 2, null);

        // Merge inline + explicit query params, then sort by key (ASCII order)
        $allQuery = [];
        if ($inlineQuery !== null && $inlineQuery !== '') {
            parse_str($inlineQuery, $parsedInline);
            $allQuery = array_merge($allQuery, $parsedInline);
        }
        if (! empty($query)) {
            $allQuery = array_merge($allQuery, $query);
        }

        // Build the request URL with alphabetically-sorted query params (Step B)
        $requestUrl = $this->baseUrl . $cleanPath;
        if (! empty($allQuery)) {
            ksort($allQuery);   // ASCII sort per Woohoo spec
            $requestUrl .= '?' . http_build_query($allQuery, '', '&', PHP_QUERY_RFC3986);
        }

        // Signature includes the FULL URL (with sorted query params) — Step C, D
        $encodedUrl = rawurlencode($requestUrl);
        $baseString = 'GET&' . $encodedUrl;
        $signature  = hash_hmac('sha512', $baseString, $this->clientSecret);

        $timeout = (int) config('woohoo.http_timeout.get', 30);

        for ($attempt = 0; $attempt < 2; $attempt++) {
            $token = $this->getBearerToken();
            if (! $token) {
                return new Response(HttpClientFactory::psr7Response(null, 401));
            }

            $result = Http::timeout($timeout)
                ->withHeaders(array_merge($this->getOAuthHeaders(), [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json',
                    'Accept'        => '*/*',
                    'dateAtClient'  => now()->utc()->isoFormat('YYYY-MM-DDTHH:mm:ss[Z]'),
                    'signature'     => $signature,
                ]))->get($requestUrl);

            $result = $this->awaitResponse($result);

            if ($attempt === 0 && $this->responseIndicatesTokenRejected($result)) {
                Log::warning('Woohoo API: bearer token rejected; clearing cache and retrying once', [
                    'path'   => $cleanPath,
                    'status' => $result->status(),
                ]);
                $this->clearCachedToken();

                continue;
            }

            return $result;
        }

        throw new \LogicException('WoohooClient::get: retry loop exited without return');
    }

    /**
     * POST request to Woohoo REST v3 (e.g. order create).
     * OAuth2.0 signature per Qwikcilver docs: sort body by keys, base string = POST&encoded_url&encoded_body, HMAC-SHA512.
     *
     * @param  array<string, mixed>  $body
     * @return \Illuminate\Http\Client\Response
     */
    public function post(string $path, array $body): Response
    {
        $url = $this->baseUrl . $path;
        $sortedBody = $this->sortJsonKeysRecursive($body);
        $bodyJson = json_encode($sortedBody, JSON_UNESCAPED_SLASHES);
        $encodedUrl = rawurlencode($url);
        $encodedBody = rawurlencode($bodyJson);
        $baseString = 'POST&' . $encodedUrl . '&' . $encodedBody;
        $signature = hash_hmac('sha512', $baseString, $this->clientSecret);

        $timeout = (int) config('woohoo.http_timeout.post', 10);

        for ($attempt = 0; $attempt < 2; $attempt++) {
            $token = $this->getBearerToken();
            if (! $token) {
                return new Response(HttpClientFactory::psr7Response(null, 401));
            }

            $result = Http::timeout($timeout)
                ->withHeaders(array_merge($this->getOAuthHeaders(), [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => '*/*',
                    'dateAtClient' => now()->utc()->isoFormat('YYYY-MM-DDTHH:mm:ss[Z]'),
                    'signature' => $signature,
                ]))->withBody($bodyJson, 'application/json')->post($url);

            $result = $this->awaitResponse($result);

            if ($attempt === 0 && $this->responseIndicatesTokenRejected($result)) {
                Log::warning('Woohoo API: bearer token rejected; clearing cache and retrying once', [
                    'path'   => $path,
                    'status' => $result->status(),
                ]);
                $this->clearCachedToken();

                continue;
            }

            return $result;
        }

        throw new \LogicException('WoohooClient::post: retry loop exited without return');
    }

    /**
     * Sort array keys recursively (ASCII order) per Woohoo OAuth2.0 signature spec.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function sortJsonKeysRecursive(array $data): array
    {
        $sorted = [];
        foreach ($data as $k => $v) {
            $sorted[$k] = is_array($v) ? $this->sortJsonKeysRecursive($v) : $v;
        }
        ksort($sorted);

        return $sorted;
    }
}
