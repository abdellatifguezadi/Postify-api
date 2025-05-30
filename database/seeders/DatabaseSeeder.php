<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\TaskColumn;
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

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'gaxown@outlook.com',
        ]);
        
        TaskColumn::factory()->create([
            'name' => 'To Do',
        ]);

        TaskColumn::factory()->create([
            'name' => 'In Progress',
        ]);

        TaskColumn::factory()->create([
            'name' => 'Done',
        ]);

        Task::factory()->create([
            'title' => 'Sample Task',
            'description' => 'This is a sample task description.',
            'user_id' => 1,
            'task_column_id' => 1,
        ]);

        Task::factory()->create([
            'title' => 'Another Task',
            'description' => 'This is another task description.',
            'user_id' => 1,
            'task_column_id' => 2,
        ]);

        Task::factory()->create([
            'title' => 'Completed Task',
            'description' => 'This task is completed.',
            'user_id' => 1,
            'task_column_id' => 3,
        ]);
    }
}
