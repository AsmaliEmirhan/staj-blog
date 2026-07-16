<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /**
     * Profil alanlarının toplu olarak atanabildiğini doğrular.
     */
    public function test_profile_fields_can_be_mass_assigned(): void
    {
        $user = new User;

        $user->fill([
            'name' => 'Emirhan Asmalı',
            'username' => 'emirhan',
            'email' => 'emirhan@example.com',
            'avatar' => 'avatars/emirhan.jpg',
            'bio' => 'Yazılım ve yapay zekâ üzerine içerikler üretiyorum.',
        ]);

        $this->assertSame('Emirhan Asmalı', $user->name);
        $this->assertSame('emirhan', $user->username);
        $this->assertSame('emirhan@example.com', $user->email);
        $this->assertSame('avatars/emirhan.jpg', $user->avatar);
        $this->assertSame(
            'Yazılım ve yapay zekâ üzerine içerikler üretiyorum.',
            $user->bio
        );
    }

    /**
     * Kullanıcının form verisiyle kendisini yönetici yapamamasını doğrular.
     */
    public function test_role_and_status_cannot_be_mass_assigned(): void
    {
        $user = new User;

        $user->fill([
            'name' => 'Test Kullanıcısı',
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_PASSIVE,
        ]);

        $this->assertArrayNotHasKey('role', $user->getAttributes());
        $this->assertArrayNotHasKey('status', $user->getAttributes());
    }

    /**
     * Yönetici rolü yardımcı metodunun doğru sonuç verdiğini doğrular.
     */
    public function test_it_can_detect_admin_users(): void
    {
        $admin = new User;
        $admin->role = User::ROLE_ADMIN;

        $normalUser = new User;
        $normalUser->role = User::ROLE_USER;

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($normalUser->isAdmin());
    }

    /**
     * Aktif hesap kontrolünün doğru çalıştığını doğrular.
     */
    public function test_it_can_detect_active_users(): void
    {
        $activeUser = new User;
        $activeUser->status = User::STATUS_ACTIVE;

        $passiveUser = new User;
        $passiveUser->status = User::STATUS_PASSIVE;

        $this->assertTrue($activeUser->isActive());
        $this->assertFalse($passiveUser->isActive());
    }

    /**
     * Hassas kullanıcı alanlarının dışarıya açılmadığını doğrular.
     */
    public function test_sensitive_fields_are_hidden(): void
    {
        $user = new User;

        $this->assertContains('password', $user->getHidden());
        $this->assertContains('remember_token', $user->getHidden());
    }
}
