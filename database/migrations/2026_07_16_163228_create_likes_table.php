<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kullanıcıların blog yazılarına yaptığı beğenileri saklar.
     */
    public function up(): void
    {
        Schema::create('likes', function (Blueprint $table): void {
            $table->id();

            // Beğeniyi yapan kullanıcıyı belirtir.
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Beğenilen blog yazısını belirtir.
            $table->foreignId('post_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();

            /*
             * Bir kullanıcının aynı yazıyı yalnızca bir kez
             * beğenebilmesini sağlar.
             */
            $table->unique(
                ['user_id', 'post_id'],
                'likes_user_post_unique'
            );
        });
    }

    /**
     * Migration geri alındığında likes tablosunu kaldırır.
     */
    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
