<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Testlerde kullanılacak varsayılan bekleyen yorumu üretir.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'user_id' => User::factory(),
            'parent_id' => null,
            'content' => fake()->paragraph(),
            'status' => Comment::STATUS_PENDING,
            'moderated_by' => null,
            'moderated_at' => null,
        ];
    }

    /**
     * Yönetici tarafından onaylanmış yorum üretir.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Comment::STATUS_APPROVED,
            'moderated_by' => User::factory()->admin(),
            'moderated_at' => now(),
        ]);
    }

    /**
     * Yönetici tarafından reddedilmiş yorum üretir.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Comment::STATUS_REJECTED,
            'moderated_by' => User::factory()->admin(),
            'moderated_at' => now(),
        ]);
    }

    /**
     * Belirtilen yoruma cevap olan yorum üretir.
     */
    public function replyTo(Comment $parent): static
    {
        return $this->state(fn (array $attributes): array => [
            'post_id' => $parent->post_id,
            'parent_id' => $parent->id,
        ]);
    }
}
