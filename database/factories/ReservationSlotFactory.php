<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ReservationSlot;
use App\Models\Reservation;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReservationSlot>
 */
class ReservationSlotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = ReservationSlot::class;
    public function definition(): array
    {

         // 親 Reservation を作る
        // $reservation = Reservation::factory()->create();

        // start_at は publish_at 以降、deadline_at 以前
        // $startAt = $this->faker->dateTimeBetween($reservation->publish_at, $reservation->deadline_at);
        $startAt = $this->faker->dateTimeBetween('+1 day', '+1 month');

        // end_at は start_at 以降
        $endAt  = (clone $startAt)->modify('+1 hour');

        return [
            'reservation_id' => null,
            'capacity'       => $this->faker->numberBetween(1, 10),
            'current_count'  => 0,
            'start_at'       => $startAt,
            'end_at'         => $endAt,
        ];
    }
}
