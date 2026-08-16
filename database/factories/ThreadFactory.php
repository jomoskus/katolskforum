<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ThreadKind;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Thread>
 */
class ThreadFactory extends Factory
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
            'title' => rtrim(fake()->sentence(6), '.'),
            'kind' => ThreadKind::Article,
            'body' => fake()->paragraphs(3, true),
            'url' => null,
        ];
    }

    /**
     * A thread that links to an external page with the author's commentary.
     */
    public function link(): static
    {
        return $this->state(fn (array $attributes): array => [
            'kind' => ThreadKind::Link,
            'url' => fake()->url(),
            'body' => fake()->paragraph(),
        ]);
    }

    /**
     * A thread whose author has deleted their account.
     */
    public function fromDeletedUser(): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => null,
        ]);
    }
}
