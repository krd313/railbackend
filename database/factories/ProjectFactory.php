<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'notes' => $this->faker->text(),
            'due_date' => $this->faker->date(),
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'on_hold', 'completed']),
            'user_id' => User::factory(),
            'share_with' => null,
        ];
    }
}
