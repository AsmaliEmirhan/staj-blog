<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Yorumun yazı ve kullanıcı ilişkilerini iki yönde doğrular.
     */
    public function test_comment_belongs_to_post_and_user(): void
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();

        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        $this->assertTrue($comment->post->is($post));
        $this->assertTrue($comment->user->is($user));
        $this->assertTrue($post->comments->contains($comment));
        $this->assertTrue($user->comments->contains($comment));
    }

    /**
     * Bir yorumun başka bir yoruma cevap olabildiğini doğrular.
     */
    public function test_comment_can_have_replies(): void
    {
        $parent = Comment::factory()->create();

        $reply = Comment::factory()
            ->replyTo($parent)
            ->create();

        $this->assertTrue($reply->parent->is($parent));
        $this->assertTrue($parent->replies->contains($reply));
        $this->assertSame($parent->post_id, $reply->post_id);
    }

    /**
     * Approved scope'un yalnızca onaylanan yorumları getirdiğini doğrular.
     */
    public function test_approved_scope_returns_only_approved_comments(): void
    {
        $approvedComment = Comment::factory()
            ->approved()
            ->create();

        Comment::factory()->create();
        Comment::factory()->rejected()->create();

        $comments = Comment::query()->approved()->get();

        $this->assertCount(1, $comments);
        $this->assertTrue($comments->contains($approvedComment));
    }

    /**
     * Ana yorum filtresini ve tarih sıralamasını doğrular.
     */
    public function test_top_level_comments_are_ordered_oldest_first(): void
    {
        $olderComment = Comment::factory()->create([
            'created_at' => now()->subDay(),
        ]);

        $newerComment = Comment::factory()->create([
            'created_at' => now(),
        ]);

        Comment::factory()
            ->replyTo($olderComment)
            ->create();

        $commentIds = Comment::query()
            ->topLevel()
            ->oldestFirst()
            ->pluck('id')
            ->all();

        $this->assertSame([
            $olderComment->id,
            $newerComment->id,
        ], $commentIds);
    }

    /**
     * Yöneticinin yorumu onaylayabildiğini doğrular.
     */
    public function test_admin_can_approve_comment(): void
    {
        $moderator = User::factory()->admin()->create();
        $comment = Comment::factory()->create();

        $result = $comment->approve($moderator);
        $comment->refresh();

        $this->assertTrue($result);
        $this->assertTrue($comment->isApproved());
        $this->assertSame($moderator->id, $comment->moderated_by);
        $this->assertNotNull($comment->moderated_at);
        $this->assertTrue($comment->moderator->is($moderator));
        $this->assertTrue(
            $moderator->moderatedComments->contains($comment)
        );
    }

    /**
     * Yöneticinin yorumu reddedebildiğini doğrular.
     */
    public function test_admin_can_reject_comment(): void
    {
        $moderator = User::factory()->admin()->create();
        $comment = Comment::factory()->create();

        $result = $comment->reject($moderator);
        $comment->refresh();

        $this->assertTrue($result);
        $this->assertSame(
            Comment::STATUS_REJECTED,
            $comment->status
        );
        $this->assertFalse($comment->isApproved());
        $this->assertSame($moderator->id, $comment->moderated_by);
        $this->assertNotNull($comment->moderated_at);
    }

    /**
     * Kritik yorum alanlarının form verisiyle değiştirilemediğini doğrular.
     */
    public function test_protected_fields_cannot_be_mass_assigned(): void
    {
        $comment = new Comment;

        $comment->fill([
            'content' => 'Test yorumu',
            'parent_id' => 123,
            'post_id' => 999,
            'user_id' => 999,
            'status' => Comment::STATUS_APPROVED,
            'moderated_by' => 999,
            'moderated_at' => now(),
        ]);

        $attributes = $comment->getAttributes();

        $this->assertSame('Test yorumu', $comment->content);
        $this->assertSame(123, $comment->parent_id);
        $this->assertArrayNotHasKey('post_id', $attributes);
        $this->assertArrayNotHasKey('user_id', $attributes);
        $this->assertArrayNotHasKey('status', $attributes);
        $this->assertArrayNotHasKey('moderated_by', $attributes);
        $this->assertArrayNotHasKey('moderated_at', $attributes);
    }

    /**
     * Silinen yorumların soft delete ile korunduğunu doğrular.
     */
    public function test_comments_are_soft_deleted(): void
    {
        $comment = Comment::factory()->create();

        $comment->delete();

        $this->assertSoftDeleted('comments', [
            'id' => $comment->id,
        ]);

        $this->assertNull(
            Comment::query()->find($comment->id)
        );

        $this->assertNotNull(
            Comment::withTrashed()->find($comment->id)
        );
    }
}
