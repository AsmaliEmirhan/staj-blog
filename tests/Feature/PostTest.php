<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Yazının kullanıcı ve kategori ilişkilerini doğrular.
     */
    public function test_post_belongs_to_an_author_and_category(): void
    {
        $author = User::factory()->create();
        $category = Category::factory()->create();

        $post = Post::factory()->create([
            'user_id' => $author->id,
            'category_id' => $category->id,
        ]);

        $this->assertTrue($post->author->is($author));
        $this->assertTrue($post->category->is($category));
    }

    /**
     * Aynı başlığa sahip yazılara benzersiz slug üretildiğini doğrular.
     */
    public function test_duplicate_titles_receive_unique_slugs(): void
    {
        $firstPost = Post::factory()->create([
            'title' => 'Laravel ile Blog Geliştirme',
            'slug' => null,
        ]);

        $secondPost = Post::factory()->create([
            'title' => 'Laravel ile Blog Geliştirme',
            'slug' => null,
        ]);

        $this->assertSame(
            'laravel-ile-blog-gelistirme',
            $firstPost->slug
        );

        $this->assertSame(
            'laravel-ile-blog-gelistirme-2',
            $secondPost->slug
        );
    }

    /**
     * Published scope'un yalnızca yayın zamanı gelmiş yazıları döndürdüğünü doğrular.
     */
    public function test_published_scope_returns_only_visible_posts(): void
    {
        $publishedPost = Post::factory()->published()->create();

        Post::factory()->create([
            'status' => Post::STATUS_DRAFT,
            'published_at' => null,
        ]);

        Post::factory()->create([
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->addDay(),
        ]);

        $posts = Post::query()->published()->get();

        $this->assertCount(1, $posts);
        $this->assertTrue($posts->contains($publishedPost));
    }

    /**
     * Post alanlarının doğru PHP türlerine dönüştüğünü doğrular.
     */
    public function test_post_fields_are_cast_to_correct_types(): void
    {
        $post = new Post;

        $post->forceFill([
            'is_ai_generated' => 1,
            'ai_keywords' => ['php', 'laravel'],
            'published_at' => '2026-07-16 12:00:00',
            'view_count' => '25',
        ]);

        $this->assertTrue($post->is_ai_generated);
        $this->assertSame(['php', 'laravel'], $post->ai_keywords);
        $this->assertInstanceOf(Carbon::class, $post->published_at);
        $this->assertSame(25, $post->view_count);
    }

    /**
     * Kritik alanların form verisiyle değiştirilemediğini doğrular.
     */
    public function test_protected_fields_cannot_be_mass_assigned(): void
    {
        $post = new Post;

        $post->fill([
            'title' => 'Güvenlik Testi',
            'user_id' => 999,
            'status' => Post::STATUS_PUBLISHED,
            'is_ai_generated' => true,
            'view_count' => 999999,
        ]);

        $attributes = $post->getAttributes();

        $this->assertArrayNotHasKey('user_id', $attributes);
        $this->assertArrayNotHasKey('status', $attributes);
        $this->assertArrayNotHasKey('is_ai_generated', $attributes);
        $this->assertArrayNotHasKey('view_count', $attributes);
    }

    /**
     * Silinen yazıların soft delete ile korunmasını doğrular.
     */
    public function test_posts_are_soft_deleted(): void
    {
        $post = Post::factory()->create();

        $post->delete();

        $this->assertSoftDeleted('posts', [
            'id' => $post->id,
        ]);

        $this->assertNull(Post::query()->find($post->id));
        $this->assertNotNull(
            Post::withTrashed()->find($post->id)
        );
    }

    /**
     * Yazının ziyaretçilere açık olup olmadığını doğru belirlediğini doğrular.
     */
    public function test_it_can_detect_published_posts(): void
    {
        $publishedPost = Post::factory()->make([
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->subMinute(),
        ]);

        $scheduledPost = Post::factory()->make([
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->addDay(),
        ]);

        $draftPost = Post::factory()->make([
            'status' => Post::STATUS_DRAFT,
            'published_at' => null,
        ]);

        $this->assertTrue($publishedPost->isPublished());
        $this->assertFalse($scheduledPost->isPublished());
        $this->assertFalse($draftPost->isPublished());
    }

    /**
     * Bir kullanıcının kendisine ait bütün yazıları getirebildiğini doğrular.
     */
    public function test_user_has_many_posts(): void
    {
        $user = User::factory()->create();

        Post::factory()
            ->count(2)
            ->create([
                'user_id' => $user->id,
            ]);

        $posts = $user->posts;

        $this->assertCount(2, $posts);

        $this->assertTrue(
            $posts->every(
                fn (Post $post): bool => $post->user_id === $user->id
            )
        );
    }

    /**
     * Bir kategorinin kendisine ait bütün yazıları getirebildiğini doğrular.
     */
    public function test_category_has_many_posts(): void
    {
        $category = Category::factory()->create();

        Post::factory()
            ->count(2)
            ->create([
                'category_id' => $category->id,
            ]);

        $posts = $category->posts;

        $this->assertCount(2, $posts);

        $this->assertTrue(
            $posts->every(
                fn (Post $post): bool => $post->category_id === $category->id
            )
        );
    }

    /**
     * Rota model bağlamasında ID yerine slug kullanıldığını doğrular.
     */
    public function test_route_key_uses_slug(): void
    {
        $post = new Post;

        $this->assertSame('slug', $post->getRouteKeyName());
    }
}
