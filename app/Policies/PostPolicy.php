<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Aktif yöneticilere bütün yazı yetkilerini verir.
     *
     * null dönerse ilgili işlem kendi metodunda kontrol edilir.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin() && $user->isActive()) {
            return true;
        }

        return null;
    }

    /**
     * Yazı listesinin görüntülenmesine izin verir.
     *
     * Listeleme sırasında yayınlanmamış yazıların filtrelenmesi
     * controller sorgusunda ayrıca uygulanacaktır.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Tek bir yazının görüntülenip görüntülenemeyeceğini belirler.
     */
    public function view(?User $user, Post $post): bool
    {
        if ($post->isPublished()) {
            return true;
        }

        return $user !== null
            && $user->isActive()
            && $user->getKey() === $post->user_id;
    }

    /**
     * Kullanıcının yeni yazı oluşturup oluşturamayacağını belirler.
     */
    public function create(User $user): bool
    {
        return $user->isActive();
    }

    /**
     * Kullanıcının yazıyı güncelleyip güncelleyemeyeceğini belirler.
     */
    public function update(User $user, Post $post): bool
    {
        return $user->isActive()
            && $user->getKey() === $post->user_id;
    }

    /**
     * Kullanıcının yazıyı silebilip silemeyeceğini belirler.
     */
    public function delete(User $user, Post $post): bool
    {
        return $user->isActive()
            && $user->getKey() === $post->user_id;
    }

    /**
     * Kullanıcının silinmiş yazıyı geri yükleyip yükleyemeyeceğini belirler.
     */
    public function restore(User $user, Post $post): bool
    {
        return $user->isActive()
            && $user->getKey() === $post->user_id;
    }

    /**
     * Kalıcı silmeyi normal kullanıcılara kapatır.
     *
     * Aktif admin kullanıcılar before metodu üzerinden izin alır.
     */
    public function forceDelete(User $user, Post $post): bool
    {
        return false;
    }
}
