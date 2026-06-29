<?php

namespace Database\Factories;

use App\Models\Chirp;
use App\Models\Like;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Like>
 */
class LikeFactory extends Factory
{
    protected $model = Like::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'chirp_id' => Chirp::factory(),
            'comment_id' => null,
            'type' => 'like',
        ];
    }

    public function dislike(): static
    {
        return $this->state(fn () => ['type' => 'dislike']);
    }
}
