<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Event;

class EventTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_creation_requires_auth()
    {
        $payload = [
            'title' => 'My Event',
            'description' => 'Event desc',
            'date' => now()->addWeek()->toDateString(),
            'location' => 'Venue',
        ];

        $this->postJson('/api/events', $payload)->assertStatus(401);
    }

    public function test_event_creation_success()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $payload = [
            'title' => 'My Event',
            'description' => 'Event desc',
            'date' => now()->addWeek()->toDateString(),
            'location' => 'Venue',
        ];

        $response = $this->postJson('/api/events', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('events', ['title' => 'My Event']);
    }

    public function test_event_list_and_show()
    {
        Event::factory()->count(3)->create();

        $response = $this->getJson('/api/events');
        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'meta']);

        $event = Event::first();
        $this->getJson("/api/events/{$event->id}")
            ->assertStatus(200)
            ->assertJsonFragment(['title' => $event->title]);
    }
}
