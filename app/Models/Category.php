<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Blog yazılarının ait olabileceği kategorileri temsil eder.
 */
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    /**
     * Toplu veri ataması yapılmasına izin verilen alanlar.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'sort_order',
    ];

    /**
     * Veritabanından gelen değerlerin PHP türlerini belirler.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Yeni kategori kaydedilirken slug boşsa kategori adından üretir.
     */
    protected static function booted(): void
    {
        static::creating(function (Category $category): void {
            if (blank($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Yalnızca aktif kategorileri sorguya dahil eder.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Kategorileri yönetici tarafından belirlenen sıraya göre getirir.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    /**
     * Laravel rota model bağlamasında ID yerine slug kullanır.
     *
     * Örnek: /kategoriler/yapay-zeka
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
