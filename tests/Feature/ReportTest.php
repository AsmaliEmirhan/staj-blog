<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Yazı bildiriminin kullanıcı ve polymorphic ilişkilerini doğrular.
     */
    public function test_post_report_belongs_to_user_and_reportable(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $report = Report::factory()
            ->reportedBy($user)
            ->forPost($post)
            ->create();

        $this->assertTrue($report->user->is($user));
        $this->assertTrue($report->reportable->is($post));
        $this->assertTrue($user->reports->contains($report));
        $this->assertTrue($post->reports->contains($report));
    }

    /**
     * Yorumların da bildirilebilir olduğunu doğrular.
     */
    public function test_comment_can_be_reported(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        $report = Report::factory()
            ->reportedBy($user)
            ->forComment($comment)
            ->create();

        $this->assertTrue($report->reportable->is($comment));
        $this->assertTrue($comment->reports->contains($report));
    }

    /**
     * Aynı kullanıcının aynı içeriği iki kez bildiremeyeceğini doğrular.
     */
    public function test_user_cannot_report_same_content_twice(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        Report::factory()
            ->reportedBy($user)
            ->forPost($post)
            ->create();

        $this->expectException(QueryException::class);

        Report::factory()
            ->reportedBy($user)
            ->forPost($post)
            ->create();
    }

    /**
     * Pending scope metodunun yalnızca bekleyen bildirimleri getirdiğini doğrular.
     */
    public function test_pending_scope_returns_only_pending_reports(): void
    {
        $pendingReport = Report::factory()->create();
        Report::factory()->resolved()->create();

        $reports = Report::query()->pending()->get();

        $this->assertCount(1, $reports);
        $this->assertTrue($reports->contains($pendingReport));
        $this->assertTrue($reports->every(
            fn (Report $report): bool => $report->status === Report::STATUS_PENDING
        ));
    }

    /**
     * Yöneticinin bildirimi incelemeye alabildiğini doğrular.
     */
    public function test_reviewer_can_mark_report_as_reviewing(): void
    {
        $reviewer = User::factory()->create();
        $report = Report::factory()->create();

        $this->assertTrue($report->markAsReviewing($reviewer));

        $report->refresh();

        $this->assertSame(Report::STATUS_REVIEWING, $report->status);
        $this->assertTrue($report->reviewer->is($reviewer));
        $this->assertNotNull($report->reviewed_at);
        $this->assertTrue($reviewer->reviewedReports->contains($report));
    }

    /**
     * Yöneticinin bildirimi çözüm notuyla kapatabildiğini doğrular.
     */
    public function test_reviewer_can_resolve_report(): void
    {
        $reviewer = User::factory()->create();
        $report = Report::factory()->create();

        $this->assertTrue($report->resolve($reviewer, 'İçerik kaldırıldı.'));

        $report->refresh();

        $this->assertSame(Report::STATUS_RESOLVED, $report->status);
        $this->assertSame('İçerik kaldırıldı.', $report->resolution_note);
        $this->assertTrue($report->reviewer->is($reviewer));
        $this->assertNotNull($report->reviewed_at);
    }

    /**
     * Yöneticinin geçersiz bildirimi reddedebildiğini doğrular.
     */
    public function test_reviewer_can_dismiss_report(): void
    {
        $reviewer = User::factory()->create();
        $report = Report::factory()->create();

        $this->assertTrue($report->dismiss($reviewer, 'İhlal bulunamadı.'));

        $report->refresh();

        $this->assertSame(Report::STATUS_DISMISSED, $report->status);
        $this->assertSame('İhlal bulunamadı.', $report->resolution_note);
        $this->assertTrue($report->reviewer->is($reviewer));
    }

    /**
     * Sistem tarafından yönetilen alanların toplu atanamadığını doğrular.
     */
    public function test_protected_report_fields_cannot_be_mass_assigned(): void
    {
        $report = new Report([
            'user_id' => 1,
            'reportable_type' => Post::class,
            'reportable_id' => 1,
            'reason' => Report::REASON_SPAM,
            'description' => 'Şüpheli içerik.',
            'status' => Report::STATUS_RESOLVED,
            'reviewed_by' => 2,
        ]);

        $this->assertSame(Report::REASON_SPAM, $report->reason);
        $this->assertSame('Şüpheli içerik.', $report->description);
        $this->assertNull($report->user_id);
        $this->assertNull($report->reportable_type);
        $this->assertNull($report->reportable_id);
        $this->assertNull($report->status);
        $this->assertNull($report->reviewed_by);
    }
}
