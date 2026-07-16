<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\BookmarkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bir kullanıcının daha sonra okumak için kaydettiği yazıyı temsil eder.
 */
class Bookmark extends Model
{
    /** @use HasFactory<BookmarkFactory> */
    use HasFactory;

    /**
     * Bookmark kayıtları doğrudan form verisiyle toplu oluşturulmaz.
     *
     * user_id ve post_id uygulama tarafından güvenli biçimde atanır.
     *
     * @var list<string>
     */
    protected $fillable = [];

    /**
     * Yazıyı kaydeden kullanıcıyı döndürür.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Kaydedilen blog yazısını döndürür.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
