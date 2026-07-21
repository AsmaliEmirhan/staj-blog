@extends('layouts.app')

@section('title', 'Yeni Yazı')

@section('content')
    <h1>Yeni blog yazısı oluştur</h1>

    <form method="POST" action="{{ route('posts.store') }}">
        @csrf

        @include('posts.partials.form')

        <button type="submit">Yazıyı kaydet</button>
    </form>

    <p>
        <a href="{{ route('posts.index') }}">Yazılara dön</a>
    </p>
@endsection