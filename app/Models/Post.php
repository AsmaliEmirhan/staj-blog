<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Kullanıcılar tarafından oluşturulan blog yazılarını temsil eder.
 */
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Blog yazısının desteklediği yayın durumları.
     */
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ARCHIVED = 'archived';

    /**
     * Toplu veri ataması yapılmasına izin verilen alanlar.
     *
     * user_id, status, is_ai_generated ve view_count güvenlik nedeniyle
     * toplu atamaya kapalıdır. Bu değerler uygulama tarafından yönetilir.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'ai_keywords',
        'published_at',
    ];

    /**
     * Veritabanı alanlarının PHP türlerini belirler.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_ai_generated' => 'boolean',
            'ai_keywords' => 'array',
            'published_at' => 'datetime',
            'view_count' => 'integer',
        ];
    }

    /**
     * Yeni yazı kaydedilirken slug boşsa başlıktan üretir.
     */
    protected static function booted(): void
    {
        static::creating(function (Post $post): void {
            if (filled($post->slug)) {
                return;
            }

            $baseSlug = Str::slug($post->title);
            $slug = $baseSlug;
            $suffix = 2;

            /*
             * Aynı başlığa sahip yazılar için benzersiz slug üretir.
             * Soft delete edilmiş yazılar da benzersizlik kontrolüne dahildir.
             */
            while (static::withTrashed()->where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$suffix;
                $suffix++;
            }

            $post->slug = $slug;
        });
    }

    /**
     * Yazının sahibi olan kullanıcıyı döndürür.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Yazının ait olduğu kategoriyi döndürür.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Yazıya bağlı etiketleri döndürür.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)
            ->withTimestamps();
    }

    /**
     * Blog yazısına yapılan yorumları eski tarihten yeniye döndürür.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)
            ->oldest();
    }

    /**
     * Yazıya ait beğeni kayıtlarını döndürür.
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Yazıyı beğenen kullanıcıları döndürür.
     */
    public function likedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'likes',
            'post_id',
            'user_id'
        )->withTimestamps();
    }

    /**
     * Yazıya ait bookmark kayıtlarını döndürür.
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * Yazıyı daha sonra okumak için kaydeden kullanıcıları döndürür.
     */
    public function bookmarkedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'bookmarks',
            'post_id',
            'user_id'
        )->withTimestamps();
    }

    /**
     * Yalnızca yayınlanmış ve yayın zamanı gelmiş yazıları getirir.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_PUBLISHED)
            ->where('published_at', '<=', now());
    }

    /**
     * Yazıları yayın tarihine göre yeniden eskiye sıralar.
     */
    public function scopeLatestPublished(Builder $query): Builder
    {
        return $query->orderByDesc('published_at');
    }

    /**
     * Yazının şu anda ziyaretçilere açık olup olmadığını kontrol eder.
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED
            && $this->published_at?->isPast() === true;
    }

    /**
     * Yazı hakkında oluşturulan bildirimleri döndürür.
     */
    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    /**
     * Yazıyla ilişkili bildirimleri döndürür.
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'subject');
    }

    /**
     * Rota model bağlamasında ID yerine slug kullanır.
     *
     * Örnek: /yazilar/laravel-ile-blog-gelistirme
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
