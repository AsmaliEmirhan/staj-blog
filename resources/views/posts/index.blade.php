@extends('layouts.app')

@section('title', 'Blog Yazıları')

@section('content')
    <h1>Blog yazıları</h1>

    <form method="GET" action="{{ route('posts.index') }}">
        <label for="search">Yazılarda ara</label>

        <input
            id="search"
            name="search"
            type="search"
            value="{{ $search }}"
            maxlength="100"
            placeholder="Başlık veya içerik yazın"
        >

        <button type="submit">Ara</button>

        @if ($search !== '')
            <a class="button" href="{{ route('posts.index') }}">Aramayı temizle</a>
        @endif
    </form>


    @auth
        @can('create', App\Models\Post::class)
            <p>
                <a class="button" href="{{ route('posts.create') }}">
                    Yeni yazı oluştur
                </a>
            </p>
        @endcan
    @endauth

    @forelse ($posts as $post)
        <article class="post">
            <h2>
                <a href="{{ route('posts.show', $post) }}">
                    {{ $post->title }}
                </a>
            </h2>

            <p>
                Yazar: {{ $post->author->name }}
                · Kategori:
                    @if ($post->category)
                        <a href="{{ route('categories.show', $post->category) }}">
                            {{ $post->category->name }}
                        </a>
                    @else
                        Kategorisiz
                    @endif
                · Yayın:
                {{ $post->published_at?->format('d.m.Y H:i') }}
            </p>

            @if ($post->excerpt)
                <p>{{ $post->excerpt }}</p>
            @else
                <p>{{ Str::limit(strip_tags($post->content), 180) }}</p>
            @endif

                    @if ($post->tags->isNotEmpty())
                <p>
                    Etiketler:

                    @foreach ($post->tags as $tag)
                        <a href="{{ route('tags.show', $tag) }}">
                            {{ $tag->name }}
                        </a>{{ $loop->last ? '' : ',' }}
                    @endforeach
                </p>
            @endif

            <a href="{{ route('posts.show', $post) }}">Devamını oku</a>
        </article>
    @empty
        <p>Henüz yayınlanmış bir blog yazısı bulunmuyor.</p>
    @endforelse

    {{ $posts->links() }}
@endsection