<?php

namespace Database\Factories;

use App\Enums\CommentStatusEnum;
use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'content' => fake()->title(),
            'user_id' => User::factory()->create()->id,
            'article_id' => Article::factory()->create()->id,
            'status' => CommentStatusEnum::PUBLISHED->value
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CommentStatusEnum::PENDING->value,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CommentStatusEnum::REJECTED->value,
        ]);
    }
}
