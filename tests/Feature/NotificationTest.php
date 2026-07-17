<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Notification;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Bildirimin alıcı, aktör ve yazı ilişkilerini doğrular.
     */
    public function test_notification_belongs_to_recipient_actor_and_post(): void
    {
        $recipient = User::factory()->create();
        $actor = User::factory()->create();
        $post = Post::factory()->create();

        $notification = Notification::factory()
            ->forRecipient($recipient)
            ->fromActor($actor)
            ->forPost($post)
            ->create();

        $this->assertTrue($notification->user->is($recipient));
        $this->assertTrue($notification->actor->is($actor));
        $this->assertTrue($notification->subject->is($post));
        $this->assertTrue($recipient->receivedNotifications->contains($notification));
        $this->assertTrue($actor->triggeredNotifications->contains($notification));
        $this->assertTrue($post->notifications->contains($notification));
    }

    /**
     * Yorumların bildirim konusu olabildiğini doğrular.
     */
    public function test_comment_can_be_notification_subject(): void
    {
        $comment = Comment::factory()->create();

        $notification = Notification::factory()
            ->forComment($comment)
            ->newComment()
            ->create();

        $this->assertTrue($notification->subject->is($comment));
        $this->assertTrue($comment->notifications->contains($notification));
        $this->assertSame(Notification::TYPE_NEW_COMMENT, $notification->type);
    }

    /**
     * Sistem bildirimlerinin aktör ve konu olmadan oluşturulabildiğini doğrular.
     */
    public function test_system_notification_has_no_actor_or_subject(): void
    {
        $notification = Notification::factory()
            ->system()
            ->create();

        $this->assertSame(Notification::TYPE_SYSTEM, $notification->type);
        $this->assertNull($notification->actor_id);
        $this->assertNull($notification->subject_type);
        $this->assertNull($notification->subject_id);
    }

    /**
     * Okunmuş ve okunmamış scope metotlarını doğrular.
     */
    public function test_read_and_unread_scopes_filter_notifications(): void
    {
        $unreadNotification = Notification::factory()->unread()->create();
        $readNotification = Notification::factory()->read()->create();

        $unreadNotifications = Notification::query()->unread()->get();
        $readNotifications = Notification::query()->read()->get();

        $this->assertCount(1, $unreadNotifications);
        $this->assertTrue($unreadNotifications->contains($unreadNotification));
        $this->assertCount(1, $readNotifications);
        $this->assertTrue($readNotifications->contains($readNotification));
    }

    /**
     * Bildirimin okundu olarak işaretlenebildiğini doğrular.
     */
    public function test_notification_can_be_marked_as_read(): void
    {
        $notification = Notification::factory()->unread()->create();

        $this->assertTrue($notification->isUnread());
        $this->assertTrue($notification->markAsRead());

        $notification->refresh();

        $this->assertTrue($notification->isRead());
        $this->assertNotNull($notification->read_at);
    }

    /**
     * Bildirimin yeniden okunmamış yapılabildiğini doğrular.
     */
    public function test_notification_can_be_marked_as_unread(): void
    {
        $notification = Notification::factory()->read()->create();

        $this->assertTrue($notification->isRead());
        $this->assertTrue($notification->markAsUnread());

        $notification->refresh();

        $this->assertTrue($notification->isUnread());
        $this->assertNull($notification->read_at);
    }

    /**
     * Bildirim verilerinin doğru PHP türlerine dönüştürüldüğünü doğrular.
     */
    public function test_notification_fields_are_cast_to_correct_types(): void
    {
        $notification = Notification::factory()->read()->create([
            'data' => [
                'post_id' => 15,
                'important' => true,
            ],
        ]);

        $this->assertIsArray($notification->data);
        $this->assertSame(15, $notification->data['post_id']);
        $this->assertTrue($notification->data['important']);
        $this->assertInstanceOf(
            Carbon::class,
            $notification->read_at
        );
    }

    /**
     * Sistem tarafından yönetilen alanların toplu atanamadığını doğrular.
     */
    public function test_protected_notification_fields_cannot_be_mass_assigned(): void
    {
        $notification = new Notification([
            'user_id' => 1,
            'actor_id' => 2,
            'subject_type' => Post::class,
            'subject_id' => 3,
            'type' => Notification::TYPE_SYSTEM,
            'title' => 'Bakım bildirimi',
            'message' => 'Sistem kısa süreli bakıma alınacaktır.',
            'data' => ['duration' => 10],
            'read_at' => now(),
        ]);

        $this->assertSame(Notification::TYPE_SYSTEM, $notification->type);
        $this->assertSame('Bakım bildirimi', $notification->title);
        $this->assertIsArray($notification->data);
        $this->assertNull($notification->user_id);
        $this->assertNull($notification->actor_id);
        $this->assertNull($notification->subject_type);
        $this->assertNull($notification->subject_id);
        $this->assertNull($notification->read_at);
    }

    /**
     * Kullanıcı foreign key davranışlarını doğrular.
     */
    public function test_user_foreign_keys_apply_expected_delete_rules(): void
    {
        $recipient = User::factory()->create();
        $actor = User::factory()->create();

        $notification = Notification::factory()
            ->forRecipient($recipient)
            ->fromActor($actor)
            ->create();

        $actor->delete();

        $this->assertNull($notification->refresh()->actor_id);

        $notificationId = $notification->getKey();

        $recipient->delete();

        $this->assertDatabaseMissing('notifications', [
            'id' => $notificationId,
        ]);
    }
}
