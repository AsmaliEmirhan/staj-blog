<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ReportFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    /** @use HasFactory<ReportFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_REVIEWING = 'reviewing';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_DISMISSED = 'dismissed';

    public const REASON_SPAM = 'spam';

    public const REASON_HARASSMENT = 'harassment';

    public const REASON_INAPPROPRIATE = 'inappropriate';

    public const REASON_MISINFORMATION = 'misinformation';

    public const REASON_OTHER = 'other';

    /**
     * Kullanıcının bildirim oluştururken doldurabileceği alanlar.
     *
     * Kullanıcı, bildirimin durumunu ve inceleyen yöneticiyi
     * doğrudan değiştiremez.
     *
     * @var list<string>
     */
    protected $fillable = [
        'reason',
        'description',
    ];

    /**
     * Tarih alanlarının Carbon nesnesine dönüştürülmesini sağlar.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * Bildirilen yazı veya yorum kaydını döndürür.
     */
    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Bildirimi oluşturan kullanıcıyı döndürür.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Bildirimi inceleyen yöneticiyi döndürür.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Yalnızca bekleyen bildirimleri getirir.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Yalnızca incelenmekte olan bildirimleri getirir.
     */
    public function scopeReviewing(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REVIEWING);
    }

    /**
     * Bildirimin bekleme durumunda olup olmadığını kontrol eder.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Bildirimi yönetici incelemesine alır.
     */
    public function markAsReviewing(User $reviewer): bool
    {
        return $this->forceFill([
            'status' => self::STATUS_REVIEWING,
            'reviewed_by' => $reviewer->getKey(),
            'reviewed_at' => now(),
        ])->save();
    }

    /**
     * Bildirimi çözümlendi olarak işaretler.
     */
    public function resolve(User $reviewer, ?string $note = null): bool
    {
        return $this->forceFill([
            'status' => self::STATUS_RESOLVED,
            'reviewed_by' => $reviewer->getKey(),
            'reviewed_at' => now(),
            'resolution_note' => $note,
        ])->save();
    }

    /**
     * Bildirimi geçersiz olarak kapatır.
     */
    public function dismiss(User $reviewer, ?string $note = null): bool
    {
        return $this->forceFill([
            'status' => self::STATUS_DISMISSED,
            'reviewed_by' => $reviewer->getKey(),
            'reviewed_at' => now(),
            'resolution_note' => $note,
        ])->save();
    }
}
