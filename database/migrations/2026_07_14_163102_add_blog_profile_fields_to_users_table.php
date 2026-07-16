<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Blog projesinde ihtiyaç duyulan kullanıcı alanlarını ekler.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // Profil adresinde kullanılacak benzersiz kullanıcı adı.
            $table->string('username', 50)
                ->unique()
                ->after('name');

            // Kullanıcının profil fotoğrafının dosya yolunu saklar.
            $table->string('avatar')
                ->nullable()
                ->after('password');

            // Kullanıcının kendisini tanıtabileceği profil açıklamasıdır.
            $table->text('bio')
                ->nullable()
                ->after('avatar');

            // Normal kullanıcı ve yönetici yetkilerini birbirinden ayırır.
            $table->string('role', 20)
                ->default('user')
                ->index()
                ->after('bio');

            // Kullanıcı hesabının aktif veya pasif durumunu saklar.
            $table->string('status', 20)
                ->default('active')
                ->index()
                ->after('role');
        });
    }

    /**
     * Migration geri alındığında eklenen alanları kaldırır.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // Önce alanlara bağlı indeksleri kaldırır.
            $table->dropUnique(['username']);
            $table->dropIndex(['role']);
            $table->dropIndex(['status']);

            // Ardından bu migration ile eklenen sütunları kaldırır.
            $table->dropColumn([
                'username',
                'avatar',
                'bio',
                'role',
                'status',
            ]);
        });
    }
};