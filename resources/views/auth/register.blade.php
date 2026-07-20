@extends('layouts.guest')

@section('title', 'Kayıt Ol | '.config('app.name'))

@section('content')
    <section>
        <h1>Hesap oluştur</h1>

        <p>
            Blog yazıları oluşturmak ve diğer özellikleri kullanmak için
            ücretsiz hesap oluşturabilirsiniz.
        </p>

        <form method="POST" action="{{ route('register.store') }}">
            @csrf

            <div>
                <label for="name">Ad soyad</label>

                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    autocomplete="name"
                    maxlength="255"
                >

                @error('name')
                    <p>{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="username">Kullanıcı adı</label>

                <input
                    id="username"
                    type="text"
                    name="username"
                    value="{{ old('username') }}"
                    required
                    autocomplete="username"
                    minlength="3"
                    maxlength="50"
                    pattern="[a-z0-9_]+"
                >

                <small>
                    Yalnızca küçük harf, rakam ve alt çizgi kullanabilirsiniz.
                </small>

                @error('username')
                    <p>{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email">E-posta adresi</label>

                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
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
                    autocomplete="new-password"
                    minlength="8"
                >

                <small>
                    En az 8 karakter; büyük ve küçük harf, rakam ve sembol içermelidir.
                </small>

                @error('password')
                    <p>{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation">Parola tekrarı</label>

                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    minlength="8"
                >
            </div>

            <button type="submit">Kayıt ol</button>
        </form>

        <p>
            Zaten hesabınız var mı?
            <a href="{{ route('login') }}">Giriş yapın</a>
        </p>
    </section>
@endsection