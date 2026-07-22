@extends('layouts.app')

@section('title', 'Kategoriler')

@section('content')
    <h1>Kategoriler</h1>

    @forelse ($categories as $category)
        <article class="post">
            <h2>
                <a href="{{ route('categories.show', $category) }}">
                    {{ $category->name }}
                </a>
            </h2>

            @if ($category->description)
                <p>{{ $category->description }}</p>
            @endif
        </article>
    @empty
        <p>Henüz aktif bir kategori bulunmuyor.</p>
    @endforelse
@endsection