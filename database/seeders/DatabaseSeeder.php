<?php

namespace Database\Seeders;

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

        Task::factory()->create([
            'user_id' => 4,
            'name' => 'Sample Task',
            'due' => '2024-08-03',
            'priority' => 'medium',
            'progress' => 'to_do',
            'tag' => 'Shopping'
        ]);
    }
}
