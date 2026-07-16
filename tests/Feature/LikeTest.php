<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Beğeninin kullanıcı ve yazı ilişkilerini iki yönde doğrular.
     */
    public function test_like_belongs_to_user_and_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $like = Like::factory()
            ->forUserAndPost($user, $post)
            ->create();

        $this->assertTrue($like->user->is($user));
        $this->assertTrue($like->post->is($post));
        $this->assertTrue($user->likes->contains($like));
        $this->assertTrue($post->likes->contains($like));
    }

    /**
     * Kullanıcının beğendiği yazılara ve yazıyı beğenen
     * kullanıcılara erişilebildiğini doğrular.
     */
    public function test_many_to_many_like_relations_work(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $user->likedPosts()->attach($post->id);

        $this->assertTrue($user->likedPosts->contains($post));
        $this->assertTrue($post->likedByUsers->contains($user));

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }

    /**
     * Kullanıcının aynı yazıyı ikinci kez beğenemediğini doğrular.
     */
    public function test_user_cannot_like_same_post_twice(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        Like::factory()
            ->forUserAndPost($user, $post)
            ->create();

        $this->expectException(QueryException::class);

        Like::factory()
            ->forUserAndPost($user, $post)
            ->create();
    }

    /**
     * Kullanıcı ve yazı alanlarının form verisiyle toplu
     * olarak atanmasının exception ile engellendiğini doğrular.
     */
    public function test_like_fields_cannot_be_mass_assigned(): void
    {
        $like = new Like;

        $this->expectException(MassAssignmentException::class);

        $like->fill([
            'user_id' => 999,
            'post_id' => 999,
        ]);
    }

    /**
     * Kullanıcı silindiğinde beğeni kaydının kaldırıldığını doğrular.
     */
    public function test_deleting_user_removes_like(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        Like::factory()
            ->forUserAndPost($user, $post)
            ->create();

        $user->delete();

        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }

    /**
     * Yazı soft delete edildiğinde beğeninin korunduğunu,
     * kalıcı silindiğinde ise kaldırıldığını doğrular.
     */
    public function test_force_deleting_post_removes_like(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        Like::factory()
            ->forUserAndPost($user, $post)
            ->create();

        $post->delete();

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);

        $post->forceDelete();

        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }
}
