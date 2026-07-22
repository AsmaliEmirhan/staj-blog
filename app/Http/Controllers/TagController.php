<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Contracts\View\View;

class TagController extends Controller
{
    /**
     * Etikete bağlı yayımlanmış blog yazılarını gösterir.
     */
    public function show(Tag $tag): View
    {
        $posts = $tag->posts()
            ->with(['author', 'category', 'tags'])
            ->where('status', Post::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->paginate(10);

        return view('tags.show', [
            'tag' => $tag,
            'posts' => $posts,
        ]);
    }
}
