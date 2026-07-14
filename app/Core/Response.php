<?php

declare(strict_types=1);

namespace App\Core;

use InvalidArgumentException;

/**
 * HTTP cevaplarını standart ve tekrar kullanılabilir biçimde oluşturur.
 */
final class Response
{
    /**
     * Response nesnesinin yalnızca sınıf içindeki factory metotlarıyla
     * oluşturulmasını sağlar.
     *
     * @param array<string, string> $headers
     */
    private function __construct(
        private readonly string $content,
        private readonly int $statusCode,
        private readonly array $headers
    ) {
        if ($statusCode < 100 || $statusCode > 599) {
            throw new InvalidArgumentException(
                'HTTP durum kodu 100 ile 599 arasında olmalıdır.'
            );
        }
    }

    /**
     * Verilen içeriği JSON cevabına dönüştürür.
     *
     * @param array<string, mixed> $payload
     */
    public static function json(array $payload, int $statusCode = 200): self
    {
        $content = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_THROW_ON_ERROR
        );

        return new self(
            content: $content,
            statusCode: $statusCode,
            headers: [
                'Content-Type' => 'application/json; charset=UTF-8',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    /**
     * Başarılı API işlemleri için standart cevap oluşturur.
     */
    public static function success(
        mixed $data = null,
        string $message = 'İşlem başarılı.',
        int $statusCode = 200
    ): self {
        return self::json(
            payload: [
                'success' => true,
                'message' => $message,
                'data' => $data,
            ],
            statusCode: $statusCode
        );
    }

    /**
     * Başarısız API işlemleri için standart hata cevabı oluşturur.
     *
     * @param array<string, mixed>|null $errors
     */
    public static function error(
        string $message,
        int $statusCode = 400,
        ?array $errors = null
    ): self {
        return self::json(
            payload: [
                'success' => false,
                'message' => $message,
                'errors' => $errors,
            ],
            statusCode: $statusCode
        );
    }

    /**
     * Hazırlanan HTTP durumunu, header'ları ve içeriği istemciye gönderir.
     */
    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->statusCode);

            foreach ($this->headers as $name => $value) {
                header(sprintf('%s: %s', $name, $value));
            }
        }

        echo $this->content;
    }

    /**
     * Testlerde cevap içeriğini inceleyebilmek için döndürür.
     */
    public function content(): string
    {
        return $this->content;
    }

    /**
     * Testlerde HTTP durum kodunu inceleyebilmek için döndürür.
     */
    public function statusCode(): int
    {
        return $this->statusCode;
    }
}