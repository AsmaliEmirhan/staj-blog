<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AiGenerationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiGeneration extends Model
{
    /** @use HasFactory<AiGenerationFactory> */
    use HasFactory;

    public const TYPE_ARTICLE = 'article';

    public const TYPE_TITLE = 'title';

    public const TYPE_SUMMARY = 'summary';

    public const TYPE_TAGS = 'tags';

    public const TYPE_IMPROVEMENT = 'improvement';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    /**
     * AI isteği oluşturulurken doldurulmasına izin verilen alanlar.
     *
     * Sonuç, durum, token ve maliyet alanları sistem tarafından yönetilir.
     *
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'provider',
        'model',
        'prompt',
        'input',
    ];

    /**
     * JSON, sayısal ve tarih alanlarını uygun PHP türlerine dönüştürür.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'input' => 'array',
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
            'total_tokens' => 'integer',
            'estimated_cost' => 'decimal:6',
            'duration_ms' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * AI isteğini başlatan kullanıcıyı döndürür.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * AI üretimiyle ilişkili yazıyı döndürür.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Yalnızca bekleyen AI işlemlerini getirir.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Yalnızca devam eden AI işlemlerini getirir.
     */
    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Yalnızca tamamlanan AI işlemlerini getirir.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Yalnızca başarısız AI işlemlerini getirir.
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * İşlemin bekleme durumunda olup olmadığını kontrol eder.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * İşlemin başarıyla tamamlanıp tamamlanmadığını kontrol eder.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * İşlemin başarısız olup olmadığını kontrol eder.
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * AI işlemini başladı olarak işaretler.
     */
    public function markAsProcessing(): bool
    {
        return $this->forceFill([
            'status' => self::STATUS_PROCESSING,
            'started_at' => now(),
            'completed_at' => null,
            'error_message' => null,
        ])->save();
    }

    /**
     * AI işlemini sonuç ve kullanım bilgileriyle tamamlar.
     */
    public function complete(
        string $output,
        int $promptTokens = 0,
        int $completionTokens = 0,
        ?string $requestId = null,
        ?string $estimatedCost = null,
    ): bool {
        $completedAt = now();

        $duration = $this->started_at !== null
            ? (int) $this->started_at->diffInMilliseconds($completedAt)
            : null;

        return $this->forceFill([
            'status' => self::STATUS_COMPLETED,
            'request_id' => $requestId,
            'output' => $output,
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $promptTokens + $completionTokens,
            'estimated_cost' => $estimatedCost,
            'duration_ms' => $duration,
            'completed_at' => $completedAt,
            'error_message' => null,
        ])->save();
    }

    /**
     * AI işlemini hata mesajıyla başarısız olarak işaretler.
     */
    public function fail(
        string $errorMessage,
        ?string $requestId = null,
    ): bool {
        $completedAt = now();

        $duration = $this->started_at !== null
            ? (int) $this->started_at->diffInMilliseconds($completedAt)
            : null;

        return $this->forceFill([
            'status' => self::STATUS_FAILED,
            'request_id' => $requestId,
            'output' => null,
            'duration_ms' => $duration,
            'completed_at' => $completedAt,
            'error_message' => $errorMessage,
        ])->save();
    }
}
