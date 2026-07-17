<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
     * Kullanıcının oluşturduğu beğeni kayıtlarını döndürür.
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Kullanıcının beğendiği blog yazılarını döndürür.
     */
    public function likedPosts(): BelongsToMany
    {
        return $this->belongsToMany(
            Post::class,
            'likes',
            'user_id',
            'post_id'
        )->withTimestamps();
    }

    /**
     * Kullanıcının oluşturduğu bookmark kayıtlarını döndürür.
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * Kullanıcının daha sonra okumak için kaydettiği yazıları döndürür.
     */
    public function bookmarkedPosts(): BelongsToMany
    {
        return $this->belongsToMany(
            Post::class,
            'bookmarks',
            'user_id',
            'post_id'
        )->withTimestamps();
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

    /**
     * Kullanıcının oluşturduğu bildirimleri döndürür.
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    /**
     * Kullanıcının aldığı uygulama bildirimlerini döndürür.
     *
     * Laravel'in yerleşik notifications ilişkisiyle çakışmaması için
     * özel bir metot adı kullanılır.
     */
    public function receivedNotifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Kullanıcının eylemleriyle oluşan bildirimleri döndürür.
     */
    public function triggeredNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'actor_id');
    }

    /**
     * Kullanıcının yönetici olarak incelediği bildirimleri döndürür.
     */
    public function reviewedReports(): HasMany
    {
        return $this->hasMany(Report::class, 'reviewed_by');
    }
}
