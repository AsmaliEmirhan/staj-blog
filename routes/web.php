<?php

declare(strict_types=1);

use App\Http\Controllers\AiGenerationController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Ana sayfa
|--------------------------------------------------------------------------
|
| Ana sayfaya gelen kullanıcı blog yazıları sayfasına yönlendirilir.
|
*/

Route::get('/', [PostController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| Kimlik doğrulama rotaları
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function (): void {
    Route::view('/register', 'auth.register')->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.store');

    Route::view('/login', 'auth.login')->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Kategori rotaları
|--------------------------------------------------------------------------
*/
Route::get('/categories', [CategoryController::class, 'index'])
    ->name('categories.index');

Route::get('/categories/{category}', [CategoryController::class, 'show'])
    ->name('categories.show');

Route::get('/tags/{tag}', [TagController::class, 'show'])
    ->name('tags.show');

/*
|--------------------------------------------------------------------------
| Blog yazısı rotaları
|--------------------------------------------------------------------------
|
| Yazı listesi ve yayınlanmış yazıların detay sayfaları herkese açıktır.
| Oluşturma, düzenleme ve silme işlemleri oturum açmayı gerektirir.
| İşlem yetkileri ayrıca PostPolicy tarafından kontrol edilir.
|
*/

Route::get('/posts', [PostController::class, 'index'])
    ->name('posts.index');

Route::middleware('auth')->group(function (): void {
    Route::get('/posts/create', [PostController::class, 'create'])
        ->name('posts.create');

    Route::post('/posts', [PostController::class, 'store'])
        ->name('posts.store');

    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])
        ->name('posts.edit');

    Route::put('/posts/{post}', [PostController::class, 'update'])
        ->name('posts.update');

    Route::delete('/posts/{post}', [PostController::class, 'destroy'])
        ->name('posts.destroy');
    Route::post(
        '/api/ai/generate-post',
        [AiGenerationController::class, 'generateBlogPost']
    )
        ->middleware('throttle:5,1')
        ->name('api.ai.generate-post');
});

Route::get('/posts/{post}', [PostController::class, 'show'])
    ->name('posts.show');
