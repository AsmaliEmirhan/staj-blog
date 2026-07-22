<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_yazi_olusturulurken_one_cikan_gorsel_kaydedilir(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'status' => User::STATUS_ACTIVE,
        ]);

        $image = UploadedFile::fake()
            ->image('kapak.jpg', 1200, 630)
            ->size(500);

        $response = $this
            ->actingAs($user)
            ->post(route('posts.store'), [
                'title' => 'Görselli Test Yazısı',
                'excerpt' => 'Öne çıkan görsel yükleme testi.',
                'content' => 'Bu içerik, yazı oluşturulurken öne çıkan görselin güvenli biçimde kaydedildiğini doğrulamak için hazırlanmıştır.',
                'featured_image' => $image,
                'status' => Post::STATUS_DRAFT,
            ]);

        $response->assertSessionHasNoErrors();

        $post = Post::query()
            ->where('title', 'Görselli Test Yazısı')
            ->firstOrFail();

        $response->assertRedirect(route('posts.show', $post));

        $this->assertNotNull($post->featured_image);
        $this->assertStringStartsWith('posts/', $post->featured_image);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'featured_image' => $post->featured_image,
        ]);

        Storage::disk('public')->assertExists($post->featured_image);
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

    public function test_yazi_olusturulurken_secilen_etiketler_kaydedilir(): void
    {
        $user = User::factory()->create([
            'status' => User::STATUS_ACTIVE,
        ]);

        $tags = Tag::factory()->count(2)->create();

        $response = $this
            ->actingAs($user)
            ->post(route('posts.store'), [
                'title' => 'Etiketli Test Yazısı',
                'excerpt' => 'Seçilen etiketlerin kaydedilmesini test ediyoruz.',
                'content' => 'Bu içerik, yazı oluşturulurken seçilen etiketlerin doğru şekilde kaydedildiğini doğrulayacak uzunluktadır.',
                'status' => Post::STATUS_DRAFT,
                'tag_ids' => $tags->modelKeys(),
            ]);

        $response->assertSessionHasNoErrors();

        $post = Post::query()
            ->where('title', 'Etiketli Test Yazısı')
            ->firstOrFail();

        $response->assertRedirect(route('posts.show', $post));

        foreach ($tags as $tag) {
            $this->assertDatabaseHas('post_tag', [
                'post_id' => $post->id,
                'tag_id' => $tag->id,
            ]);
        }

        $this->assertEqualsCanonicalizing(
            $tags->modelKeys(),
            $post->tags()->pluck('tags.id')->all(),
        );
    }

    public function test_yazi_duzenlenirken_etiketler_guncellenir(): void
    {
        $user = User::factory()->create([
            'status' => User::STATUS_ACTIVE,
        ]);

        $post = Post::factory()
            ->for($user, 'author')
            ->create();

        $oldTag = Tag::factory()->create();
        $newTags = Tag::factory()->count(2)->create();

        $post->tags()->attach($oldTag->id);

        $response = $this
            ->actingAs($user)
            ->put(route('posts.update', $post), [
                'title' => 'Etiketleri Güncellenen Yazı',
                'excerpt' => 'Yazının etiketleri düzenleme ekranından güncellendi.',
                'content' => 'Bu içerik, yazı düzenlenirken eski etiketlerin kaldırılıp yeni etiketlerin kaydedildiğini doğrulamak için hazırlanmıştır.',
                'status' => Post::STATUS_DRAFT,
                'tag_ids' => $newTags->modelKeys(),
            ]);

        $response->assertSessionHasNoErrors();

        $post->refresh();

        $response->assertRedirect(route('posts.show', $post));

        $this->assertDatabaseMissing('post_tag', [
            'post_id' => $post->id,
            'tag_id' => $oldTag->id,
        ]);

        foreach ($newTags as $newTag) {
            $this->assertDatabaseHas('post_tag', [
                'post_id' => $post->id,
                'tag_id' => $newTag->id,
            ]);
        }

        $this->assertEqualsCanonicalizing(
            $newTags->modelKeys(),
            $post->tags()->pluck('tags.id')->all(),
        );
    }

    public function test_yazi_aramasi_baslik_ve_icerige_gore_sonuclari_filtreler(): void
    {
        $titleMatch = Post::factory()->published()->create([
            'title' => 'Laravel ile Güvenli Uygulama Geliştirme',
            'content' => 'Bu yazı güvenli web uygulamalarını açıklamaktadır.',
        ]);

        $contentMatch = Post::factory()->published()->create([
            'title' => 'PHP Uygulama Rehberi',
            'content' => 'Bu içerikte Laravel kullanımı ayrıntılı olarak anlatılmaktadır.',
        ]);

        $unmatchedPost = Post::factory()->published()->create([
            'title' => 'Veritabanı Tasarımı',
            'content' => 'Bu içerikte ilişkisel veritabanları anlatılmaktadır.',
        ]);

        $response = $this->get(route('posts.index', [
            'search' => 'Laravel',
        ]));

        $response
            ->assertOk()
            ->assertViewIs('posts.index')
            ->assertViewHas('search', 'Laravel')
            ->assertSee($titleMatch->title)
            ->assertSee($contentMatch->title)
            ->assertDontSee($unmatchedPost->title);
    }

    public function test_yazi_sayfalamasinda_arama_parametresi_korunur(): void
    {
        foreach (range(1, 11) as $number) {
            Post::factory()->published()->create([
                'title' => "Laravel Sayfalama Yazısı {$number}",
                'published_at' => now()->subMinutes($number),
            ]);
        }

        $unmatchedPost = Post::factory()->published()->create([
            'title' => 'PHP Sayfalama Yazısı',
            'content' => 'Bu içerik arama sonucuna dahil edilmemelidir.',
        ]);

        $firstPageResponse = $this->get(route('posts.index', [
            'search' => 'Laravel',
        ]));

        $firstPageResponse
            ->assertOk()
            ->assertViewHas('posts', function ($posts): bool {
                $nextPageUrl = $posts->nextPageUrl();

                return $posts->total() === 11
                    && $posts->count() === 10
                    && $posts->perPage() === 10
                    && $posts->currentPage() === 1
                    && is_string($nextPageUrl)
                    && str_contains($nextPageUrl, 'search=Laravel')
                    && str_contains($nextPageUrl, 'page=2');
            })
            ->assertDontSee($unmatchedPost->title);

        $secondPageResponse = $this->get(route('posts.index', [
            'search' => 'Laravel',
            'page' => 2,
        ]));

        $secondPageResponse
            ->assertOk()
            ->assertViewHas('search', 'Laravel')
            ->assertViewHas('posts', function ($posts): bool {
                return $posts->total() === 11
                    && $posts->count() === 1
                    && $posts->currentPage() === 2;
            })
            ->assertDontSee($unmatchedPost->title);
    }

    public function test_yazi_guncellenirken_yeni_gorsel_kaydedilir_ve_eski_gorsel_silinir(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'status' => User::STATUS_ACTIVE,
        ]);

        $oldImagePath = 'posts/eski-kapak.jpg';

        Storage::disk('public')->put($oldImagePath, 'eski görsel');

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'featured_image' => $oldImagePath,
            'status' => Post::STATUS_DRAFT,
        ]);

        $newImage = UploadedFile::fake()
            ->image('yeni-kapak.jpg', 1200, 630)
            ->size(500);

        $response = $this
            ->actingAs($user)
            ->put(route('posts.update', $post), [
                'title' => 'Görseli Güncellenen Yazı',
                'excerpt' => 'Yeni öne çıkan görsel testi.',
                'content' => 'Bu içerik, yazı güncellenirken yeni görselin kaydedildiğini ve eski görselin silindiğini doğrulamak için hazırlanmıştır.',
                'featured_image' => $newImage,
                'status' => Post::STATUS_DRAFT,
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('posts.show', $post));

        $post->refresh();

        $this->assertNotNull($post->featured_image);
        $this->assertNotSame($oldImagePath, $post->featured_image);
        $this->assertStringStartsWith('posts/', $post->featured_image);

        Storage::disk('public')->assertExists($post->featured_image);
        Storage::disk('public')->assertMissing($oldImagePath);
    }
}
