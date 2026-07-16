<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Blog yazılarının saklanacağı posts tablosunu oluşturur.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table): void {
            $table->id();

            /*
             * Yazının yazarını belirtir.
             * Yazarı bulunan kullanıcı, yazıları varken silinemez.
             */
            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();

            /*
             * Yazının kategorisini belirtir.
             * Kategori silinirse yazı korunur ve category_id null olur.
             */
            $table->foreignId('category_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Yazının kullanıcıya gösterilecek başlığıdır.
            $table->string('title', 200);

            // SEO uyumlu ve benzersiz yazı adresidir.
            $table->string('slug', 220)->unique();

            // Listeleme sayfalarında gösterilecek kısa özettir.
            $table->text('excerpt')->nullable();

            // Blog yazısının asıl içeriğini saklar.
            $table->longText('content');

            // Yazının kapak görselinin dosya yoludur.
            $table->string('featured_image')->nullable();

            // Yazının taslak, yayında veya arşivlenmiş durumunu saklar.
            $table->string('status', 20)->default('draft');

            // İçeriğin yapay zekâ yardımıyla üretildiğini belirtir.
            $table->boolean('is_ai_generated')->default(false);

            // AI Bot'a gönderilen anahtar kelimeleri JSON olarak saklar.
            $table->json('ai_keywords')->nullable();

            // Yazının yayınlandığı veya yayınlanacağı zamanı saklar.
            $table->timestamp('published_at')->nullable();

            // Yazının toplam görüntülenme sayısını tutar.
            $table->unsignedBigInteger('view_count')->default(0);

            $table->timestamps();

            // Silinen yazıların geri yüklenebilmesini sağlar.
            $table->softDeletes();

            /*
             * Ana sayfadaki yayın sorgularını hızlandırır.
             * Önce durum, ardından yayın tarihi üzerinden arama yapılır.
             */
            $table->index(
                ['status', 'published_at'],
                'posts_status_published_at_index'
            );

            // AI ile oluşturulan yazıların filtrelenmesini hızlandırır.
            $table->index('is_ai_generated');
        });
    }

    /**
     * Migration geri alındığında posts tablosunu kaldırır.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
