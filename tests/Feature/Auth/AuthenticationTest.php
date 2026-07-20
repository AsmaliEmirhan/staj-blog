<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ziyaretçinin kayıt sayfasını görüntüleyebildiğini doğrular.
     */
    public function test_guest_can_view_registration_page(): void
    {
        $response = $this->get(route('register'));

        $response
            ->assertOk()
            ->assertViewIs('auth.register')
            ->assertSee('Hesap oluştur');
    }

    /**
     * Ziyaretçinin giriş sayfasını görüntüleyebildiğini doğrular.
     */
    public function test_guest_can_view_login_page(): void
    {
        $response = $this->get(route('login'));

        $response
            ->assertOk()
            ->assertViewIs('auth.login')
            ->assertSee('Giriş yap');
    }

    /**
     * Geçerli bilgilerle yeni kullanıcı oluşturulabildiğini doğrular.
     */
    public function test_guest_can_register(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => '  Emirhan Asmalı  ',
            'username' => '  Emirhan_Asmali  ',
            'email' => '  EMIRHAN@example.com  ',
            'password' => 'StrongPass1!',
            'password_confirmation' => 'StrongPass1!',
            'role' => User::ROLE_ADMIN,
            'status' => 'inactive',
        ]);

        $response->assertRedirect(route('home'));

        $user = User::query()
            ->where('email', 'emirhan@example.com')
            ->firstOrFail();

        $this->assertSame('Emirhan Asmalı', $user->name);
        $this->assertSame('emirhan_asmali', $user->username);
        $this->assertTrue(Hash::check('StrongPass1!', $user->password));
        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->isActive());
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Geçersiz kayıt bilgilerinin reddedildiğini doğrular.
     */
    public function test_registration_requires_valid_and_unique_data(): void
    {
        User::factory()->create([
            'username' => 'existing_user',
            'email' => 'existing@example.com',
        ]);

        $response = $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'A',
                'username' => 'existing_user',
                'email' => 'existing@example.com',
                'password' => 'weak',
                'password_confirmation' => 'different',
            ]);

        $response
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors([
                'name',
                'username',
                'email',
                'password',
            ]);

        $this->assertGuest();
    }

    /**
     * Aktif kullanıcının doğru bilgilerle giriş yapabildiğini doğrular.
     */
    public function test_active_user_can_login(): void
    {
        $password = 'StrongPass1!';

        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make($password),
            'status' => User::STATUS_ACTIVE,
        ]);

        $response = $this->post(route('login.store'), [
            'email' => '  USER@example.com  ',
            'password' => $password,
            'remember' => true,
        ]);

        $response->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }

    /**
     * Hatalı parolayla giriş yapılamadığını doğrular.
     */
    public function test_user_cannot_login_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('StrongPass1!'),
            'status' => User::STATUS_ACTIVE,
        ]);

        $response = $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'user@example.com',
                'password' => 'WrongPass1!',
            ]);

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    /**
     * Giriş yapmış kullanıcının güvenli şekilde çıkış yapabildiğini doğrular.
     */
    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('logout'));

        $response->assertRedirect(route('home'));

        $this->assertGuest();
    }

    /**
     * Ziyaretçinin logout route'una erişemediğini doğrular.
     */
    public function test_guest_cannot_access_logout_route(): void
    {
        $response = $this->post(route('logout'));

        $response->assertRedirect(route('login'));

        $this->assertGuest();
    }

    /**
     * Beş başarısız girişten sonra yeni denemelerin sınırlandırıldığını doğrular.
     */
    public function test_login_is_rate_limited_after_five_failed_attempts(): void
    {
        $email = 'limited@example.com';
        $ipAddress = '127.0.0.1';

        $key = Str::transliterate(
            Str::lower($email).'|'.$ipAddress
        );

        RateLimiter::clear($key);

        $this->withServerVariables([
            'REMOTE_ADDR' => $ipAddress,
        ]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), [
                'email' => $email,
                'password' => 'WrongPass1!',
            ]);
        }

        $this->assertTrue(
            RateLimiter::tooManyAttempts($key, 5)
        );

        $response = $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => $email,
                'password' => 'WrongPass1!',
            ]);

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $errors = session('errors');

        $this->assertStringContainsString(
            'Çok fazla giriş denemesi',
            $errors->first('email')
        );

        $this->assertGuest();

        RateLimiter::clear($key);
    }
}
