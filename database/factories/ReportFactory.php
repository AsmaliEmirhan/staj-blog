<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    /**
     * Varsayılan olarak bekleyen bir yazı bildirimi üretir.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'reportable_type' => Post::class,
            'reportable_id' => Post::factory(),
            'reason' => fake()->randomElement([
                Report::REASON_SPAM,
                Report::REASON_HARASSMENT,
                Report::REASON_INAPPROPRIATE,
                Report::REASON_MISINFORMATION,
                Report::REASON_OTHER,
            ]),
            'description' => fake()->optional()->sentence(),
            'status' => Report::STATUS_PENDING,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'resolution_note' => null,
        ];
    }

    /**
     * Bildirimi belirtilen yazıya bağlar.
     */
    public function forPost(Post $post): static
    {
        return $this->state(fn (): array => [
            'reportable_type' => Post::class,
            'reportable_id' => $post->getKey(),
        ]);
    }

    /**
     * Bildirimi belirtilen yoruma bağlar.
     */
    public function forComment(Comment $comment): static
    {
        return $this->state(fn (): array => [
            'reportable_type' => Comment::class,
            'reportable_id' => $comment->getKey(),
        ]);
    }

    /**
     * Bildirimi belirtilen kullanıcı adına oluşturur.
     */
    public function reportedBy(User $user): static
    {
        return $this->state(fn (): array => [
            'user_id' => $user->getKey(),
        ]);
    }

    /**
     * Bildirimi inceleniyor durumuna getirir.
     */
    public function reviewing(?User $reviewer = null): static
    {
        return $this->state(fn (): array => [
            'status' => Report::STATUS_REVIEWING,
            'reviewed_by' => $reviewer?->getKey() ?? User::factory(),
            'reviewed_at' => now(),
            'resolution_note' => null,
        ]);
    }

    /**
     * Bildirimi çözümlendi durumuna getirir.
     */
    public function resolved(?User $reviewer = null): static
    {
        return $this->state(fn (): array => [
            'status' => Report::STATUS_RESOLVED,
            'reviewed_by' => $reviewer?->getKey() ?? User::factory(),
            'reviewed_at' => now(),
            'resolution_note' => fake()->sentence(),
        ]);
    }

    /**
     * Bildirimi geçersiz olarak kapatır.
     */
    public function dismissed(?User $reviewer = null): static
    {
        return $this->state(fn (): array => [
            'status' => Report::STATUS_DISMISSED,
            'reviewed_by' => $reviewer?->getKey() ?? User::factory(),
            'reviewed_at' => now(),
            'resolution_note' => fake()->sentence(),
        ]);
    }
}
