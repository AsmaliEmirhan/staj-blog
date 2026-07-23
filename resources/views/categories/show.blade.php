@extends('layouts.app')

@section('title', $category->name)

@section('content')
    <header>
        <h1>{{ $category->name }}</h1>

        @if ($category->description)
            <p>{{ $category->description }}</p>
        @endif
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

                @if ($post->published_at)
                    · Yayın: {{ $post->published_at->format('d.m.Y H:i') }}
                @endif
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

            <a href="{{ route('posts.show', $post) }}">
                Devamını oku
            </a>
        </article>
    @empty
        <p>Bu kategoride henüz yayımlanmış bir yazı bulunmuyor.</p>
    @endforelse

    @if ($posts->hasPages())
        {{ $posts->links() }}
    @endif

    <p>
        <a href="{{ route('posts.index') }}">Tüm yazılara dön</a>
    </p>
@endsection