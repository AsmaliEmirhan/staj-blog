<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * HTTP metodu ve URL yoluna göre doğru işlemi çalıştırır.
 */
final class Router
{
    /**
     * Uygulamaya kaydedilmiş rotaları tutar.
     *
     * @var array<int, array{
     *     method: string,
     *     path: string,
     *     pattern: string,
     *     handler: callable
     * }>
     */
    private array $routes = [];

    /**
     * @param Request $request Tarayıcıdan gelen mevcut HTTP isteği
     * @param string $basePath Projenin sunucudaki temel URL yolu
     */
    public function __construct(
        private readonly Request $request,
        private readonly string $basePath = ''
    ) {
    }

    /**
     * GET rotası ekler.
     */
    public function get(string $path, callable $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }

    /**
     * POST rotası ekler.
     */
    public function post(string $path, callable $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }

    /**
     * PUT rotası ekler.
     */
    public function put(string $path, callable $handler): self
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    /**
     * PATCH rotası ekler.
     */
    public function patch(string $path, callable $handler): self
    {
        return $this->addRoute('PATCH', $path, $handler);
    }

    /**
     * DELETE rotası ekler.
     */
    public function delete(string $path, callable $handler): self
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * Yeni rotayı listeye ekler.
     */
    private function addRoute(
        string $method,
        string $path,
        callable $handler
    ): self {
        $normalizedPath = $this->normalizePath($path);

        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $normalizedPath,
            'pattern' => $this->compileRoutePattern($normalizedPath),
            'handler' => $handler,
        ];

        return $this;
    }

    /**
     * Gelen isteğe uygun rotayı bulur ve ilgili işlemi çalıştırır.
     *
     * @return mixed
     */
    public function dispatch(): mixed
    {
        $requestMethod = $this->request->method();
        $requestPath = $this->removeBasePath($this->request->path());

        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            $matches = [];

            if (preg_match($route['pattern'], $requestPath, $matches) !== 1) {
                continue;
            }

            $routeParameters = $this->extractRouteParameters($matches);

            return call_user_func(
                $route['handler'],
                $this->request,
                $routeParameters
            );
        }

        throw new RuntimeException(
            sprintf(
                'Rota bulunamadı: %s %s',
                $requestMethod,
                $requestPath
            ),
            404
        );
    }

    /**
     * Dinamik rota alanlarını düzenli ifade desenine dönüştürür.
     *
     * Örnek:
     * /posts/{slug} → /posts/(?P<slug>[^/]+)
     */
    private function compileRoutePattern(string $path): string
    {
        if ($path === '/') {
            return '#^/?$#';
        }

        $segments = explode('/', trim($path, '/'));
        $patternSegments = [];

        foreach ($segments as $segment) {
            if (
                preg_match(
                    '/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/',
                    $segment,
                    $matches
                ) === 1
            ) {
                $parameterName = $matches[1];
                $patternSegments[] = sprintf(
                    '(?P<%s>[^/]+)',
                    $parameterName
                );

                continue;
            }

            $patternSegments[] = preg_quote($segment, '#');
        }

        return '#^/' . implode('/', $patternSegments) . '/?$#';
    }

    /**
     * Regex sonucundan yalnızca isimlendirilmiş rota parametrelerini alır.
     *
     * @param array<int|string, string> $matches
     * @return array<string, string>
     */
    private function extractRouteParameters(array $matches): array
    {
        $parameters = [];

        foreach ($matches as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            $parameters[$key] = rawurldecode($value);
        }

        return $parameters;
    }

    /**
     * Laragon localhost kullanımındaki proje yolunu URL'den kaldırır.
     *
     * Örnek:
     * /staj-blog/public/posts → /posts
     */
    private function removeBasePath(string $path): string
    {
        $normalizedPath = $this->normalizePath($path);
        $normalizedBasePath = $this->normalizeBasePath($this->basePath);

        if ($normalizedBasePath === '') {
            return $normalizedPath;
        }

        if ($normalizedPath === $normalizedBasePath) {
            return '/';
        }

        $basePathWithSlash = $normalizedBasePath . '/';

        if (str_starts_with($normalizedPath, $basePathWithSlash)) {
            $pathWithoutBase = substr(
                $normalizedPath,
                strlen($normalizedBasePath)
            );

            return $this->normalizePath($pathWithoutBase);
        }

        return $normalizedPath;
    }

    /**
     * Rota yollarını standart biçime dönüştürür.
     */
    private function normalizePath(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        $path = preg_replace('#/+#', '/', $path) ?? '/';

        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        return $path;
    }

    /**
     * Temel URL yolunu standart biçime dönüştürür.
     */
    private function normalizeBasePath(string $basePath): string
    {
        $basePath = trim($basePath);

        if ($basePath === '' || $basePath === '/' || $basePath === '.') {
            return '';
        }

        return $this->normalizePath($basePath);
    }
}