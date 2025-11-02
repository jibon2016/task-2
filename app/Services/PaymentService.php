<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentService
{
    protected int $delayMs;

    /**
     * @param int $delayMs simulate network latency in milliseconds (default 200ms)
     */
    public function __construct(int $delayMs = 200)
    {
        $this->delayMs = $delayMs;
    }

    /**
     * Simulate processing a payment.
     *
     * @param array $payload  Example: ['amount' => 12.34, 'currency' => 'USD', 'method' => [...]]
     * @param bool|null $forceOutcome  true = force success, false = force failure, null = random
     * @return array  ['success' => bool, 'transaction_id' => ?string, 'message' => string, 'raw' => array]
     */
    public function process(array $payload, ?bool $forceOutcome = null): array
    {
        // simulate latency
        if ($this->delayMs > 0) {
            usleep($this->delayMs * 1000);
        }

        // determine outcome: forced or random (80% success by default)
        $success = $forceOutcome ?? (mt_rand(1, 100) <= 80);

        $transactionId = $success ? (string) Str::uuid() : null;
        $message = $success
            ? 'Payment processed successfully.'
            : 'Payment failed due to simulated gateway error.';

        $raw = [
            'simulated_gateway' => true,
            'payload' => $payload,
            'outcome' => $success ? 'success' : 'failure',
            'timestamp' => now()->toISOString(),
        ];

        if ($success) {
            Log::info('PaymentService: success', [
                'transaction_id' => $transactionId,
                'amount' => $payload['amount'] ?? null,
            ]);

            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'message' => $message,
                'raw' => $raw,
            ];
        }

        Log::warning('PaymentService: failure', [
            'payload' => $payload,
        ]);

        return [
            'success' => false,
            'transaction_id' => null,
            'message' => $message,
            'raw' => $raw,
        ];
    }
}
