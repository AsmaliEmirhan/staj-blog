<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

// Composer, ortam değişkenleri ve uygulama ayarlarını yükler.
require_once dirname(__DIR__) . '/bootstrap/app.php';

// Tarayıcıdan gelen isteği yakalar.
$request = Request::capture();

// public/index.php dosyasının sunucudaki temel URL yolunu belirler.
// Localhost kullanımında sonuç: /staj-blog/public
$scriptDirectory = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$basePath = str_replace('\\', '/', $scriptDirectory);

if ($basePath === '/' || $basePath === '.') {
    $basePath = '';
}

// Router nesnesini mevcut istek ve temel URL yoluyla oluşturur.
$router = new Router($request, $basePath);

// Ana test rotası.
$router->get(
    '/',
    static function (Request $request, array $parameters): void {
        Response::success(
            message: 'Ana rota başarıyla çalıştı.',
            data: [
                'method' => $request->method(),
                'path' => $request->path(),
            ]
        )->send();
    }
);

// Query parametresi test rotası.
$router->get(
    '/request-test',
    static function (Request $request, array $parameters): void {
        Response::success(
            message: 'GET rotası başarıyla çalıştı.',
            data: [
                'query' => $request->query(),
            ]
        )->send();
    }
);

// JSON body test rotası.
$router->post(
    '/request-test',
    static function (Request $request, array $parameters): void {
        Response::success(
            message: 'POST rotası başarıyla çalıştı.',
            data: [
                'body' => $request->input(),
            ]
        )->send();
    }
);

// Dinamik URL parametresi test rotası.
$router->get(
    '/posts/{slug}',
    static function (Request $request, array $parameters): void {
        Response::success(
            message: 'Dinamik blog rotası başarıyla çalıştı.',
            data: [
                'slug' => $parameters['slug'] ?? null,
            ]
        )->send();
    }
);

// Gelen isteğe uygun rotayı çalıştırır.
$router->dispatch();