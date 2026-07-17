<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Yapay zekâ üretim kayıtları tablosunu oluşturur.
     */
    public function up(): void
    {
        Schema::create('ai_generations', function (Blueprint $table): void {
            $table->id();

            // AI isteğini başlatan kullanıcı silinse de işlem kaydı korunur.
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Üretim belirli bir yazıyla ilgiliyse bağlantısı saklanır.
            $table->foreignId('post_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // article, title, summary, tags veya improvement gibi işlem türü.
            $table->string('type', 50);

            // Kullanılan AI sağlayıcısı ve model.
            $table->string('provider', 50)->default('openai');
            $table->string('model', 100);

            // Sağlayıcı tarafından döndürülen benzersiz istek kimliği.
            $table->string('request_id', 150)->nullable()->unique();

            // Modele gönderilen komut ve ek giriş verileri.
            $table->longText('prompt');
            $table->json('input')->nullable();

            // Modelin ürettiği sonuç.
            $table->longText('output')->nullable();

            // pending, processing, completed veya failed.
            $table->string('status', 20)->default('pending');

            // Kullanılan token miktarları.
            $table->unsignedInteger('prompt_tokens')->default(0);
            $table->unsignedInteger('completion_tokens')->default(0);
            $table->unsignedInteger('total_tokens')->default(0);

            // Tahmini maliyet ve işlem süresi.
            $table->decimal('estimated_cost', 12, 6)->nullable();
            $table->unsignedInteger('duration_ms')->nullable();

            // Başarısız işlemlerde hata açıklaması.
            $table->text('error_message')->nullable();

            // İşlemin başlama ve tamamlanma zamanları.
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(
                ['user_id', 'status', 'created_at'],
                'ai_generations_user_status_created_index'
            );

            $table->index(
                ['type', 'status'],
                'ai_generations_type_status_index'
            );
        });
    }

    /**
     * Yapay zekâ üretim kayıtları tablosunu kaldırır.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_generations');
    }
};
