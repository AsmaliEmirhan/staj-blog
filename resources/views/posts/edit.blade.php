@extends('layouts.app')

@section('title', 'Yazıyı Düzenle')

@section('content')
    <h1>Blog yazısını düzenle</h1>

    <form method="POST" action="{{ route('posts.update', $post) }}">
        @csrf
        @method('PUT')

        @include('posts.partials.form')

        <button type="submit">Değişiklikleri kaydet</button>
    </form>

    <p>
        <a href="{{ route('posts.show', $post) }}">Yazıya dön</a>
    </p>
@endsection