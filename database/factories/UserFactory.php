<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Factory tarafından kullanılacak varsayılan parola özeti.
     */
    protected static ?string $password;

    /**
     * Test ve örnek kullanıcıların varsayılan alanlarını üretir.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),

            // unique() aynı test grubunda tekrar eden kullanıcı adı üretmez.
            'username' => fake()->unique()->userName(),

            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),

            // Parola her kullanıcı için tekrar tekrar hesaplanmaz.
            'password' => static::$password ??= Hash::make('password'),

            'avatar' => null,
            'bio' => fake()->optional()->sentence(),
            'role' => User::ROLE_USER,
            'status' => User::STATUS_ACTIVE,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * E-posta adresi doğrulanmamış kullanıcı üretir.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Yönetici yetkisine sahip kullanıcı üretir.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => User::ROLE_ADMIN,
        ]);
    }

    /**
     * Pasif durumda kullanıcı hesabı üretir.
     */
    public function passive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => User::STATUS_PASSIVE,
        ]);
    }
}
