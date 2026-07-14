<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * HTTP durum kodu ve cevap başlıkları taşıyan uygulama hatasıdır.
 */
final class HttpException extends RuntimeException
{
    /**
     * @param int $statusCode HTTP hata durum kodu
     * @param string $message Kullanıcıya gösterilecek hata mesajı
     * @param array<string, string> $headers Cevaba eklenecek HTTP başlıkları
     */
    public function __construct(
        private readonly int $statusCode,
        string $message,
        private readonly array $headers = []
    ) {
        parent::__construct($message, $statusCode);
    }

    /**
     * HTTP durum kodunu döndürür.
     */
    public function statusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Cevaba eklenmesi gereken HTTP başlıklarını döndürür.
     *
     * @return array<string, string>
     */
    public function headers(): array
    {
        return $this->headers;
    }
}