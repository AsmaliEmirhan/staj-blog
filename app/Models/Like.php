<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\LikeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bir kullanıcının blog yazısına yaptığı beğeniyi temsil eder.
 */
class Like extends Model
{
    /** @use HasFactory<LikeFactory> */
    use HasFactory;

    /**
     * Beğeniler doğrudan form verisiyle toplu oluşturulmayacaktır.
     *
     * user_id ve post_id değerleri kimliği doğrulanmış kullanıcı
     * ve seçilen yazı üzerinden uygulama tarafından atanacaktır.
     *
     * @var list<string>
     */
    protected $fillable = [];

    /**
     * Beğeniyi yapan kullanıcıyı döndürür.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Beğenilen blog yazısını döndürür.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
