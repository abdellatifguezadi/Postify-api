<?php

namespace Database\Seeders;

use App\Models\TaskColumn;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskColumnFactory extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TaskColumn::factory()
            ->create([
                'name' => 'Default Column',
            ]);
    }
}
