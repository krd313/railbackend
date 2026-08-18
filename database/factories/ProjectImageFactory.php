<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectImage>
 */
class ProjectImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileName = $this->faker->uuid() . '.' . $this->faker->randomElement(['jpg', 'jpeg', 'png', 'pdf']);
        $projectId = Project::query()->inRandomOrder(1)->value('id');
        $userId = User::query()->inRandomOrder(1)->value('id');

        return [
            'project_id' => $projectId ?? Project::factory(),
            'user_id' => $userId ?? User::factory(),
            'image_path' => 'projects/project-' . ($projectId ?? 1) . '/' . $fileName,
            'file_name' => $fileName,
            'file_type' => $this->faker->randomElement(['image/jpeg', 'image/png', 'application/pdf']),
        ];
    }
}
