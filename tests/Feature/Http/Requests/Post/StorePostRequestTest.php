<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Requests\Post;

use App\Http\Requests\Post\StorePostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class StorePostRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        /*
        * Form Request doğrulama hatalarının yönlendirme yerine
        * 422 durum koduyla JSON olarak dönmesini sağlar.
        */
        $this->withHeader('Accept', 'application/json');

        /*
        * StorePostRequest sınıfını controller oluşturmadan test edebilmek için
        * yalnızca test ortamında kullanılan geçici bir rota tanımlar.
        */
        Route::post('/test/store-post-request', function (
            StorePostRequest $request
        ) {
            return response()->json($request->validated());
        });
    }

    public function test_guest_cannot_create_post(): void
    {
        $response = $this->postJson(
            '/test/store-post-request',
            $this->validPayload()
        );

        $response->assertForbidden();
    }

    public function test_inactive_user_cannot_create_post(): void
    {
        $user = User::factory()->passive()->create();

        $response = $this
            ->actingAs($user)
            ->postJson(
                '/test/store-post-request',
                $this->validPayload()
            );

        $response->assertForbidden();
    }

    public function test_active_user_can_submit_valid_post_data(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $response = $this
            ->actingAs($user)
            ->postJson('/test/store-post-request', [
                ...$this->validPayload(),
                'category_id' => $category->id,
                'tag_ids' => $tags->modelKeys(),
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('category_id', $category->id)
            ->assertJsonPath('status', Post::STATUS_DRAFT)
            ->assertJsonPath('tag_ids', $tags->modelKeys());
    }

    public function test_post_data_is_trimmed_and_empty_excerpt_becomes_null(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->postJson('/test/store-post-request', [
                'title' => '  Laravel ile Güvenli Blog  ',
                'excerpt' => '   ',
                'content' => '  '.$this->validContent().'  ',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('title', 'Laravel ile Güvenli Blog')
            ->assertJsonPath('excerpt', null)
            ->assertJsonPath('content', $this->validContent())
            ->assertJsonPath('status', Post::STATUS_DRAFT);
    }

    public function test_required_fields_and_length_limits_are_validated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->postJson('/test/store-post-request', [
                'title' => 'A',
                'content' => 'Kısa içerik',
                'excerpt' => str_repeat('a', 1001),
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'title',
                'excerpt',
                'content',
            ]);
    }

    public function test_published_post_requires_published_at(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->postJson('/test/store-post-request', [
                ...$this->validPayload(),
                'status' => Post::STATUS_PUBLISHED,
                'published_at' => null,
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('published_at');
    }

    public function test_invalid_category_tags_and_status_are_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->postJson('/test/store-post-request', [
                ...$this->validPayload(),
                'category_id' => 999999,
                'status' => Post::STATUS_ARCHIVED,
                'tag_ids' => [999998, 999998],
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'category_id',
                'status',
                'tag_ids.0',
                'tag_ids.1',
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'title' => 'Laravel ile Güvenli Blog Geliştirme',
            'excerpt' => 'Laravel doğrulama yapısını anlatan örnek yazı.',
            'content' => $this->validContent(),
        ];
    }

    private function validContent(): string
    {
        return 'Bu örnek blog içeriği doğrulama için gereken en az elli karakter sınırını güvenli biçimde karşılamaktadır.';
    }
}
