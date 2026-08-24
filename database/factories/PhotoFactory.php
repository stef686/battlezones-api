<?php

namespace Database\Factories;

use App\Models\Photo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Photo>
 */
class PhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'path' => 'photos/'.fake()->uuid().'.jpg',
            'thumbnail_path' => 'photos/thumbs/'.fake()->uuid().'.jpg',
        ];
    }

    /**
     * A Photo uploaded without a description.
     *
     * The default carries one so the shape stays fixed: the API documentation
     * infers its schema from a factory model, and a field that is sometimes
     * null moves `nullable` in and out of the committed spec.
     */
    public function withoutDescription(): static
    {
        return $this->state(['description' => null]);
    }
}
