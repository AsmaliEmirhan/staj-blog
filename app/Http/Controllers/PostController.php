<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Post::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $search = trim($validated['search'] ?? '');

        $posts = Post::query()
            ->with(['author', 'category', 'tags'])
            ->where('status', Post::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->latest('published_at')
            ->paginate(10)
            ->withQueryString();

        return view('posts.index', compact('posts', 'search'));
    }

    /**
     * Yeni yazı oluşturma formunu gösterir.
     */
    public function create(): View
    {
        $this->authorize('create', Post::class);

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $tags = Tag::query()
            ->orderBy('name')
            ->get();

        return view('posts.create', compact('categories', 'tags'));
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $post = DB::transaction(function () use ($request): Post {
            $validated = $request->validated();
            $tagIds = $validated['tag_ids'] ?? [];
            $status = $validated['status'];

            unset(
                $validated['tag_ids'],
                $validated['status']
            );

            $post = new Post($validated);
            $post->user_id = $request->user()->getKey();
            $post->status = $status;
            $post->save();

            $post->tags()->sync($tagIds);

            return $post;
        });

        return redirect()
            ->route('posts.show', $post)
            ->with('success', 'Blog yazısı başarıyla oluşturuldu.');
    }

    /**
     * Belirtilen blog yazısını gösterir.
     */
    public function show(Post $post): View
    {
        $this->authorize('view', $post);

        $post->load(['author', 'category', 'tags']);

        return view('posts.show', compact('post'));
    }

    /**
     * Yazı düzenleme formunu gösterir.
     */
    public function edit(Post $post): View
    {
        $this->authorize('update', $post);

        $post->load('tags');

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $tags = Tag::query()
            ->orderBy('name')
            ->get();

        return view('posts.edit', compact('post', 'categories', 'tags'));
    }

    public function update(
        UpdatePostRequest $request,
        Post $post
    ): RedirectResponse {
        DB::transaction(function () use ($request, $post): void {
            $validated = $request->validated();
            $tagIds = $validated['tag_ids'] ?? [];
            $status = $validated['status'];

            unset(
                $validated['tag_ids'],
                $validated['status']
            );

            $post->fill($validated);
            $post->status = $status;
            $post->save();

            $post->tags()->sync($tagIds);
        });

        return redirect()
            ->route('posts.show', $post)
            ->with('success', 'Blog yazısı başarıyla güncellendi.');
    }

    /**
     * Blog yazısını geri alınabilir biçimde siler.
     */
    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()
            ->route('posts.index')
            ->with('success', 'Blog yazısı başarıyla silindi.');
    }
}
