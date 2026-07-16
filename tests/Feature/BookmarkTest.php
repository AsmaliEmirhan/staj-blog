<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Bookmark;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookmarkTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Bookmark kaydının kullanıcı ve yazı ilişkilerini iki yönde doğrular.
     */
    public function test_bookmark_belongs_to_user_and_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $bookmark = Bookmark::factory()
            ->forUserAndPost($user, $post)
            ->create();

        $this->assertTrue($bookmark->user->is($user));
        $this->assertTrue($bookmark->post->is($post));
        $this->assertTrue($user->bookmarks->contains($bookmark));
        $this->assertTrue($post->bookmarks->contains($bookmark));
    }

    /**
     * Kullanıcının kaydettiği yazılara ve yazıyı kaydeden
     * kullanıcılara erişilebildiğini doğrular.
     */
    public function test_many_to_many_bookmark_relations_work(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $user->bookmarkedPosts()->attach($post->id);

        $this->assertTrue(
            $user->bookmarkedPosts->contains($post)
        );

        $this->assertTrue(
            $post->bookmarkedByUsers->contains($user)
        );

        $this->assertDatabaseHas('bookmarks', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }

    /**
     * Kullanıcının aynı yazıyı ikinci kez kaydedemediğini doğrular.
     */
    public function test_user_cannot_bookmark_same_post_twice(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        Bookmark::factory()
            ->forUserAndPost($user, $post)
            ->create();

        $this->expectException(QueryException::class);

        Bookmark::factory()
            ->forUserAndPost($user, $post)
            ->create();
    }

    /**
     * Kullanıcı ve yazı alanlarının form verisiyle toplu
     * olarak atanmasının exception ile engellendiğini doğrular.
     */
    public function test_bookmark_fields_cannot_be_mass_assigned(): void
    {
        $bookmark = new Bookmark;

        $this->expectException(MassAssignmentException::class);

        $bookmark->fill([
            'user_id' => 999,
            'post_id' => 999,
        ]);
    }

    /**
     * Kullanıcı silindiğinde bookmark kaydının kaldırıldığını doğrular.
     */
    public function test_deleting_user_removes_bookmark(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        Bookmark::factory()
            ->forUserAndPost($user, $post)
            ->create();

        $user->delete();

        $this->assertDatabaseMissing('bookmarks', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }

    /**
     * Yazı soft delete edildiğinde bookmark kaydının korunduğunu,
     * kalıcı silindiğinde ise kaldırıldığını doğrular.
     */
    public function test_force_deleting_post_removes_bookmark(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        Bookmark::factory()
            ->forUserAndPost($user, $post)
            ->create();

        $post->delete();

        $this->assertDatabaseHas('bookmarks', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);

        $post->forceDelete();

        $this->assertDatabaseMissing('bookmarks', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }
}
