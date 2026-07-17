<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AiGeneration;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiGeneration>
 */
class AiGenerationFactory extends Factory
{
    /**
     * Varsayılan olarak bekleyen bir makale üretim isteği oluşturur.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'post_id' => Post::factory(),
            'type' => AiGeneration::TYPE_ARTICLE,
            'provider' => 'openai',
            'model' => 'gpt-4.1-mini',
            'request_id' => null,
            'prompt' => fake()->paragraph(),
            'input' => [
                'language' => 'tr',
                'tone' => 'professional',
            ],
            'output' => null,
            'status' => AiGeneration::STATUS_PENDING,
            'prompt_tokens' => 0,
            'completion_tokens' => 0,
            'total_tokens' => 0,
            'estimated_cost' => null,
            'duration_ms' => null,
            'error_message' => null,
            'started_at' => null,
            'completed_at' => null,
        ];
    }

    /**
     * AI üretimini belirtilen kullanıcıya bağlar.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (): array => [
            'user_id' => $user->getKey(),
        ]);
    }

    /**
     * AI üretimini belirtilen yazıya bağlar.
     */
    public function forPost(Post $post): static
    {
        return $this->state(fn (): array => [
            'post_id' => $post->getKey(),
        ]);
    }

    /**
     * Yazıya bağlı olmayan bağımsız bir üretim oluşturur.
     */
    public function withoutPost(): static
    {
        return $this->state(fn (): array => [
            'post_id' => null,
        ]);
    }

    /**
     * Başlık üretimi durumu oluşturur.
     */
    public function titleGeneration(): static
    {
        return $this->state(fn (): array => [
            'type' => AiGeneration::TYPE_TITLE,
            'prompt' => 'Verilen konu için etkileyici bir blog başlığı üret.',
        ]);
    }

    /**
     * İşlemi devam ediyor durumuna getirir.
     */
    public function processing(): static
    {
        return $this->state(fn (): array => [
            'status' => AiGeneration::STATUS_PROCESSING,
            'started_at' => now(),
        ]);
    }

    /**
     * Başarıyla tamamlanmış bir AI üretimi oluşturur.
     */
    public function completed(): static
    {
        $promptTokens = fake()->numberBetween(50, 300);
        $completionTokens = fake()->numberBetween(100, 1000);

        return $this->state(fn (): array => [
            'request_id' => fake()->unique()->uuid(),
            'output' => fake()->paragraphs(3, true),
            'status' => AiGeneration::STATUS_COMPLETED,
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $promptTokens + $completionTokens,
            'estimated_cost' => fake()->randomFloat(6, 0.000001, 0.100000),
            'duration_ms' => fake()->numberBetween(100, 10000),
            'error_message' => null,
            'started_at' => now()->subSecond(),
            'completed_at' => now(),
        ]);
    }

    /**
     * Başarısız olmuş bir AI üretimi oluşturur.
     */
    public function failed(): static
    {
        return $this->state(fn (): array => [
            'request_id' => fake()->unique()->uuid(),
            'output' => null,
            'status' => AiGeneration::STATUS_FAILED,
            'duration_ms' => fake()->numberBetween(100, 10000),
            'error_message' => 'AI sağlayıcısından yanıt alınamadı.',
            'started_at' => now()->subSecond(),
            'completed_at' => now(),
        ]);
    }
}
