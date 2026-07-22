@extends('layouts.app')

@section('title', $category->name)

@section('content')
    <h1>{{ $category->name }}</h1>

    @if ($category->description)
        <p>{{ $category->description }}</p>
    @endif

    @forelse ($posts as $post)
        <article class="post">
            <h2>
                <a href="{{ route('posts.show', $post) }}">
                    {{ $post->title }}
                </a>
            </h2>

            <p>
                Yazar: {{ $post->author->name }}
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
                    {{ $post->tags->pluck('name')->join(', ') }}
                </p>
            @endif

            <a href="{{ route('posts.show', $post) }}">
                Devamını oku
            </a>
        </article>
    @empty
        <p>Bu kategoride henüz yayımlanmış bir yazı bulunmuyor.</p>
    @endforelse

    {{ $posts->links() }}

    <p>
        <a href="{{ route('posts.index') }}">Tüm yazılara dön</a>
    </p>
@endsection