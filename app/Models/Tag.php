<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/**
 * Blog yazılarına bağlanabilen etiketleri temsil eder.
 */
class Tag extends Model
{
    /** @use HasFactory<TagFactory> */
    use HasFactory;

    /**
     * Toplu veri ataması yapılmasına izin verilen alanlar.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * Yeni etiket kaydedilirken slug boşsa etiket adından üretir.
     */
    protected static function booted(): void
    {
        static::creating(function (Tag $tag): void {
            if (filled($tag->slug)) {
                return;
            }

            $baseSlug = Str::slug($tag->name);
            $slug = $baseSlug;
            $suffix = 2;

            // Daha önce kullanılan slug değerleri için sayısal ek üretir.
            while (static::query()->where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$suffix;
                $suffix++;
            }

            $tag->slug = $slug;
        });
    }

    /**
     * Etiketin bağlı olduğu blog yazılarını döndürür.
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class)
            ->withTimestamps();
    }

    /**
     * Rota model bağlamasında ID yerine slug kullanır.
     *
     * Örnek: /etiketler/laravel
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
