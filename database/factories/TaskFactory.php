<?php

namespace Database\Factories;

use App\Models\Board;
use App\Models\BoardUser;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'board_id' => Board::factory(),
            'board_user_id' => BoardUser::factory(),
            'name' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'due' => $this->faker->dateTimeBetween('now', '+1 month'),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
            'progress' => $this->faker->randomElement(['to_do', 'in_progress', 'done']),
            'tag' => $this->faker->word,
            'attachment' => null,
        ];
    }
}