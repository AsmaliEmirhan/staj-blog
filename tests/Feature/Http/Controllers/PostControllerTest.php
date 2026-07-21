<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_ana_sayfa_yalnizca_yayindaki_yazilari_listeler(): void
    {
        $publishedPost = Post::factory()->published()->create([
            'title' => 'Yayındaki Blog Yazısı',
        ]);

        $draftPost = Post::factory()->create([
            'title' => 'Taslak Blog Yazısı',
        ]);

        $futurePost = Post::factory()->create([
            'title' => 'İleri Tarihli Blog Yazısı',
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->addDay(),
        ]);

        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertViewIs('posts.index')
            ->assertViewHas('posts')
            ->assertSee($publishedPost->title)
            ->assertDontSee($draftPost->title)
            ->assertDontSee($futurePost->title);
    }

    public function test_posts_sayfasi_yayindaki_yazilari_listeler(): void
    {
        $post = Post::factory()->published()->create([
            'title' => 'Laravel ile Blog Geliştirme',
        ]);

        $response = $this->get(route('posts.index'));

        $response
            ->assertOk()
            ->assertViewIs('posts.index')
            ->assertSee($post->title);
    }

    public function test_ziyaretci_yayindaki_yazinin_detayini_gorebilir(): void
    {
        $post = Post::factory()->published()->create([
            'title' => 'Herkese Açık Blog Yazısı',
        ]);

        $response = $this->get(route('posts.show', $post));

        $response
            ->assertOk()
            ->assertViewIs('posts.show')
            ->assertViewHas('post', function (Post $viewPost) use ($post): bool {
                return $viewPost->is($post);
            })
            ->assertSee($post->title)
            ->assertSee(nl2br(e($post->content)), false);
    }

    public function test_ziyaretci_taslak_yazinin_detayini_goremez(): void
    {
        $post = Post::factory()->create([
            'status' => Post::STATUS_DRAFT,
            'published_at' => null,
        ]);

        $response = $this->get(route('posts.show', $post));

        $response->assertForbidden();
    }

    public function test_aktif_kullanici_kendi_taslak_yazisini_gorebilir(): void
    {
        $user = User::factory()->create([
            'status' => User::STATUS_ACTIVE,
        ]);

        $post = Post::factory()
            ->for($user, 'author')
            ->create([
                'title' => 'Kullanıcının Taslak Yazısı',
                'status' => Post::STATUS_DRAFT,
                'published_at' => null,
            ]);

        $response = $this
            ->actingAs($user)
            ->get(route('posts.show', $post));

        $response
            ->assertOk()
            ->assertViewIs('posts.show')
            ->assertSee($post->title);
    }

    public function test_oturum_acmamis_kullanici_yazi_olusturma_sayfasina_giremez(): void
    {
        $response = $this->get(route('posts.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_aktif_kullanici_yazi_olusturma_formunu_gorebilir(): void
    {
        $user = User::factory()->create([
            'status' => User::STATUS_ACTIVE,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('posts.create'));

        $response
            ->assertOk()
            ->assertViewIs('posts.create');
    }

    public function test_aktif_kullanici_taslak_yazi_olusturabilir(): void
    {
        $user = User::factory()->create([
            'status' => User::STATUS_ACTIVE,
        ]);

        $postData = [
            'title' => 'Test Blog Yazısı',
            'excerpt' => 'Test yazısının kısa açıklaması.',
            'content' => 'Bu test yazısı, minimum elli karakter doğrulamasını geçecek kadar ayrıntılı bir içeriğe sahiptir.',
            'status' => Post::STATUS_DRAFT,
        ];

        $response = $this
            ->actingAs($user)
            ->post(route('posts.store'), $postData);

        $response->assertSessionHasNoErrors();

        $post = Post::query()
            ->where('title', 'Test Blog Yazısı')
            ->firstOrFail();

        $response
            ->assertRedirect(route('posts.show', $post))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'user_id' => $user->id,
            'title' => 'Test Blog Yazısı',
            'slug' => 'test-blog-yazisi',
            'status' => Post::STATUS_DRAFT,
            'published_at' => null,
        ]);
    }

    public function test_yayindaki_yazi_olusturulurken_yayin_tarihi_kaydedilir(): void
    {
        $user = User::factory()->create([
            'status' => User::STATUS_ACTIVE,
        ]);

        $publishedAt = now()->addHour()->format('Y-m-d\TH:i');

        $response = $this
            ->actingAs($user)
            ->post(route('posts.store'), [
                'title' => 'Yayınlanmış Test Yazısı',
                'excerpt' => 'Yayınlanmış yazının kısa açıklaması.',
                'content' => 'Bu yayınlanmış test yazısı, minimum elli karakter koşulunu karşılayacak uzunlukta hazırlanmıştır.',
                'status' => Post::STATUS_PUBLISHED,
                'published_at' => $publishedAt,
            ]);

        $response->assertSessionHasNoErrors();

        $post = Post::query()
            ->where('title', 'Yayınlanmış Test Yazısı')
            ->firstOrFail();

        $response->assertRedirect(route('posts.show', $post));

        $this->assertSame(Post::STATUS_PUBLISHED, $post->status);
        $this->assertNotNull($post->published_at);
        $this->assertSame(
            $publishedAt,
            $post->published_at->format('Y-m-d\TH:i'),
        );
    }

    public function test_yazi_olustururken_zorunlu_alanlar_dogrulanir(): void
    {
        $user = User::factory()->create([
            'status' => User::STATUS_ACTIVE,
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('posts.create'))
            ->post(route('posts.store'), []);

        $response
            ->assertRedirect(route('posts.create'))
            ->assertSessionHasErrors([
                'title',
                'content',
            ])
            ->assertSessionDoesntHaveErrors('status');

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_gecersiz_yazi_durumu_kabul_edilmez(): void
    {
        $user = User::factory()->create([
            'status' => User::STATUS_ACTIVE,
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('posts.create'))
            ->post(route('posts.store'), [
                'title' => 'Geçersiz Durum Testi',
                'excerpt' => 'Geçersiz durum kontrolü.',
                'content' => 'Bu içerik veritabanına kaydedilmemelidir.',
                'status' => 'invalid-status',
            ]);

        $response
            ->assertRedirect(route('posts.create'))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseMissing('posts', [
            'title' => 'Geçersiz Durum Testi',
        ]);
    }
}
