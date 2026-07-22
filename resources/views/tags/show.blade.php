@extends('layouts.app')

@section('title', $tag->name . ' Etiketli Yazılar')

@section('content')
    <header>
        <h1>{{ $tag->name }} etiketli yazılar</h1>

        <p>
            <a href="{{ route('posts.index') }}">Tüm yazılara dön</a>
        </p>
    </header>

    @forelse ($posts as $post)
        <article class="post">
            @if ($post->featured_image)
                <a href="{{ route('posts.show', $post) }}">
                    <img
                        src="{{ asset('storage/' . $post->featured_image) }}"
                        alt="{{ $post->title }}"
                        loading="lazy"
                    >
                </a>
            @endif

            <h2>
                <a href="{{ route('posts.show', $post) }}">
                    {{ $post->title }}
                </a>
            </h2>

            <p>
                Yazar: {{ $post->author->name }}

                @if ($post->category)
                    · Kategori:
                    <a href="{{ route('categories.show', $post->category) }}">
                        {{ $post->category->name }}
                    </a>
                @endif

                @if ($post->published_at)
                    · Yayın: {{ $post->published_at->format('d.m.Y H:i') }}
                @endif
            </p>

            @if ($post->excerpt)
                <p>{{ $post->excerpt }}</p>
            @else
                <p>
                    {{ Str::limit(strip_tags($post->content), 180) }}
                </p>
            @endif

            <a href="{{ route('posts.show', $post) }}">
                Devamını oku
            </a>
        </article>
    @empty
        <p>Bu etikete ait yayımlanmış bir blog yazısı bulunmuyor.</p>
    @endforelse

    @if ($posts->hasPages())
        {{ $posts->links() }}
    @endif
@endsection