<?php

namespace Database\Factories;

use App\Models\TaskNote;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskNote>
 */
class TaskNoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'user_id' => User::factory(),
            'note' => $this->faker->text(),
            'date' => $this->faker->date(),
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'on_hold', 'completed', 'cancelled']),
        ];
    }
}
