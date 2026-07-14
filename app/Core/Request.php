<?php

declare(strict_types=1);

namespace App\Core;

use InvalidArgumentException;
use JsonException;

/**
 * Tarayıcıdan gelen HTTP isteğini temsil eder.
 */
final class Request
{
    /**
     * İzin verilen en büyük JSON istek boyutu: 1 MB.
     */
    private const MAX_JSON_BODY_SIZE = 1_048_576;

    /**
     * Request nesnesi yalnızca capture() metodu üzerinden oluşturulur.
     *
     * @param array<string, mixed> $queryParams
     * @param array<string, mixed> $body
     * @param array<string, string> $headers
     */
    private function __construct(
        private readonly string $method,
        private readonly string $uri,
        private readonly string $path,
        private readonly array $queryParams,
        private readonly array $body,
        private readonly array $headers
    ) {
    }

    /**
     * PHP global değişkenlerinden mevcut HTTP isteğini oluşturur.
     */
    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Query string bölümünü kaldırarak yalnızca URL yolunu alır.
        $parsedPath = parse_url($uri, PHP_URL_PATH);
        $path = is_string($parsedPath) && $parsedPath !== ''
            ? rawurldecode($parsedPath)
            : '/';

        $headers = self::captureHeaders();
        $body = self::captureBody($method, $headers);

        return new self(
            method: $method,
            uri: $uri,
            path: $path,
            queryParams: $_GET,
            body: $body,
            headers: $headers
        );
    }

    /**
     * İsteğin HTTP metodunu döndürür.
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Query string dahil olmak üzere istek URI bilgisini döndürür.
     */
    public function uri(): string
    {
        return $this->uri;
    }

    /**
     * Query string içermeyen URL yolunu döndürür.
     */
    public function path(): string
    {
        return $this->path;
    }

    /**
     * İsteğin belirtilen HTTP metoduna sahip olup olmadığını kontrol eder.
     */
    public function isMethod(string $method): bool
    {
        return $this->method === strtoupper($method);
    }

    /**
     * Query parametrelerinden birini veya tamamını döndürür.
     *
     * @return mixed
     */
    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->queryParams;
        }

        return $this->queryParams[$key] ?? $default;
    }

    /**
     * JSON veya form verilerinden birini ya da tamamını döndürür.
     *
     * @return mixed
     */
    public function input(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->body;
        }

        return $this->body[$key] ?? $default;
    }

    /**
     * Query parametreleriyle body verilerini birleştirerek döndürür.
     *
     * Aynı isimde değer bulunursa body içindeki değer öncelikli olur.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return array_merge($this->queryParams, $this->body);
    }

    /**
     * Bütün HTTP başlıklarını döndürür.
     *
     * @return array<string, string>
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Belirtilen HTTP başlığını büyük-küçük harf duyarsız şekilde döndürür.
     */
    public function header(string $name, ?string $default = null): ?string
    {
        return $this->headers[strtolower($name)] ?? $default;
    }

    /**
     * PHP sunucu değişkenlerinden HTTP başlıklarını toplar.
     *
     * @return array<string, string>
     */
    private static function captureHeaders(): array
    {
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (!is_string($value)) {
                continue;
            }

            if (str_starts_with($key, 'HTTP_')) {
                $headerName = substr($key, 5);
                $headerName = str_replace('_', '-', strtolower($headerName));

                $headers[$headerName] = $value;
            }
        }

        // Content-Type ve Content-Length HTTP_ ön eki olmadan gelebilir.
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['content-type'] = (string) $_SERVER['CONTENT_TYPE'];
        }

        if (isset($_SERVER['CONTENT_LENGTH'])) {
            $headers['content-length'] = (string) $_SERVER['CONTENT_LENGTH'];
        }

        return $headers;
    }

    /**
     * JSON veya klasik form verilerini okur.
     *
     * @param array<string, string> $headers
     * @return array<string, mixed>
     */
    private static function captureBody(string $method, array $headers): array
    {
        // GET ve HEAD isteklerinde body okunmaz.
        if (in_array($method, ['GET', 'HEAD'], true)) {
            return [];
        }

        $contentType = strtolower($headers['content-type'] ?? '');
        $contentType = trim(explode(';', $contentType)[0]);

        // JSON olmayan klasik form istekleri PHP tarafından $_POST içine yazılır.
        if ($contentType !== 'application/json') {
            return $_POST;
        }

        $contentLength = (int) ($headers['content-length'] ?? 0);

        if ($contentLength > self::MAX_JSON_BODY_SIZE) {
            throw new InvalidArgumentException(
                'Gönderilen JSON verisi izin verilen 1 MB sınırını aşıyor.'
            );
        }

        $rawBody = file_get_contents('php://input');

        if ($rawBody === false || trim($rawBody) === '') {
            return [];
        }

        if (strlen($rawBody) > self::MAX_JSON_BODY_SIZE) {
            throw new InvalidArgumentException(
                'Gönderilen JSON verisi izin verilen 1 MB sınırını aşıyor.'
            );
        }

        try {
            $decodedBody = json_decode(
                $rawBody,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            throw new InvalidArgumentException(
                'Gönderilen JSON verisi geçerli değil.',
                previous: $exception
            );
        }

        if (!is_array($decodedBody)) {
            throw new InvalidArgumentException(
                'JSON isteğinin kök değeri bir nesne olmalıdır.'
            );
        }

        return $decodedBody;
    }
}