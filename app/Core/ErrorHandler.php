<?php

declare(strict_types=1);

namespace App\Core;

use App\Config\AppConfig;
use App\Exceptions\HttpException;
use ErrorException;
use RuntimeException;
use Throwable;

/**
 * Uygulamadaki PHP hatalarını ve exception'ları merkezi olarak yönetir.
 */
final class ErrorHandler
{
    /**
     * Hataların kaydedileceği dosya yolu.
     */
    private static string $logFile = '';

    /**
     * Handler'ın birden fazla kez kaydedilmesini engeller.
     */
    private static bool $registered = false;

    /**
     * Bu sınıftan nesne oluşturulmasını engeller.
     */
    private function __construct()
    {
    }

    /**
     * PHP hata, exception ve kapanış handler'larını kaydeder.
     */
    public static function register(string $rootPath): void
    {
        if (self::$registered) {
            return;
        }

        $logDirectory = $rootPath . '/storage/logs';

        if (
            !is_dir($logDirectory)
            && !mkdir($logDirectory, 0755, true)
            && !is_dir($logDirectory)
        ) {
            throw new RuntimeException(
                'Log klasörü oluşturulamadı.'
            );
        }

        self::$logFile = $logDirectory . '/app.log';

        // PHP'nin hataları doğrudan HTML olarak göstermesini engeller.
        ini_set('display_errors', '0');
        ini_set('html_errors', '0');

        // Bütün PHP hata seviyelerinin yakalanmasını sağlar.
        error_reporting(E_ALL);

        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);

        self::$registered = true;
    }

    /**
     * PHP warning ve notice seviyelerini ErrorException'a dönüştürür.
     */
    public static function handleError(
        int $severity,
        string $message,
        string $file,
        int $line
    ): bool {
        // @ ile bilinçli olarak gizlenen hataları dönüştürmez.
        if ((error_reporting() & $severity) === 0) {
            return false;
        }

        throw new ErrorException(
            message: $message,
            code: 0,
            severity: $severity,
            filename: $file,
            line: $line
        );
    }

    /**
     * Yakalanmamış exception'ları güvenli JSON cevaplarına dönüştürür.
     */
    public static function handleException(Throwable $exception): void
    {
        if ($exception instanceof HttpException) {
            self::sendHttpException($exception);

            return;
        }

        // Beklenmeyen sunucu hatalarını log dosyasına kaydeder.
        self::writeLog($exception);

        $message = 'Beklenmeyen bir sunucu hatası oluştu.';
        $errors = null;

        // Teknik bilgiler yalnızca geliştirme ortamında gösterilir.
        if (AppConfig::debugEnabled()) {
            $message = $exception->getMessage();

            $errors = [
                'type' => $exception::class,
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
        }

        Response::error(
            message: $message,
            statusCode: 500,
            errors: $errors
        )->send();
    }

    /**
     * PHP'nin normal handler'ların çalışamadığı ölümcül hatalarını yakalar.
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error === null) {
            return;
        }

        $fatalErrorTypes = [
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_COMPILE_ERROR,
            E_USER_ERROR,
        ];

        if (!in_array($error['type'], $fatalErrorTypes, true)) {
            return;
        }

        $exception = new ErrorException(
            message: $error['message'],
            code: 0,
            severity: $error['type'],
            filename: $error['file'],
            line: $error['line']
        );

        self::handleException($exception);
    }

    /**
     * Kontrollü HTTP hatalarını durum kodu ve başlıklarıyla gönderir.
     */
    private static function sendHttpException(
        HttpException $exception
    ): void {
        if (!headers_sent()) {
            foreach ($exception->headers() as $name => $value) {
                header(sprintf('%s: %s', $name, $value));
            }
        }

        Response::error(
            message: $exception->getMessage(),
            statusCode: $exception->statusCode()
        )->send();
    }

    /**
     * Beklenmeyen hatayı tarih ve stack trace bilgisiyle log dosyasına yazar.
     */
    private static function writeLog(Throwable $exception): void
    {
        $logMessage = sprintf(
            "[%s] %s: %s in %s:%d\n%s\n\n",
            date(DATE_ATOM),
            $exception::class,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        // Log yazılamaması durumunda yeni bir hata döngüsü oluşmasını engeller.
        @file_put_contents(
            self::$logFile,
            $logMessage,
            FILE_APPEND | LOCK_EX
        );
    }
}