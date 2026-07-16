<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Blog yazılarının gruplandırılacağı kategoriler tablosunu oluşturur.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();

            // Kategorinin kullanıcıya gösterilecek adıdır.
            $table->string('name', 100)->unique();

            // Kategori sayfasının SEO uyumlu URL değeridir.
            $table->string('slug', 120)->unique();

            // Kategoriyi açıklayan isteğe bağlı metindir.
            $table->text('description')->nullable();

            // Pasif kategorilerin sitede gösterilmesini engeller.
            $table->boolean('is_active')
                ->default(true)
                ->index();

            // Kategorilerin hangi sırada gösterileceğini belirler.
            $table->unsignedInteger('sort_order')
                ->default(0)
                ->index();

            $table->timestamps();
        });
    }

    /**
     * Migration geri alındığında categories tablosunu kaldırır.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
