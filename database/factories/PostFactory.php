<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Testlerde kullanılacak varsayılan blog yazısını üretir.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(6);

        return [
            // Gerekli olduğunda ilişkili kullanıcıyı otomatik oluşturur.
            'user_id' => User::factory(),

            // Yazı için ilişkili kategori oluşturur.
            'category_id' => Category::factory(),

            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->paragraph(),
            'content' => implode("\n\n", fake()->paragraphs(5)),
            'featured_image' => null,
            'status' => Post::STATUS_DRAFT,
            'is_ai_generated' => false,
            'ai_keywords' => null,
            'published_at' => null,
            'view_count' => fake()->numberBetween(0, 1000),
        ];
    }

    /**
     * Yayın tarihi gelmiş, ziyaretçilere açık yazı üretir.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Arşivlenmiş yazı üretir.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Post::STATUS_ARCHIVED,
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Yapay zekâ yardımıyla oluşturulmuş yazı üretir.
     *
     * @param  list<string>  $keywords
     */
    public function aiGenerated(
        array $keywords = ['yapay zekâ', 'yazılım', 'teknoloji']
    ): static {
        return $this->state(fn (array $attributes): array => [
            'is_ai_generated' => true,
            'ai_keywords' => $keywords,
        ]);
    }

    /**
     * Herhangi bir kategoriye bağlı olmayan yazı üretir.
     */
    public function withoutCategory(): static
    {
        return $this->state(fn (array $attributes): array => [
            'category_id' => null,
        ]);
    }
}
