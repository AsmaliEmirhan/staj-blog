@extends('layouts.app')

@section('title', $post->title)

@section('content')
    <article>
        <h1>{{ $post->title }}</h1>

        <p>
            Yazar: {{ $post->author->name }}
            · Kategori: {{ $post->category?->name ?? 'Kategorisiz' }}
        </p>

        <p>
            Durum: {{ $post->status }}

            @if ($post->published_at)
                · Yayın tarihi: {{ $post->published_at->format('d.m.Y H:i') }}
            @endif
        </p>

        @if ($post->featured_image)
            <img
                src="{{ $post->featured_image }}"
                alt="{{ $post->title }}"
            >
        @endif

        @if ($post->excerpt)
            <p><strong>{{ $post->excerpt }}</strong></p>
        @endif

        <div>
            {!! nl2br(e($post->content)) !!}
        </div>

            @if ($post->tags->isNotEmpty())
                <p>
                    <strong>Etiketler:</strong>

                    @foreach ($post->tags as $tag)
                        <a href="{{ route('tags.show', $tag) }}">
                            {{ $tag->name }}
                        </a>{{ $loop->last ? '' : ',' }}
                    @endforeach
                </p>
            @endif
    </article>

    <div class="actions">
        <a href="{{ route('posts.index') }}">Yazılara dön</a>

        @can('update', $post)
            <a class="button" href="{{ route('posts.edit', $post) }}">
                Düzenle
            </a>
        @endcan

        @can('delete', $post)
            <form
                method="POST"
                action="{{ route('posts.destroy', $post) }}"
                onsubmit="return confirm('Bu yazıyı silmek istediğinize emin misiniz?')"
            >
                @csrf
                @method('DELETE')

                <button type="submit">Yazıyı sil</button>
            </form>
        @endcan
    </div>
@endsection