<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Blog yazılarında kullanılacak etiketleri saklayan tabloyu oluşturur.
     */
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table): void {
            $table->id();

            // Etiketin kullanıcıya gösterilecek benzersiz adıdır.
            $table->string('name', 50)->unique();

            // Etiket sayfasında kullanılacak SEO uyumlu URL değeridir.
            $table->string('slug', 70)->unique();

            $table->timestamps();
        });
    }

    /**
     * Migration geri alındığında tags tablosunu kaldırır.
     */
    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
