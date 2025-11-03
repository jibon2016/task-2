<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Event;
use App\Models\Ticket;
use App\Services\PaymentService;
use Mockery;

class PaymentFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_requires_auth()
    {
        $event = Event::factory()->create();
        $ticket = Ticket::factory()->create(['event_id' => $event->id]);

        $this->postJson('/api/payments', ['ticket_id' => $ticket->id, 'amount' => 100])
            ->assertStatus(401);
    }

    public function test_payment_success_path()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $event = Event::factory()->create();
        $ticket = Ticket::factory()->create(['event_id' => $event->id, 'user_id' => $user->id]);

        // mock PaymentService in the container to simulate gateway success
        $mock = Mockery::mock(PaymentService::class);
        $mock->shouldReceive('charge')->once()->withArgs(function ($payload) use ($ticket) {
            return isset($payload['ticket_id']) && $payload['ticket_id'] === $ticket->id;
        })->andReturn(['status' => 'success', 'transaction_id' => 'tx_123']);

        $this->app->instance(PaymentService::class, $mock);

        $response = $this->postJson('/api/payments', [
            'ticket_id' => $ticket->id,
            'amount' => 100,
            'payment_method' => 'card',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['status' => 'success', 'transaction_id' => 'tx_123']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
