<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Blog yazılarıyla etiketler arasındaki çoktan çoğa ilişkiyi oluşturur.
     */
    public function up(): void
    {
        Schema::create('post_tag', function (Blueprint $table): void {
            /*
             * Yazı silindiğinde bu yazıya ait etiket bağlantılarını
             * otomatik olarak kaldırır.
             */
            $table->foreignId('post_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
             * Etiket silindiğinde etikete ait yazı bağlantılarını
             * otomatik olarak kaldırır.
             */
            $table->foreignId('tag_id')
                ->constrained()
                ->cascadeOnDelete();

            // Etiketin yazıya ne zaman bağlandığını takip eder.
            $table->timestamps();

            /*
             * Aynı etiketin aynı yazıya ikinci kez eklenmesini engeller.
             * İki foreign key birlikte tablonun primary key değeridir.
             */
            $table->primary(['post_id', 'tag_id']);
        });
    }

    /**
     * Migration geri alındığında post_tag ara tablosunu kaldırır.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_tag');
    }
};
