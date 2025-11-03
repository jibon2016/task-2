<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Event;
use App\Models\Ticket;

class TicketBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_booking_requires_auth()
    {
        $event = Event::factory()->create();

        $this->postJson("/api/events/{$event->id}/tickets", ['quantity' => 1])
            ->assertStatus(401);
    }

    public function test_ticket_booking_success()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();
        $this->actingAs($user, 'api');

        $payload = ['quantity' => 2];

        $response = $this->postJson("/api/events/{$event->id}/tickets", $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tickets', [
            'event_id' => $event->id,
            'user_id' => $user->id,
        ]);

        // ensure the response contains ticket data
        $response->assertJsonStructure(['data' => ['id', 'event_id', 'user_id', 'quantity']]);
    }
}
