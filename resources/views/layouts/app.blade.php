<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name'))</title>

    <style>
        body {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            color: #222;
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }

        nav {
            display: flex;
            align-items: center;
            gap: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid #ddd;
        }

        nav form {
            margin-left: auto;
        }

        main {
            padding-top: 24px;
        }

        label {
            display: block;
            margin-top: 16px;
            font-weight: bold;
        }

        input,
        select,
        textarea {
            box-sizing: border-box;
            width: 100%;
            padding: 8px;
        }

        input[type="checkbox"] {
            width: auto;
        }

        button,
        .button {
            display: inline-block;
            width: auto;
            margin-top: 16px;
            padding: 8px 14px;
            border: 1px solid #333;
            background: #fff;
            color: #222;
            cursor: pointer;
            text-decoration: none;
        }

        .post {
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #ddd;
        }

        .success {
            padding: 12px;
            background: #dff7df;
        }

        .errors {
            padding: 12px;
            background: #ffe1e1;
        }

        .field-error {
            color: #b00020;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 20px;
        }

        .actions form,
        .actions .button {
            margin: 0;
        }

        .tags {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .tag-option label {
            display: inline;
            margin: 0;
            font-weight: normal;
        }
    </style>
</head>
<body>
    <header>
        <nav aria-label="Ana menü">
            <a href="{{ route('home') }}">
                {{ config('app.name') }}
            </a>

            <a href="{{ route('posts.index') }}">Yazılar</a>
            <a href="{{ route('categories.index') }}">Kategoriler</a>

            @auth
                <a href="{{ route('posts.create') }}">Yeni yazı</a>

                <span>{{ auth()->user()->name }}</span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit">Çıkış yap</button>
                </form>
            @else
                <a href="{{ route('login') }}">Giriş yap</a>
                <a href="{{ route('register') }}">Kayıt ol</a>
            @endauth
        </nav>
    </header>

    <main>
        @if (session('success'))
            <div class="success" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="errors" role="alert">
                <p>Formdaki hataları kontrol ediniz:</p>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>