<?php

namespace App\Services\Woohoo;

use Illuminate\Support\Facades\Cache;

class WoohooRefnoGenerator
{
    public function __construct(
        protected string $orgShortCode
    ) {}

    public static function fromConfig(): self
    {
        return new self(config('woohoo.org_short_code', 'ONEZERO'));
    }

    /**
     * Generate unique refno: ORGCODE_YYYYMMDD_SEQ_UNIQ (e.g. ONEZERO_20260219_001_a1b2c3d4).
     * Sequence is per-day; UNIQ suffix guarantees uniqueness even if cache fails.
     */
    public function generate(): string
    {
        $date = now()->utc()->format('Ymd');
        $key = 'woohoo_refno_seq_' . $date;
        $seq = (int) Cache::increment($key);
        if ($seq === 1) {
            Cache::put($key, 1, now()->endOfDay()->addDay());
        }
        $base = $this->orgShortCode . '_' . $date . '_' . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
        $uniq = bin2hex(random_bytes(4)); // 8 chars, guarantees uniqueness per request
        return $base . '_' . $uniq;
    }
}
