<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ── Loyalty Program ──────────────────────────────────────────
            [
                'group'       => 'loyalty',
                'key'         => 'loyalty.default_rate',
                'value'       => '0.01',
                'type'        => 'float',
                'label'       => 'Default Earn Rate',
                'description' => 'Fraction of order amount credited as points (0.01 = 1%). Products can override this.',
            ],
            [
                'group'       => 'loyalty',
                'key'         => 'loyalty.validity_days',
                'value'       => '30',
                'type'        => 'integer',
                'label'       => 'Points Validity (days)',
                'description' => 'Number of days before earned points expire.',
            ],
            [
                'group'       => 'loyalty',
                'key'         => 'loyalty.min_redeem',
                'value'       => '1',
                'type'        => 'float',
                'label'       => 'Minimum Redeemable Points',
                'description' => 'Minimum points required to apply any redemption on an order.',
            ],
            [
                'group'       => 'loyalty',
                'key'         => 'loyalty.max_redeem_per_order',
                'value'       => '500',
                'type'        => 'float',
                'label'       => 'Max Points Per Order (₹)',
                'description' => 'Maximum points (₹ value) a user can redeem on a single order. Set 0 for no cap.',
            ],
        ];

        foreach ($settings as $s) {
            Setting::updateOrCreate(['key' => $s['key']], $s);
        }
    }
}
