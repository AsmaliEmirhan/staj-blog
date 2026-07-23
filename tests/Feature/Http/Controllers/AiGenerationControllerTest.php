<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\AiGeneration;
use App\Models\User;
use App\Services\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

class AiGenerationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set(
            'services.gemini.model',
            'test-model'
        );
    }

    public function test_active_user_can_generate_blog_post(): void
    {
        $user = User::factory()->create();

        $this->mock(
            GeminiService::class,
            function (MockInterface $mock): void {
                $mock->shouldReceive('generateText')
                    ->once()
                    ->withArgs(
                        fn (string $prompt): bool => str_contains(
                            $prompt,
                            'Laravel, PHP'
                        )
                    )
                    ->andReturn('Üretilen test blog yazısı.');
            }
        );

        $response = $this
            ->actingAs($user)
            ->postJson(route('api.ai.generate-post'), [
                'keywords' => 'Laravel, PHP',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath(
                'message',
                'Blog yazısı başarıyla üretildi.'
            )
            ->assertJsonPath(
                'data.content',
                'Üretilen test blog yazısı.'
            );

        $generation = AiGeneration::query()->firstOrFail();

        $this->assertTrue($generation->isCompleted());
        $this->assertTrue($generation->user->is($user));
        $this->assertSame('gemini', $generation->provider);
        $this->assertSame('test-model', $generation->model);
        $this->assertSame(
            ['keywords' => 'Laravel, PHP'],
            $generation->input
        );
        $this->assertSame(
            'Üretilen test blog yazısı.',
            $generation->output
        );
    }

    public function test_guest_cannot_generate_blog_post(): void
    {
        $this->mock(
            GeminiService::class,
            function (MockInterface $mock): void {
                $mock->shouldNotReceive('generateText');
            }
        );

        $response = $this->postJson(
            route('api.ai.generate-post'),
            ['keywords' => 'Laravel']
        );

        $response->assertUnauthorized();

        $this->assertDatabaseCount('ai_generations', 0);
    }

    public function test_passive_user_cannot_generate_blog_post(): void
    {
        $user = User::factory()->passive()->create();

        $this->mock(
            GeminiService::class,
            function (MockInterface $mock): void {
                $mock->shouldNotReceive('generateText');
            }
        );

        $response = $this
            ->actingAs($user)
            ->postJson(route('api.ai.generate-post'), [
                'keywords' => 'Laravel',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseCount('ai_generations', 0);
    }

    public function test_keywords_are_required(): void
    {
        $user = User::factory()->create();

        $this->mock(
            GeminiService::class,
            function (MockInterface $mock): void {
                $mock->shouldNotReceive('generateText');
            }
        );

        $response = $this
            ->actingAs($user)
            ->postJson(route('api.ai.generate-post'), [
                'keywords' => '',
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('keywords');

        $this->assertDatabaseCount('ai_generations', 0);
    }

    public function test_failed_gemini_request_is_recorded(): void
    {
        $user = User::factory()->create();

        $this->mock(
            GeminiService::class,
            function (MockInterface $mock): void {
                $mock->shouldReceive('generateText')
                    ->once()
                    ->andThrow(
                        new RuntimeException(
                            'Gemini bağlantı hatası.'
                        )
                    );
            }
        );

        $response = $this
            ->actingAs($user)
            ->postJson(route('api.ai.generate-post'), [
                'keywords' => 'Laravel',
            ]);

        $response
            ->assertStatus(502)
            ->assertExactJson([
                'message' => 'Blog yazısı şu anda üretilemedi. Lütfen daha sonra tekrar deneyin.',
            ]);

        $generation = AiGeneration::query()->firstOrFail();

        $this->assertTrue($generation->isFailed());
        $this->assertSame(
            'AI servisi isteği tamamlayamadı.',
            $generation->error_message
        );
        $this->assertNull($generation->output);
    }
}
