<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bildirimler tablosunu oluşturur.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table): void {
            $table->id();

            // Bildirimin gönderildiği kullanıcı.
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Bildirimi tetikleyen kullanıcı. Sistem bildirimlerinde boş olabilir.
            $table->foreignId('actor_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Bildirimin türü.
            $table->string('type', 50);

            // Bildirimle ilişkili yazı, yorum veya başka bir model.
            $table->nullableMorphs('subject');

            // Kullanıcıya gösterilecek içerik.
            $table->string('title', 150);
            $table->text('message');

            // Bildirime özel ek veriler.
            $table->json('data')->nullable();

            // Boşsa bildirim henüz okunmamıştır.
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            // Okunmamış bildirimleri hızlı sıralamak için.
            $table->index(
                ['user_id', 'read_at', 'created_at'],
                'notifications_user_read_created_index'
            );

            $table->index('type');
        });
    }

    /**
     * Bildirimler tablosunu kaldırır.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
