<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Services\Woohoo\WoohooBalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function __construct(protected WoohooBalanceService $balanceService) {}

    /**
     * POST /api/v1/balance
     *
     * Public — no auth required.
     * Checks the remaining balance on a Woohoo gift card.
     */
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'card_number' => 'required|string|min:4|max:50',
            'pin'         => 'nullable|string|max:30',
            'sku'         => 'nullable|string|max:100',
        ]);

        $result = $this->balanceService->check(
            $request->input('card_number'),
            $request->input('pin'),
            $request->input('sku')
        );

        if ($result['success']) {
            return $this->success([
                'card_number' => $result['cardNumber'],
                'balance'     => $result['balance'],
                'expiry'      => $result['expiry'],
                'status'      => $result['status'],
                'currency'    => $result['currency'],
            ], 'Balance retrieved successfully.');
        }

        // Return a 422 so the frontend can display the Woohoo error message.
        return $this->error($result['error'] ?? 'Balance enquiry failed.', 422, [
            'code' => $result['code'] ?? null,
        ]);
    }
}
