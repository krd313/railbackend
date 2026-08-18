<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $project = Project::query()->inRandomOrder(random_int(1, PHP_INT_MAX))->first(['*']);

        if (! $project) {
            throw new \RuntimeException('No projects exist. Seed projects before tasks.');
        }

        return [
            'project_id' => $project->id,
            'user_id' => $project->user_id ?? User::query()->inRandomOrder(random_int(1, PHP_INT_MAX))->value('id'),
            'name' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            // 'notes' => TaskNote::factory(),
            'reference' => $this->faker->optional()->word(),
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'on_hold', 'completed', 'cancelled']),
            'priority' => $this->faker->numberBetween(0, 5),
            'due_date' => $this->faker->optional()->date(),
        ];
    }
}
