<?php

namespace Database\Factories;
use App\Models\Chat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chat>
 */
class ChatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Chat::class;
    public function definition(): array
    {
        return [
            'sender_id'   => $this->faker->numberBetween(1, 30),
            'receiver_id' => 1,
            'message'     => $this->faker->realText(50),
            'created_at'  => $this->faker->date(),
        ];
    }
}
