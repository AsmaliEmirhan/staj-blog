<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Requests\Post;

use App\Http\Requests\Post\UpdatePostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class UpdatePostRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Accept', 'application/json');

        /*
         * UpdatePostRequest sınıfını controller oluşturmadan test etmek için
         * yalnızca test ortamında kullanılan geçici rota tanımlar.
         */
        Route::put('/test/update-post-request/{post}', function (
            UpdatePostRequest $request,
            Post $post
        ) {
            return response()->json($request->validated());
        })->middleware(SubstituteBindings::class);
    }

    public function test_guest_cannot_update_post(): void
    {
        $post = Post::factory()->create();

        $response = $this->putJson(
            "/test/update-post-request/{$post->slug}",
            $this->validPayload()
        );

        $response->assertForbidden();
    }

    public function test_inactive_owner_cannot_update_post(): void
    {
        $user = User::factory()->passive()->create();
        $post = Post::factory()->for($user, 'author')->create();

        $response = $this
            ->actingAs($user)
            ->putJson(
                "/test/update-post-request/{$post->slug}",
                $this->validPayload()
            );

        $response->assertForbidden();
    }

    public function test_another_user_cannot_update_post(): void
    {
        $owner = User::factory()->create();
        $anotherUser = User::factory()->create();
        $post = Post::factory()->for($owner, 'author')->create();

        $response = $this
            ->actingAs($anotherUser)
            ->putJson(
                "/test/update-post-request/{$post->slug}",
                $this->validPayload()
            );

        $response->assertForbidden();
    }

    public function test_owner_can_submit_valid_update_data(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user, 'author')->create();
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $response = $this
            ->actingAs($user)
            ->putJson("/test/update-post-request/{$post->slug}", [
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

    public function test_active_admin_can_update_another_users_post(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->create();

        $response = $this
            ->actingAs($admin)
            ->putJson(
                "/test/update-post-request/{$post->slug}",
                $this->validPayload()
            );

        $response->assertOk();
    }

    public function test_update_data_is_trimmed_and_empty_excerpt_becomes_null(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user, 'author')->create();

        $response = $this
            ->actingAs($user)
            ->putJson("/test/update-post-request/{$post->slug}", [
                'title' => '  Güncellenmiş Blog Yazısı  ',
                'excerpt' => '   ',
                'content' => '  '.$this->validContent().'  ',
                'status' => Post::STATUS_DRAFT,
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('title', 'Güncellenmiş Blog Yazısı')
            ->assertJsonPath('excerpt', null)
            ->assertJsonPath('content', $this->validContent());
    }

    public function test_published_post_requires_published_at(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user, 'author')->create();

        $response = $this
            ->actingAs($user)
            ->putJson("/test/update-post-request/{$post->slug}", [
                ...$this->validPayload(),
                'status' => Post::STATUS_PUBLISHED,
                'published_at' => null,
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('published_at');
    }

    public function test_archived_status_is_accepted(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user, 'author')->create();

        $response = $this
            ->actingAs($user)
            ->putJson("/test/update-post-request/{$post->slug}", [
                ...$this->validPayload(),
                'status' => Post::STATUS_ARCHIVED,
                'published_at' => now()->toDateTimeString(),
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', Post::STATUS_ARCHIVED);
    }

    public function test_invalid_category_and_tags_are_rejected(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user, 'author')->create();

        $response = $this
            ->actingAs($user)
            ->putJson("/test/update-post-request/{$post->slug}", [
                ...$this->validPayload(),
                'category_id' => 999999,
                'tag_ids' => [999998, 999998],
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'category_id',
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
            'title' => 'Laravel Blog Yazısını Güncelleme',
            'excerpt' => 'Blog yazısı güncelleme işlemini anlatan örnek metin.',
            'content' => $this->validContent(),
            'status' => Post::STATUS_DRAFT,
        ];
    }

    private function validContent(): string
    {
        return 'Bu örnek blog içeriği doğrulama için gereken en az elli karakter sınırını güvenli biçimde karşılamaktadır.';
    }
}
