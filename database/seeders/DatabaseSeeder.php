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
        User::factory()->count(10)->create();


        //create Teams
        Team::create([
            'name' => 'xXx',
            'slug' => 'xxx#23951',
        ]);
        Team::create([
            'name' => 'Tech Giants',
            'slug' => 'tech-giants',
        ]);
        Team::create([
            'name' => 'Creative Solutions',
            'slug' => 'creative-solutions',
        ]);
        Team::create([
            'name' => 'Future Tech',
            'slug' => 'future-tech',
        ]);

        // Create Profiles
        Profile::create([
            'name' => 'RankUp',
            'team_id' => 1,
            'avatar' => 'https://example.com/avatar1.jpg',

        ]);

        Profile::create([
            'name' => 'RankDown',
            'team_id' => 1,
            'avatar' => 'https://example.com/avatar1.jpg',

        ]);

        Profile::create([
            'name' => 'Tech Innovators',
            'team_id' => 1,
            'avatar' => 'https://example.com/avatar1.jpg',
        ]);
        Profile::create([
            'name' => 'Creative Minds',
            'team_id' => 1,
            'avatar' => 'https://example.com/avatar2.jpg',
        ]);
        Profile::create([
            'name' => 'Future Vision',
            'team_id' => 1,
            'avatar' => 'https://example.com/avatar3.jpg',
        ]);


        // Create social accounts
        // add random chars in the access_token for testing purposes

        Profile::find(1)->socialAccounts()->create([
            'platform' => 'twitter',
            'access_token' => 'lskghklsg',
            'account_name' => 'rankup_twitter',
        ]);
        Profile::find(1)->socialAccounts()->create([
            'platform' => 'linkedin',
            'access_token' => 'mklsdjgm',
            'account_name' => 'rankup_linkedin',
        ]);
        Profile::find(2)->socialAccounts()->create([
            'platform' => 'twitter',
            'access_token' => 'lmsdgmlslkdurt_',
            'account_name' => 'rankdown_twitter',
        ]);
        Profile::find(2)->socialAccounts()->create([
            'platform' => 'instagram',
            'access_token' => 'oeznclm',
            'account_name' => 'rankdown_linkedin',
        ]);
        Profile::find(3)->socialAccounts()->create([
            'platform' => 'facebook',
            'access_token' => 'lkjnvpozaer',
            'account_name' => 'tech_innovators_twitter',
        ]);
        Profile::find(3)->socialAccounts()->create([
            'platform' => 'linkedin',
            'access_token' => 'drftioz',
            'account_name' => 'tech_innovators_linkedin',
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

        // Create tasks
        Task::create([
            'title' => 'Task 1',
            'description' => 'Description for Task 1',
            'task_column_id' => 1,
            'due_date' => now()->addDays(7),
        ]);
        Task::create([
            'title' => 'Task 2',
            'description' => 'Description for Task 2',
            'task_column_id' => 2,
            'due_date' => now()->addDays(3),
        ]);
        Task::create([
            'title' => 'Task 3',
            'description' => 'Description for Task 3',
            'task_column_id' => 3,
            'due_date' => now()->addDays(1),
        ]);

        Task::create([
            'title' => 'Task 4',
            'description' => 'Description for Task 4',
            'task_column_id' => 1,
            'due_date' => now()->addDays(5),
        ]);
        Task::create([
            'title' => 'Task 5',
            'description' => 'Description for Task 5',
            'task_column_id' => 2,
            'due_date' => now()->addDays(2),
        ]);
    }
}
