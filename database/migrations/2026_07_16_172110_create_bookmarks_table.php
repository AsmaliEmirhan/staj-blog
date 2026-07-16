<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kullanıcıların daha sonra okumak için kaydettiği yazıları saklar.
     */
    public function up(): void
    {
        Schema::create('bookmarks', function (Blueprint $table): void {
            $table->id();

            // Yazıyı kaydeden kullanıcıyı belirtir.
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Kaydedilen blog yazısını belirtir.
            $table->foreignId('post_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();

            /*
             * Bir kullanıcının aynı yazıyı kaydedilenler listesine
             * yalnızca bir kez ekleyebilmesini sağlar.
             */
            $table->unique(
                ['user_id', 'post_id'],
                'bookmarks_user_post_unique'
            );
        });
    }

    /**
     * Migration geri alındığında bookmarks tablosunu kaldırır.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookmarks');
    }
};
