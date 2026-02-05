<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Reservation;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Reservation::class;
    public function definition(): array
    {
        // publish_at を生成
        $publishAt = $this->faker->dateTimeBetween('-1 month', 'now');

        // deadline_at は publish_at 以降
        $deadlineAt = $this->faker->dateTimeBetween($publishAt, '+1 month');

        return [
            'created_by'  => 1,
            'title'       => $this->faker->realText(20),
            'staff_name'  => $this->faker->name(),
            'description' => $this->faker->realText(50),
            'publish_at'  => $publishAt,
            'deadline_at' => $deadlineAt,
        ];
    }
}
