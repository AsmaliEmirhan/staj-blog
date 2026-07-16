<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Blog yazılarına yapılan yorumları saklayan tabloyu oluşturur.
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table): void {
            $table->id();

            /*
             * Yorumun ait olduğu yazıyı belirtir.
             * Yazı kalıcı olarak silinirse yorumları da kaldırılır.
             */
            $table->foreignId('post_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
             * Yorumu yazan kullanıcıyı belirtir.
             * Kullanıcı silinirse yorum korunur ve user_id null olur.
             */
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
             * Bir yoruma cevap verildiyse üst yorumun ID değerini tutar.
             * Üst yorum kalıcı silinirse cevap korunur.
             */
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('comments')
                ->nullOnDelete();

            // Kullanıcının yazdığı yorum içeriğidir.
            $table->text('content');

            /*
             * Yorumun moderasyon durumunu saklar.
             * pending, approved veya rejected değerlerinden biri kullanılır.
             */
            $table->string('status', 20)
                ->default('pending');

            /*
             * Yorumu onaylayan veya reddeden yöneticiyi saklar.
             * Yönetici hesabı silinse bile moderasyon kaydı korunur.
             */
            $table->foreignId('moderated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Yorumun incelendiği tarih ve saati saklar.
            $table->timestamp('moderated_at')->nullable();

            $table->timestamps();

            // Silinen yorumların gerektiğinde geri yüklenmesini sağlar.
            $table->softDeletes();

            /*
             * Bir yazının onaylanmış yorumlarını tarih sırasıyla
             * listeleyen sorguları hızlandırır.
             */
            $table->index(
                ['post_id', 'status', 'created_at'],
                'comments_post_status_created_at_index'
            );
        });
    }

    /**
     * Migration geri alındığında comments tablosunu kaldırır.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
