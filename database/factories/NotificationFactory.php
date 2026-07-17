<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Notification;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Varsayılan olarak okunmamış bir yazı bildirimi üretir.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'actor_id' => User::factory(),
            'type' => Notification::TYPE_POST_LIKED,
            'subject_type' => Post::class,
            'subject_id' => Post::factory(),
            'title' => 'Yazınız beğenildi',
            'message' => fake()->sentence(),
            'data' => [
                'source' => 'application',
            ],
            'read_at' => null,
        ];
    }

    /**
     * Bildirimi belirtilen kullanıcıya gönderir.
     */
    public function forRecipient(User $user): static
    {
        return $this->state(fn (): array => [
            'user_id' => $user->getKey(),
        ]);
    }

    /**
     * Bildirimi tetikleyen kullanıcıyı belirler.
     */
    public function fromActor(?User $actor): static
    {
        return $this->state(fn (): array => [
            'actor_id' => $actor?->getKey(),
        ]);
    }

    /**
     * Bildirimi belirtilen yazıyla ilişkilendirir.
     */
    public function forPost(Post $post): static
    {
        return $this->state(fn (): array => [
            'subject_type' => Post::class,
            'subject_id' => $post->getKey(),
        ]);
    }

    /**
     * Bildirimi belirtilen yorumla ilişkilendirir.
     */
    public function forComment(Comment $comment): static
    {
        return $this->state(fn (): array => [
            'subject_type' => Comment::class,
            'subject_id' => $comment->getKey(),
        ]);
    }

    /**
     * Bildirimi yorum bildirimi haline getirir.
     */
    public function newComment(): static
    {
        return $this->state(fn (): array => [
            'type' => Notification::TYPE_NEW_COMMENT,
            'title' => 'Yazınıza yeni yorum yapıldı',
        ]);
    }

    /**
     * Bildirimi sistem bildirimi haline getirir.
     */
    public function system(): static
    {
        return $this->state(fn (): array => [
            'actor_id' => null,
            'type' => Notification::TYPE_SYSTEM,
            'subject_type' => null,
            'subject_id' => null,
            'title' => 'Sistem bildirimi',
        ]);
    }

    /**
     * Bildirimi okunmuş duruma getirir.
     */
    public function read(): static
    {
        return $this->state(fn (): array => [
            'read_at' => now(),
        ]);
    }

    /**
     * Bildirimi okunmamış duruma getirir.
     */
    public function unread(): static
    {
        return $this->state(fn (): array => [
            'read_at' => null,
        ]);
    }
}
