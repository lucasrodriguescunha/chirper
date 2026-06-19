<?php

namespace Database\Factories;

use App\Models\Chirp;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Chirp>
 */
class ChirpFactory extends Factory
{
    protected $model = Chirp::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'message' => fake()->sentence(),
        ];
    }
}
