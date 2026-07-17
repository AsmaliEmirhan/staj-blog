<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    /** @use HasFactory<NotificationFactory> */
    use HasFactory;

    public const TYPE_POST_LIKED = 'post_liked';

    public const TYPE_POST_BOOKMARKED = 'post_bookmarked';

    public const TYPE_NEW_COMMENT = 'new_comment';

    public const TYPE_COMMENT_REPLY = 'comment_reply';

    public const TYPE_REPORT_RESOLVED = 'report_resolved';

    public const TYPE_SYSTEM = 'system';

    /**
     * Bildirim oluşturulurken doldurulmasına izin verilen alanlar.
     *
     * Alıcı, aktör ve ilgili model sistem tarafından atanır.
     *
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'title',
        'message',
        'data',
    ];

    /**
     * JSON ve tarih alanlarını uygun PHP türlerine dönüştürür.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    /**
     * Bildirimin gönderildiği kullanıcıyı döndürür.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Bildirimi tetikleyen kullanıcıyı döndürür.
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Bildirimle ilişkili yazı, yorum veya diğer modeli döndürür.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Yalnızca okunmamış bildirimleri getirir.
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    /**
     * Yalnızca okunmuş bildirimleri getirir.
     */
    public function scopeRead(Builder $query): Builder
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Bildirimin okunmuş olup olmadığını kontrol eder.
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Bildirimin okunmamış olup olmadığını kontrol eder.
     */
    public function isUnread(): bool
    {
        return ! $this->isRead();
    }

    /**
     * Bildirimi okundu olarak işaretler.
     */
    public function markAsRead(): bool
    {
        if ($this->isRead()) {
            return true;
        }

        return $this->forceFill([
            'read_at' => now(),
        ])->save();
    }

    /**
     * Bildirimi yeniden okunmamış olarak işaretler.
     */
    public function markAsUnread(): bool
    {
        if ($this->isUnread()) {
            return true;
        }

        return $this->forceFill([
            'read_at' => null,
        ])->save();
    }
}
