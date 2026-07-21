@extends('layouts.app')

@section('title', 'Blog Yazıları')

@section('content')
    <h1>Blog yazıları</h1>

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
                · Kategori: {{ $post->category?->name ?? 'Kategorisiz' }}
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

            <a href="{{ route('posts.show', $post) }}">Devamını oku</a>
        </article>
    @empty
        <p>Henüz yayınlanmış bir blog yazısı bulunmuyor.</p>
    @endforelse

    {{ $posts->links() }}
@endsection