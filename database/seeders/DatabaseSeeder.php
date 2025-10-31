<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name'     => 'Admin User',
            'email'    => 'test@example.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
            'phone'    => '1234567890',
            ]);
        User::factory(2)->create([
            'role' => 'admin',
        ]);
        User::factory(3)->create([
            'role' => 'organizer',
        ]);
        User::factory(10)->create([
            'role' => 'customer',
        ]);
        $this->call(
            [
                EventSeeder::class,
                TicketSeeder::class,
                BookingSeeder::class,
                PaymentSeeder::class,
            ]
        );
    }
}
