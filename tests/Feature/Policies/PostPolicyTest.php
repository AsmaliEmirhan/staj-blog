<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PostPolicyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ziyaretçinin yayınlanmış yazıyı görüntüleyebildiğini doğrular.
     */
    public function test_guest_can_view_published_post(): void
    {
        $post = Post::factory()->published()->create();

        $this->assertTrue(
            Gate::allows('view', $post)
        );
    }

    /**
     * Ziyaretçinin taslak yazıyı görüntüleyemediğini doğrular.
     */
    public function test_guest_cannot_view_draft_post(): void
    {
        $post = Post::factory()->create([
            'status' => Post::STATUS_DRAFT,
            'published_at' => null,
        ]);

        $this->assertFalse(
            Gate::allows('view', $post)
        );
    }

    /**
     * Aktif yazarın kendi taslak yazısını yönetebildiğini doğrular.
     */
    public function test_active_author_can_manage_own_post(): void
    {
        $author = User::factory()->create([
            'status' => User::STATUS_ACTIVE,
        ]);

        $post = Post::factory()->create([
            'user_id' => $author->getKey(),
            'status' => Post::STATUS_DRAFT,
            'published_at' => null,
        ]);

        $gate = Gate::forUser($author);

        $this->assertTrue($gate->allows('view', $post));
        $this->assertTrue($gate->allows('update', $post));
        $this->assertTrue($gate->allows('delete', $post));
        $this->assertTrue($gate->allows('restore', $post));
        $this->assertFalse($gate->allows('forceDelete', $post));
    }

    /**
     * Kullanıcının başka bir yazara ait taslağı yönetemediğini doğrular.
     */
    public function test_user_cannot_manage_another_authors_draft(): void
    {
        $author = User::factory()->create();
        $otherUser = User::factory()->create();

        $post = Post::factory()->create([
            'user_id' => $author->getKey(),
            'status' => Post::STATUS_DRAFT,
            'published_at' => null,
        ]);

        $gate = Gate::forUser($otherUser);

        $this->assertFalse($gate->allows('view', $post));
        $this->assertFalse($gate->allows('update', $post));
        $this->assertFalse($gate->allows('delete', $post));
        $this->assertFalse($gate->allows('restore', $post));
        $this->assertFalse($gate->allows('forceDelete', $post));
    }

    /**
     * Aktif kullanıcının yeni yazı oluşturabildiğini doğrular.
     */
    public function test_active_user_can_create_post(): void
    {
        $user = User::factory()->create([
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->assertTrue(
            Gate::forUser($user)->allows('create', Post::class)
        );
    }

    /**
     * Aktif olmayan kullanıcının yazı oluşturamadığını doğrular.
     */
    public function test_inactive_user_cannot_create_or_manage_post(): void
    {
        $user = User::factory()->create([
            'status' => 'inactive',
        ]);

        $post = Post::factory()->create([
            'user_id' => $user->getKey(),
            'status' => Post::STATUS_DRAFT,
            'published_at' => null,
        ]);

        $gate = Gate::forUser($user);

        $this->assertFalse($gate->allows('create', Post::class));
        $this->assertFalse($gate->allows('view', $post));
        $this->assertFalse($gate->allows('update', $post));
        $this->assertFalse($gate->allows('delete', $post));
        $this->assertFalse($gate->allows('restore', $post));
    }

    /**
     * Aktif yöneticinin bütün yazı işlemlerini yapabildiğini doğrular.
     */
    public function test_active_admin_can_manage_any_post(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $post = Post::factory()->create([
            'status' => Post::STATUS_DRAFT,
            'published_at' => null,
        ]);

        $gate = Gate::forUser($admin);

        $this->assertTrue($gate->allows('viewAny', Post::class));
        $this->assertTrue($gate->allows('view', $post));
        $this->assertTrue($gate->allows('create', Post::class));
        $this->assertTrue($gate->allows('update', $post));
        $this->assertTrue($gate->allows('delete', $post));
        $this->assertTrue($gate->allows('restore', $post));
        $this->assertTrue($gate->allows('forceDelete', $post));
    }

    /**
     * Herkesin yazı listesini görüntüleme yetkisine sahip olduğunu doğrular.
     */
    public function test_guest_can_view_post_list(): void
    {
        $this->assertTrue(
            Gate::allows('viewAny', Post::class)
        );
    }
}
