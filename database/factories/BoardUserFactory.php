<?php

namespace Database\Factories;

use App\Models\Board;
use App\Models\BoardUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BoardUser>
 */
class BoardUserFactory extends Factory
{
    protected $model = BoardUser::class;

    public function definition()
    {
        return [
            'board_id' => Board::factory(),
            'user_id' => User::factory(),
            'role' => 'owner',
        ];
    }
}