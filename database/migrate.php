<?php

declare(strict_types=1);

use App\Core\Database;


// Uygulama ayarlarını, Composer autoload sistemini ve hata yöneticisini başlatır.
require dirname(__DIR__) . '/bootstrap/app.php';

// Daha önce hazırladığımız PDO bağlantısını alır.
$pdo = Database::connection();

/*
 * Çalıştırılmış migration dosyalarını kaydeden tabloyu oluşturur.
 *
 * UNIQUE kuralı, aynı migration dosyasının ikinci kez kaydedilmesini engeller.
 */
$pdo->exec(
    <<<'SQL'
    CREATE TABLE IF NOT EXISTS migrations (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL UNIQUE,
        executed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB
      DEFAULT CHARACTER SET utf8mb4
      COLLATE utf8mb4_unicode_ci
    SQL
);

// Daha önce çalıştırılmış migration dosyalarının isimlerini okur.
$executedMigrations = $pdo
    ->query('SELECT migration FROM migrations ORDER BY id')
    ->fetchAll(PDO::FETCH_COLUMN);

// Migration klasöründeki bütün PHP dosyalarını bulur.
$migrationFiles = glob(__DIR__ . '/migrations/*.php') ?: [];

// Migration dosyalarının her ortamda aynı sırayla çalışmasını sağlar.
sort($migrationFiles);

$executedCount = 0;

foreach ($migrationFiles as $migrationFile) {
    $migrationName = basename($migrationFile);

    // Daha önce çalıştırılan migration dosyasını atlar.
    if (in_array($migrationName, $executedMigrations, true)) {
        continue;
    }

    /*
     * Her migration dosyasının çalıştırılabilir bir fonksiyon
     * döndürmesini bekler.
     */
    $migration = require $migrationFile;

    if (!is_callable($migration)) {
        throw new RuntimeException(
            sprintf(
                '%s migration dosyası çalıştırılabilir bir fonksiyon döndürmelidir.',
                $migrationName
            )
        );
    }

    // Migration içerisindeki tablo oluşturma işlemini çalıştırır.
    $migration($pdo);

    // Migration başarıyla tamamlandığında kayıt tablosuna ekler.
    $statement = $pdo->prepare(
        'INSERT INTO migrations (migration) VALUES (:migration)'
    );

    $statement->execute([
        'migration' => $migrationName,
    ]);

    $executedCount++;

    echo sprintf("Çalıştırıldı: %s%s", $migrationName, PHP_EOL);
}

if ($executedCount === 0) {
    echo 'Çalıştırılmayı bekleyen migration bulunamadı.' . PHP_EOL;
} else {
    echo sprintf(
        'Toplam %d migration başarıyla çalıştırıldı.%s',
        $executedCount,
        PHP_EOL
    );
}