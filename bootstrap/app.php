<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use App\Core\ErrorHandler;


// Projenin ana klasör yolunu belirler.
$rootPath = dirname(__DIR__);

// Composer tarafından oluşturulan otomatik sınıf yükleyiciyi başlatır.
require_once $rootPath . '/vendor/autoload.php';

// Ortam değişkenlerini proje kökündeki .env dosyasından yükler.
$dotenv = Dotenv::createImmutable($rootPath);
$dotenv->safeLoad();

// Boş bırakılmaması gereken temel uygulama ayarlarını doğrular.
$dotenv->required([
    'APP_NAME',
    'APP_ENV',
    'APP_DEBUG',
    'APP_URL',
    'APP_TIMEZONE',
    'APP_KEY',
    'DB_HOST',
    'DB_PORT',
    'DB_DATABASE',
    'DB_USERNAME',
    'SESSION_NAME',
    'SESSION_LIFETIME',
    'SESSION_SECURE_COOKIE',
    'AI_DAILY_LIMIT',
    'LOG_LEVEL',
])->notEmpty();

// Yerel ortamda boş bırakılmasına izin verilen değişkenlerin tanımlı olduğunu kontrol eder.
$dotenv->required([
    'DB_PASSWORD',
    'AI_PROVIDER',
    'AI_API_KEY',
    'AI_MODEL',
]);

// Uygulama ortamının yalnızca izin verilen değerlerden biri olmasını sağlar.
$dotenv->required('APP_ENV')->allowedValues([
    'local',
    'testing',
    'production',
]);

// Boolean olması gereken ayarları doğrular.
$dotenv->required('APP_DEBUG')->isBoolean();
$dotenv->required('SESSION_SECURE_COOKIE')->isBoolean();

// Sayısal olması gereken ayarları doğrular.
$dotenv->required('DB_PORT')->isInteger();
$dotenv->required('SESSION_LIFETIME')->isInteger();
$dotenv->required('AI_DAILY_LIMIT')->isInteger();

// PHP'nin tarih ve saat işlemlerinde kullanacağı zaman dilimini ayarlar.
date_default_timezone_set($_ENV['APP_TIMEZONE']);
// PHP hatalarını ve exception'ları merkezi olarak yönetecek sistemi başlatır.
ErrorHandler::register($rootPath);
// Başlangıç dosyasını çağıran kodun proje ana yoluna erişmesini sağlar.
return $rootPath;