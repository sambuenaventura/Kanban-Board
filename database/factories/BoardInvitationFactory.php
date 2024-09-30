<?php

namespace Database\Factories;

use App\Models\Board;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BoardInvitation>
 */
class BoardInvitationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'board_id' => Board::factory(),
            'user_id' => User::factory(),
            'invited_by' => User::factory(),
            'status' => $this->faker->randomElement(['pending', 'accepted', 'declined']),
        ];
    }
}
