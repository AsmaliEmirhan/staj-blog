<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Yeni kullanıcı hesabı oluşturur ve oturum açar.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::query()->create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()
            ->route('home')
            ->with('success', 'Hesabınız başarıyla oluşturuldu.');
    }

    /**
     * Kullanıcı bilgilerini doğrular ve güvenli oturum başlatır.
     *
     * @throws ValidationException
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $this->ensureIsNotRateLimited($request);

        $authenticated = Auth::attempt([
            'email' => $request->string('email')->toString(),
            'password' => $request->string('password')->toString(),
            'status' => User::STATUS_ACTIVE,
        ], $request->boolean('remember'));

        if (! $authenticated) {
            RateLimiter::hit($this->throttleKey($request), 60);

            throw ValidationException::withMessages([
                'email' => 'Giriş bilgileri hatalı veya hesabınız aktif değil.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    /**
     * Kullanıcının oturumunu güvenli şekilde sonlandırır.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('home')
            ->with('success', 'Oturumunuz güvenli şekilde kapatıldı.');
    }

    /**
     * Çok sayıda başarısız giriş denemesini engeller.
     *
     * @throws ValidationException
     */
    private function ensureIsNotRateLimited(LoginRequest $request): void
    {
        $key = $this->throttleKey($request);

        if (! RateLimiter::tooManyAttempts($key, 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($key);

        throw ValidationException::withMessages([
            'email' => "Çok fazla giriş denemesi yapıldı. {$seconds} saniye sonra tekrar deneyin.",
        ]);
    }

    /**
     * Giriş sınırlandırması için e-posta ve IP tabanlı anahtar üretir.
     */
    private function throttleKey(LoginRequest $request): string
    {
        return Str::transliterate(
            Str::lower($request->string('email')->toString())
                .'|'.$request->ip()
        );
    }
}
