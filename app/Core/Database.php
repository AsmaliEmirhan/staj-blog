<?php

declare(strict_types=1);

namespace App\Core;

use App\Config\AppConfig;
use PDO;

/**
 * Uygulamanın MySQL bağlantısını PDO üzerinden yönetir.
 */
final class Database
{
    /**
     * Açılan PDO bağlantısını saklar.
     *
     * Böylece aynı HTTP isteği içerisinde tekrar tekrar
     * yeni veritabanı bağlantısı oluşturulmaz.
     */
    private static ?PDO $connection = null;

    /**
     * Bu sınıf statik olarak kullanılacağı için nesne oluşturulmasını engeller.
     */
    private function __construct()
    {
    }

    /**
     * Hazır bir bağlantı varsa onu, yoksa yeni PDO bağlantısını döndürür.
     */
    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        // Bağlantı bilgilerini .env dosyasından güvenli biçimde okur.
        $host = AppConfig::string('DB_HOST');
        $port = AppConfig::integer('DB_PORT');
        $database = AppConfig::string('DB_DATABASE');
        $username = AppConfig::string('DB_USERNAME');
        $password = AppConfig::string('DB_PASSWORD');
        $charset = AppConfig::string('DB_CHARSET', 'utf8mb4');

        // PDO'nun MySQL sunucusuna bağlanırken kullanacağı bağlantı adresi.
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $host,
            $port,
            $database,
            $charset
        );

        self::$connection = new PDO(
            $dsn,
            $username,
            $password,
            [
                // SQL hatalarının exception olarak yakalanmasını sağlar.
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

                // Sonuçların varsayılan olarak ilişkisel dizi dönmesini sağlar.
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

                // Sorguların MySQL tarafından gerçek prepared statement olarak hazırlanmasını sağlar.
                PDO::ATTR_EMULATE_PREPARES => false,

                // Sayısal değerlerin gereksiz yere metne dönüştürülmesini önler.
                PDO::ATTR_STRINGIFY_FETCHES => false,
            ]
        );

        return self::$connection;
    }
}