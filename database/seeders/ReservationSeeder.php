<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Reservation;

class ReservationSeeder extends Seeder
{

    public function run(): void
    {
        // Reservation::factory()->count(10)->create();
        Reservation::factory()
            ->count(10)
            ->hasSlots(5) 
            ->create();
    }
}
