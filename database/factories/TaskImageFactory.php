<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\TaskImage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskImage>
 */
class TaskImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileName = $this->faker->uuid() . '.' . $this->faker->randomElement(['jpg', 'jpeg', 'png', 'pdf']);
        $task = Task::query()->inRandomOrder(1)->first(['id', 'project_id']);
        $taskId = $task?->id;
        $projectId = $task?->project_id;
        $userId = User::query()->inRandomOrder(1)->value('id');

        return [
            'task_id' => $taskId ?? Task::factory(),
            'user_id' => $userId ?? User::factory(),
            'image_path' => 'projects/project-' . ($projectId ?? 1) . '/tasks/task-' . ($taskId ?? 1) . '/' . $fileName,
            'file_name' => $fileName,
            'file_type' => $this->faker->randomElement(['image/jpeg', 'image/png', 'application/pdf']),
        ];
    }
}
