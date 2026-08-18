<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\Task;
use App\Models\TaskImage;
use App\Models\TaskNote;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'krd313',
            'email' => 'krd313@gmail.com',
            'password' => bcrypt('password'),
        ]);
        User::factory(5)->create();
        Project::factory(5)->create();
        Task::factory(10)->create();
        TaskNote::factory(10)->create();
        TaskImage::factory(10)->create();
        ProjectImage::factory(10)->create();

        // Ensure first seeded records are owned by user 1.
        // Project::query()->orderBy('id')->limit(10)->update(['user_id' => 1]);
        // Task::query()->orderBy('id')->limit(10)->update(['user_id' => 1]);
        // TaskNote::query()->orderBy('id')->limit(10)->update(['user_id' => 1]);
        // TaskImage::query()->orderBy('id')->limit(10)->update(['user_id' => 1]);
        // ProjectImage::query()->orderBy('id')->limit(10)->update(['user_id' => 1]);
    }
}
