<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Sisteme kayıtlı kullanıcıları temsil eder.
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Kullanıcı rollerinin kod içerisinde farklı yazılmasını engeller.
     */
    public const ROLE_USER = 'user';

    public const ROLE_ADMIN = 'admin';

    /**
     * Kullanıcı hesabının desteklediği durumlar.
     */
    public const STATUS_ACTIVE = 'active';

    public const STATUS_PASSIVE = 'passive';

    /**
     * Toplu veri ataması yapılmasına izin verilen alanlar.
     *
     * role ve status güvenlik nedeniyle bu listeye eklenmedi.
     * Böylece kayıt formundan gönderilen sahte bir "role=admin"
     * değeri kullanıcıya yönetici yetkisi veremez.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'avatar',
        'bio',
    ];

    /**
     * Model JSON veya diziye çevrildiğinde gizlenecek hassas alanlar.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Veritabanından gelen alanların PHP türlerini belirler.
     *
     * "hashed" dönüşümü, modele verilen düz parolayı otomatik
     * olarak güvenli bir parola özetine dönüştürür.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Kullanıcının yazdığı blog yazılarını döndürür.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Kullanıcının yazdığı yorumları döndürür.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Yöneticinin incelediği yorumları döndürür.
     */
    public function moderatedComments(): HasMany
    {
        return $this->hasMany(Comment::class, 'moderated_by');
    }

    /**
     * Kullanıcının yönetici yetkisine sahip olup olmadığını kontrol eder.
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Kullanıcı hesabının aktif olup olmadığını kontrol eder.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
