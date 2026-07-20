@extends('layouts.guest')

@section('title', 'Giriş Yap | '.config('app.name'))

@section('content')
    <section>
        <h1>Giriş yap</h1>

        <p>
            Yazılarınızı yönetmek ve hesabınıza erişmek için giriş yapın.
        </p>

        <form method="POST" action="{{ route('login.store') }}">
            @csrf

            <div>
                <label for="email">E-posta adresi</label>

                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="email"
                    maxlength="255"
                >

                @error('email')
                    <p>{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password">Parola</label>

                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                >

                @error('password')
                    <p>{{ $message }}</p>
                @enderror
            </div>

            <div>
                <input
                    id="remember"
                    type="checkbox"
                    name="remember"
                    value="1"
                    @checked(old('remember'))
                >

                <label for="remember">Beni hatırla</label>
            </div>

            <button type="submit">Giriş yap</button>
        </form>

        <p>
            Henüz hesabınız yok mu?
            <a href="{{ route('register') }}">Hesap oluşturun</a>
        </p>
    </section>
@endsection