<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::all()->random()->id,
            'ticket_id' => Ticket::all()->random()->id,
            'quantity' => $this->faker->numberBetween(1, 5),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'canceled']),
        ];
    }
}
