<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\PaymentService;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_charge_success()
    {
        // Fake the external gateway response
        Http::fake([
            'https://payment-gateway.example/*' => Http::response([
                'status' => 'success',
                'transaction_id' => 'tx_abc123',
            ], 200),
        ]);

        $service = new PaymentService();

        $result = $service->charge([
            'amount' => 100,
            'token' => 'tok_visa',
            'ticket_id' => 1,
        ]);

        $this->assertIsArray($result);
        $this->assertEquals('success', $result['status']);
        $this->assertArrayHasKey('transaction_id', $result);
    }

    public function test_charge_failure_throws_exception()
    {
        Http::fake([
            'https://payment-gateway.example/*' => Http::response(['error' => 'card_declined'], 402),
        ]);

        $service = new PaymentService();

        $this->expectException(\Exception::class);

        $service->charge([
            'amount' => 100,
            'token' => 'tok_bad',
            'ticket_id' => 1,
        ]);
    }
}
