<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Blog yazılarına kullanıcılar tarafından yapılan yorumları temsil eder.
 */
class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Yorumun desteklediği moderasyon durumları.
     */
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    /**
     * Toplu veri ataması yapılmasına izin verilen alanlar.
     *
     * post_id, user_id, status ve moderasyon alanları güvenlik
     * nedeniyle toplu atamaya kapalıdır.
     *
     * @var list<string>
     */
    protected $fillable = [
        'parent_id',
        'content',
    ];

    /**
     * Veritabanı alanlarının PHP türlerini belirler.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'moderated_at' => 'datetime',
        ];
    }

    /**
     * Yorumun ait olduğu blog yazısını döndürür.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Yorumu yazan kullanıcıyı döndürür.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Yorumun cevap olarak yazıldığı üst yorumu döndürür.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Yoruma verilen alt cevapları döndürür.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->oldest();
    }

    /**
     * Yorumu inceleyen yönetici kullanıcıyı döndürür.
     */
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    /**
     * Yalnızca onaylanmış yorumları sorguya dahil eder.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Yalnızca başka bir yoruma cevap olmayan ana yorumları getirir.
     */
    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Yorumları eski tarihten yeni tarihe sıralar.
     */
    public function scopeOldestFirst(Builder $query): Builder
    {
        return $query->oldest();
    }

    /**
     * Yorumun ziyaretçilere gösterilebilir durumda olduğunu kontrol eder.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Yorumu belirtilen yönetici adına onaylar.
     */
    public function approve(User $moderator): bool
    {
        $this->status = self::STATUS_APPROVED;
        $this->moderated_by = $moderator->id;
        $this->moderated_at = now();

        return $this->save();
    }

    /**
     * Yorumu belirtilen yönetici adına reddeder.
     */
    public function reject(User $moderator): bool
    {
        $this->status = self::STATUS_REJECTED;
        $this->moderated_by = $moderator->id;
        $this->moderated_at = now();

        return $this->save();
    }
}
