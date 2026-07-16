<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Slug verilmediğinde kategori adından otomatik üretildiğini doğrular.
     */
    public function test_slug_is_generated_from_category_name(): void
    {
        $category = Category::factory()->create([
            'name' => 'Yapay Zekâ',
            'slug' => null,
        ]);

        $this->assertSame('yapay-zeka', $category->slug);

        $this->assertDatabaseHas('categories', [
            'name' => 'Yapay Zekâ',
            'slug' => 'yapay-zeka',
        ]);
    }

    /**
     * Aktiflik ve sıralama alanlarının doğru PHP türlerine dönüştüğünü doğrular.
     */
    public function test_category_fields_are_cast_to_correct_types(): void
    {
        $category = Category::factory()->make([
            'is_active' => 0,
            'sort_order' => '5',
        ]);

        $this->assertFalse($category->is_active);
        $this->assertSame(5, $category->sort_order);
    }

    /**
     * Active scope'un pasif kategorileri sorgudan çıkardığını doğrular.
     */
    public function test_active_scope_returns_only_active_categories(): void
    {
        $activeCategory = Category::factory()->create([
            'is_active' => true,
        ]);

        Category::factory()->inactive()->create();

        $categories = Category::query()->active()->get();

        $this->assertCount(1, $categories);
        $this->assertTrue($categories->contains($activeCategory));
    }

    /**
     * Ordered scope'un önce sıra numarası, sonra kategori adına göre çalıştığını doğrular.
     */
    public function test_ordered_scope_sorts_categories_correctly(): void
    {
        Category::factory()->create([
            'name' => 'Yazılım',
            'slug' => 'yazilim',
            'sort_order' => 20,
        ]);

        Category::factory()->create([
            'name' => 'Teknoloji',
            'slug' => 'teknoloji',
            'sort_order' => 10,
        ]);

        Category::factory()->create([
            'name' => 'Donanım',
            'slug' => 'donanim',
            'sort_order' => 10,
        ]);

        $categoryNames = Category::query()
            ->ordered()
            ->pluck('name')
            ->all();

        $this->assertSame([
            'Donanım',
            'Teknoloji',
            'Yazılım',
        ], $categoryNames);
    }

    /**
     * Rota model bağlamasında ID yerine slug kullanıldığını doğrular.
     */
    public function test_route_key_uses_slug(): void
    {
        $category = new Category;

        $this->assertSame('slug', $category->getRouteKeyName());
    }
}
