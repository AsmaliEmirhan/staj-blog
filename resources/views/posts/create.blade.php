@extends('layouts.app')

@section('title', 'Yeni Yazı')

@section('content')
    <h1>Yeni blog yazısı oluştur</h1>

    <section
        id="ai-blog-generator"
        data-endpoint="{{ route('api.ai.generate-post') }}"
        aria-labelledby="ai-generator-title"
    >
        <h2 id="ai-generator-title">AI ile içerik üret</h2>

        <p>
            Blog konusunu açıklayan anahtar kelimeleri girin.
            Üretilen metni kaydetmeden önce kontrol edin.
        </p>

        <label for="ai-keywords">Anahtar kelimeler</label>
        <textarea
            id="ai-keywords"
            rows="3"
            maxlength="500"
            placeholder="Örnek: Laravel, PHP, güvenli web geliştirme"
        ></textarea>

        <button id="generate-blog-button" type="button">
            İçerik üret
        </button>

        <p
            id="ai-generation-status"
            role="status"
            aria-live="polite"
        ></p>
    </section>

    <form
        id="post-create-form"
        method="POST"
        action="{{ route('posts.store') }}"
        enctype="multipart/form-data"
    >
        @csrf

        @include('posts.partials.form')

        <button type="submit">Yazıyı kaydet</button>
    </form>

    <p>
        <a href="{{ route('posts.index') }}">Yazılara dön</a>
    </p>

    <script
        src="{{ asset('js/ai-blog-generator.js') }}"
        defer
    ></script>
@endsection