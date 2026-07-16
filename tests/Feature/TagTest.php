<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Slug verilmediğinde etiket adından otomatik üretildiğini doğrular.
     */
    public function test_slug_is_generated_from_tag_name(): void
    {
        $tag = Tag::factory()->create([
            'name' => 'Yapay Zekâ',
            'slug' => null,
        ]);

        $this->assertSame('yapay-zeka', $tag->slug);

        $this->assertDatabaseHas('tags', [
            'name' => 'Yapay Zekâ',
            'slug' => 'yapay-zeka',
        ]);
    }

    /**
     * Bir yazıya birden fazla etiket bağlanabildiğini doğrular.
     */
    public function test_post_can_have_multiple_tags(): void
    {
        $post = Post::factory()->create();

        $firstTag = Tag::factory()
            ->named('Laravel')
            ->create();

        $secondTag = Tag::factory()
            ->named('PHP')
            ->create();

        $post->tags()->attach([
            $firstTag->id,
            $secondTag->id,
        ]);

        $this->assertCount(2, $post->fresh()->tags);

        $this->assertTrue(
            $firstTag->posts()->whereKey($post->id)->exists()
        );

        $this->assertDatabaseHas('post_tag', [
            'post_id' => $post->id,
            'tag_id' => $firstTag->id,
        ]);

        $this->assertDatabaseHas('post_tag', [
            'post_id' => $post->id,
            'tag_id' => $secondTag->id,
        ]);
    }

    /**
     * Aynı etiketin aynı yazıya ikinci kez bağlanamadığını doğrular.
     */
    public function test_same_tag_cannot_be_attached_twice(): void
    {
        $post = Post::factory()->create();
        $tag = Tag::factory()->create();

        $post->tags()->attach($tag->id);

        $this->expectException(QueryException::class);

        $post->tags()->attach($tag->id);
    }

    /**
     * Etiket silindiğinde pivot bağlantısının da silindiğini doğrular.
     */
    public function test_deleting_tag_removes_pivot_record(): void
    {
        $post = Post::factory()->create();
        $tag = Tag::factory()->create();

        $post->tags()->attach($tag->id);

        $this->assertDatabaseHas('post_tag', [
            'post_id' => $post->id,
            'tag_id' => $tag->id,
        ]);

        $tag->delete();

        $this->assertDatabaseMissing('post_tag', [
            'post_id' => $post->id,
            'tag_id' => $tag->id,
        ]);
    }

    /**
     * Rota model bağlamasında ID yerine slug kullanıldığını doğrular.
     */
    public function test_route_key_uses_slug(): void
    {
        $tag = new Tag;

        $this->assertSame('slug', $tag->getRouteKeyName());
    }
}
