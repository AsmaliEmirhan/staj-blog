<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Contracts\View\View;

class CategoryController extends Controller
{
    /**
     * Aktif kategorileri listeler.
     */
    public function index(): View
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('categories.index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Aktif kategoriye ait yayımlanmış yazıları listeler.
     */
    public function show(Category $category): View
    {
        abort_unless($category->is_active, 404);

        $posts = $category->posts()
            ->with(['author', 'category', 'tags'])
            ->published()
            ->latestPublished()
            ->paginate(10);

        return view('categories.show', [
            'category' => $category,
            'posts' => $posts,
        ]);
    }
}
