<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Bookmark;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bookmark>
 */
class BookmarkFactory extends Factory
{
    /**
     * Testlerde kullanılacak örnek bookmark kaydını üretir.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Yazıyı kaydeden test kullanıcısını oluşturur.
            'user_id' => User::factory(),

            // Kaydedilecek test blog yazısını oluşturur.
            'post_id' => Post::factory(),
        ];
    }

    /**
     * Belirli bir kullanıcı ve yazı için bookmark üretir.
     */
    public function forUserAndPost(User $user, Post $post): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }
}
