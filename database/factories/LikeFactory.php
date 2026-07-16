<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Like>
 */
class LikeFactory extends Factory
{
    /**
     * Testlerde kullanılacak örnek beğeni kaydını üretir.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Beğeniyi yapan test kullanıcısını oluşturur.
            'user_id' => User::factory(),

            // Beğenilecek test blog yazısını oluşturur.
            'post_id' => Post::factory(),
        ];
    }

    /**
     * Belirli bir kullanıcı ve yazı için beğeni üretir.
     */
    public function forUserAndPost(User $user, Post $post): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }
}
