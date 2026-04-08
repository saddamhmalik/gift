<?php

namespace App\Exceptions\Woohoo;

use Exception;

class WoohooOrderException extends Exception
{
    public function __construct(
        string $message,
        protected ?string $woohooCode = null,
        protected ?array $woohooResponse = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getWoohooCode(): ?string
    {
        return $this->woohooCode;
    }

    public function getWoohooResponse(): ?array
    {
        return $this->woohooResponse;
    }

    public static function duplicateRefno(?array $response = null): self
    {
        return new self(
            'Woohoo order failed: duplicate refno (5313).',
            '5313',
            $response
        );
    }

    public static function insufficientBalance(?array $response = null): self
    {
        return new self(
            'Woohoo order failed: insufficient wallet balance (6063).',
            '6063',
            $response
        );
    }

    public static function svcNotEnabled(?array $response = null): self
    {
        return new self(
            'Woohoo order failed: SVC payment not enabled (5035).',
            '5035',
            $response
        );
    }

    public static function fromResponse(array $response, string $defaultMessage = 'Woohoo order failed.'): self
    {
        $code = $response['errorCode'] ?? $response['code'] ?? null;
        $message = $response['message'] ?? $response['errorMessage'] ?? $defaultMessage;
        if ($code !== null) {
            $message = "Woohoo [{$code}]: {$message}";
        }
        return new self($message, $code ? (string) $code : null, $response);
    }

    public static function clientTimeout(string $refno, ?\Throwable $previous = null): self
    {
        return new self(
            "Woohoo order request timed out before a response (refno {$refno}). Reconcile with Order Status API if an order id is available.",
            'CLIENT_TIMEOUT',
            ['refno' => $refno],
            $previous
        );
    }
}
