<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Views;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiGenerationFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_view_ai_generation_interface(): void
    {
        $user = User::factory()->create([
            'status' => User::STATUS_ACTIVE,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('posts.create'));

        $response
            ->assertOk()
            ->assertSee('AI ile içerik üret')
            ->assertSee('id="ai-blog-generator"', false)
            ->assertSee('id="ai-keywords"', false)
            ->assertSee('id="generate-blog-button"', false)
            ->assertSee(
                'data-endpoint="'.route('api.ai.generate-post').'"',
                false
            )
            ->assertSee(
                asset('js/ai-blog-generator.js'),
                false
            );
    }
}
