<?php

namespace Database\Seeders;

use App\Models\BoardUser;
use App\Models\Task;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $boardUser = BoardUser::updateOrCreate(
            ['board_id' => 60, 'user_id' => 5], // Check for existing entry
            [
                'role' => 'owner' // Set role
            ]
        );

        // Create tasks for the hard-coded board and user
        Task::factory()->count(10)->create([
            'board_id' => 60,               // Hard-coded board ID
            'board_user_id' => $boardUser->id,
        ]);
    }
}
