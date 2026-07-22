@extends('layouts.app')

@section('title', $tag->name . ' Etiketli Yazılar')

@section('content')
    <h1>{{ $tag->name }} etiketli yazılar</h1>

    <p>
        <a href="{{ route('posts.index') }}">Tüm yazılara dön</a>
    </p>

    @forelse ($posts as $post)
        <article class="post">
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

                · Yayın: {{ $post->published_at?->format('d.m.Y H:i') }}
            </p>

            @if ($post->excerpt)
                <p>{{ $post->excerpt }}</p>
            @else
                <p>{{ Str::limit(strip_tags($post->content), 180) }}</p>
            @endif

            <a href="{{ route('posts.show', $post) }}">Devamını oku</a>
        </article>
    @empty
        <p>Bu etikete ait yayımlanmış bir blog yazısı bulunmuyor.</p>
    @endforelse

    {{ $posts->links() }}
@endsection