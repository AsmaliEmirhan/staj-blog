<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Yazı ve yorum şikâyetlerini saklayan reports tablosunu oluşturur.
     */
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table): void {
            $table->id();

            /*
             * Şikâyeti oluşturan kullanıcıyı belirtir.
             * Kullanıcı silinirse şikâyet kaydı moderasyon geçmişi
             * için korunur ve user_id null olur.
             */
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
             * Şikâyetin yazıya mı yoksa yoruma mı ait olduğunu saklar.
             *
             * reportable_type: App\Models\Post veya App\Models\Comment
             * reportable_id: İlgili yazı veya yorumun ID değeri
             */
            $table->morphs('reportable');

            // Spam, taciz, yanlış bilgi veya diğer şikâyet nedenini saklar.
            $table->string('reason', 50);

            // Kullanıcının şikâyetle ilgili ek açıklamasıdır.
            $table->text('description')->nullable();

            /*
             * Şikâyetin moderasyon durumunu saklar.
             * pending, reviewing, resolved veya dismissed kullanılır.
             */
            $table->string('status', 20)
                ->default('pending');

            // Şikâyeti inceleyen yönetici kullanıcıyı saklar.
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Şikâyetin incelendiği tarih ve saati saklar.
            $table->timestamp('reviewed_at')->nullable();

            // Yöneticinin şikâyet hakkında verdiği karar açıklamasıdır.
            $table->text('resolution_note')->nullable();

            $table->timestamps();

            /*
             * Aynı kullanıcının aynı içeriği birden fazla kez
             * şikâyet etmesini engeller.
             */
            $table->unique(
                ['user_id', 'reportable_type', 'reportable_id'],
                'reports_user_reportable_unique'
            );

            // Bekleyen şikâyetleri tarih sırasıyla getirmeyi hızlandırır.
            $table->index(
                ['status', 'created_at'],
                'reports_status_created_at_index'
            );
        });
    }

    /**
     * Migration geri alındığında reports tablosunu kaldırır.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
