<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name'))</title>
</head>
<body>
    <header>
        <nav aria-label="Ana menü">
            <a href="{{ route('home') }}">
                {{ config('app.name') }}
            </a>

            <a href="{{ route('login') }}">Giriş yap</a>
            <a href="{{ route('register') }}">Kayıt ol</a>
        </nav>
    </header>

    <main>
        @if (session('success'))
            <div role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div role="alert">
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