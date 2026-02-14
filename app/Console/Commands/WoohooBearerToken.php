<?php

namespace App\Console\Commands;

use App\Services\WoohooClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class WoohooBearerToken extends Command
{
    protected $signature = 'giftbox:woohoo-token
                            {--fresh : Clear cached token and fetch a new one}
                            {--show : Output token (default: just verify and cache)}';

    protected $description = 'Generate Woohoo (QwikGift) Bearer token using Consumer Key, Consumer Secret, username and password; optionally display it';

    public function handle(): int
    {
        if ($this->option('fresh')) {
            WoohooClient::fromConfig()->clearCachedToken();
            $this->info('Cached token cleared.');
        }

        if (! config('woohoo.oauth.client_id') || ! config('woohoo.oauth.client_secret')) {
            $this->error('Set WOOHOO_CLIENT_ID and WOOHOO_CLIENT_SECRET in .env');
            return self::FAILURE;
        }
        if (! config('woohoo.oauth.username') || ! config('woohoo.oauth.password')) {
            $this->error('Set WOOHOO_USERNAME and WOOHOO_PASSWORD in .env (from Qwikcilver)');
            return self::FAILURE;
        }

        $client = WoohooClient::fromConfig();
        $authCode = $client->getAuthorizationCode();
        if (! $authCode) {
            $this->error('Failed to get authorization code. Check base URL, client id, username and password.');
            $last = $client->getLastVerifyResponse();
            if ($last !== null) {
                $this->warn('Woohoo verify response: HTTP ' . $last['status']);
                $this->line($last['body']);
            }
            return self::FAILURE;
        }
        $this->info('Authorization code received.');

        $tokenResponse = $client->getTokenResponse($authCode);
        if (! $tokenResponse) {
            $this->error('Failed to exchange for Bearer token. Check client secret.');
            return self::FAILURE;
        }

        $token = $tokenResponse['token'] ?? $tokenResponse['access_token'] ?? null;
        if (! $token) {
            $this->error('Token response did not contain token.');
            return self::FAILURE;
        }

        $client->clearCachedToken();
        Cache::put('woohoo_bearer_token', $token, config('woohoo.token_cache_ttl', 604800));
        $this->info('Bearer token generated and cached.');

        if ($this->option('show')) {
            $this->newLine();
            $this->line('Bearer token:');
            $this->line($token);
            $tokenSecret = $tokenResponse['tokenSecret'] ?? $tokenResponse['token_secret'] ?? $tokenResponse['secret'] ?? null;
            if ($tokenSecret !== null) {
                $this->newLine();
                $this->line('Token secret:');
                $this->line($tokenSecret);
            }
            $this->newLine();
        }

        return self::SUCCESS;
    }
}
