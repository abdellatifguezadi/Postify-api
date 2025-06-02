<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\Task;
use App\Models\TaskColumn;
use App\Models\Team;
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
            'password' => bcrypt('password123'),
        ]);

        Team::create([
            'name' => 'xXx',
            'slug' => 'xxx#23951',
        ]);

        Profile::create([
            'name' => 'RankUp',
            'team_id' => 1,
        ]);

        // Create task columns

        TaskColumn::create([
            'name' => 'To Do',
            'profile_id' => 1,
        ]);

        TaskColumn::create([
            'name' => 'In Progress',
            'profile_id' => 1,
        ]);

        TaskColumn::create([
            'name' => 'Done',
            'profile_id' => 1,
        ]);

        // Task::factory()->create([
        //     'title' => 'Sample Task',
        //     'description' => 'This is a sample task description.',
        //     'user_id' => 1,
        //     'task_column_id' => 1,
        // ]);

        // Task::factory()->create([
        //     'title' => 'Another Task',
        //     'description' => 'This is another task description.',
        //     'user_id' => 1,
        //     'task_column_id' => 2,
        // ]);

        // Task::factory()->create([
        //     'title' => 'Completed Task',
        //     'description' => 'This task is completed.',
        //     'user_id' => 1,
        //     'task_column_id' => 3,
        // ]);
    }
}
