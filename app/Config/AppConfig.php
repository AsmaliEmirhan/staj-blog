<?php

declare(strict_types=1);

namespace App\Config;

use InvalidArgumentException;
use UnexpectedValueException;

/**
 * Uygulama ortam değişkenlerine güvenli ve tür kontrollü erişim sağlar.
 */
final class AppConfig
{
    /**
     * Bu sınıf yalnızca statik metotlarla kullanılacağı için nesne oluşturulmasını engeller.
     */
    private function __construct()
    {
    }

    /**
     * Ortam değişkenini metin olarak döndürür.
     */
    public static function string(string $key, ?string $default = null): string
    {
        $value = $_ENV[$key] ?? $default;

        if ($value === null) {
            throw new InvalidArgumentException(
                sprintf('Gerekli ortam değişkeni tanımlı değil: %s', $key)
            );
        }

        return (string) $value;
    }

    /**
     * Ortam değişkenini boolean değerine dönüştürür.
     */
    public static function boolean(string $key, ?bool $default = null): bool
    {
        $value = $_ENV[$key] ?? null;

        if ($value === null) {
            if ($default !== null) {
                return $default;
            }

            throw new InvalidArgumentException(
                sprintf('Gerekli ortam değişkeni tanımlı değil: %s', $key)
            );
        }

        $booleanValue = filter_var(
            $value,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );

        if ($booleanValue === null) {
            throw new UnexpectedValueException(
                sprintf('%s ortam değişkeni boolean olmalıdır.', $key)
            );
        }

        return $booleanValue;
    }

    /**
     * Ortam değişkenini tam sayıya dönüştürür.
     */
    public static function integer(string $key, ?int $default = null): int
    {
        $value = $_ENV[$key] ?? null;

        if ($value === null) {
            if ($default !== null) {
                return $default;
            }

            throw new InvalidArgumentException(
                sprintf('Gerekli ortam değişkeni tanımlı değil: %s', $key)
            );
        }

        $integerValue = filter_var($value, FILTER_VALIDATE_INT);

        if ($integerValue === false) {
            throw new UnexpectedValueException(
                sprintf('%s ortam değişkeni tam sayı olmalıdır.', $key)
            );
        }

        return $integerValue;
    }

    /**
     * Uygulamanın belirtilen ortamda çalışıp çalışmadığını kontrol eder.
     */
    public static function isEnvironment(string $environment): bool
    {
        return self::string('APP_ENV') === $environment;
    }

    /**
     * Uygulamanın üretim ortamında olup olmadığını kontrol eder.
     */
    public static function isProduction(): bool
    {
        return self::isEnvironment('production');
    }

    /**
     * Hata ayıklama modunun açık olup olmadığını döndürür.
     */
    public static function debugEnabled(): bool
    {
        return self::boolean('APP_DEBUG');
    }
}