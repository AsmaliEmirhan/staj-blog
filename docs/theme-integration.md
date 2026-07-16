# InkVoice Tema Entegrasyon Planı

## 1. Tema bilgileri

- Tema adı: InkVoice
- Tema sürümü: 1.0
- Paket tarihi: 21 Ocak 2025
- Tema geliştiricisi: DexignZone
- Tema türü: Blog ve dergi XHTML şablonu
- Temel CSS altyapısı: Bootstrap 5.3.3
- JavaScript altyapısı: jQuery 3.7.1
- Slider altyapısı: Swiper 11.1.4
- Tema kaynağı: Kurum tarafından sağlanan Envato Elements paketi

Tema paketi React teması değildir. Bootstrap, jQuery, HTML, CSS, SCSS ve JavaScript dosyalarından oluşur.

Projede ana arayüz Laravel Blade ile hazırlanacaktır. React yalnızca AI Bot yazı oluşturma ekranında gerçekten ihtiyaç duyulursa bağımsız bir bileşen olarak kullanılacaktır.

## 2. Tema paketinin içeriği

Ana tema arşivinde iki paket bulunmaktadır:

```text
InkVoice-v1.0-21_January_2025/
├── xhtml.zip
└── documentation.zip
```

- `xhtml.zip`: Tema sayfaları, CSS, JavaScript, font ve görselleri içerir.
- `documentation.zip`: Tema geliştiricisinin kurulum ve kullanım açıklamalarını içerir.

Tema arşivi doğrudan Laravel proje köküne açılmayacaktır. Yalnızca kullanacağımız sayfalar ve varlıklar seçilerek projeye aktarılacaktır.

## 3. Kullanılacak teknoloji yapısı

### Backend

- Laravel 13
- PHP 8.3
- MySQL 8.4
- Eloquent ORM
- Blade şablon motoru

### Frontend

- Laravel Blade
- Bootstrap 5.3.3
- InkVoice tema stilleri
- jQuery 3.7.1
- Swiper
- Vite
- Gerektiğinde sınırlı React kullanımı

## 4. Planlanan Laravel görünüm yapısı

```text
resources/views/
├── layouts/
│   ├── app.blade.php
│   ├── admin.blade.php
│   └── auth.blade.php
├── partials/
│   ├── header.blade.php
│   ├── footer.blade.php
│   ├── navigation.blade.php
│   ├── sidebar.blade.php
│   └── flash-message.blade.php
├── components/
│   ├── post-card.blade.php
│   ├── category-badge.blade.php
│   ├── tag-list.blade.php
│   ├── comment-item.blade.php
│   └── pagination.blade.php
├── home.blade.php
├── posts/
│   ├── index.blade.php
│   └── show.blade.php
├── categories/
│   └── show.blade.php
├── tags/
│   └── show.blade.php
├── profile/
│   ├── show.blade.php
│   └── edit.blade.php
├── ai/
│   └── writer.blade.php
└── admin/
    ├── dashboard.blade.php
    ├── posts/
    ├── categories/
    ├── comments/
    ├── reports/
    └── users/
```

Bu yapı hazırlanırken yalnızca gerçekten kullanılan Blade dosyaları oluşturulacaktır.

## 5. Tema varlıklarının planlanan konumu

Temadan seçilen ve tarayıcı tarafından doğrudan kullanılacak varlıklar şu yapıda tutulacaktır:

```text
public/theme/inkvoice/
├── css/
├── js/
├── images/
├── icons/
└── fonts/
```

Projeye özel frontend kaynakları ise Laravel’in standart klasörlerinde kalacaktır:

```text
resources/
├── css/
│   └── app.css
└── js/
    ├── app.js
    └── ai-writer.jsx
```

Tema arşivinin tamamı Git deposuna eklenmeyecektir. Kullanılmayan demo sayfaları, kaynak arşivleri ve gereksiz eklentiler projeye taşınmayacaktır.

## 6. HTML sayfalarının Blade yapısına dönüştürülmesi

Tema entegrasyonu sırasında aşağıdaki işlem sırası uygulanacaktır:

1. Ana tema sayfası incelenecek.
2. Tekrarlanan header, navigation, sidebar ve footer bölümleri ayrılacak.
3. Ortak HTML yapısı `layouts/app.blade.php` dosyasına taşınacak.
4. Tekrarlanan bölümler Blade partial ve component dosyalarına dönüştürülecek.
5. Statik metinler veritabanından gelen Laravel değişkenleriyle değiştirilecek.
6. Statik bağlantılar Laravel `route()` fonksiyonuyla üretilecek.
7. Tema varlık yolları `asset()` fonksiyonuyla oluşturulacak.
8. Formlara CSRF koruması eklenecek.
9. Mobil görünüm ve tarayıcı uyumluluğu test edilecek.

Örnek statik tema yolu:

```html
<img src="images/posts/example.jpg" alt="Örnek">
```

Laravel Blade karşılığı:

```blade
<img
    src="{{ asset('theme/inkvoice/images/posts/example.jpg') }}"
    alt="{{ $post->title }}"
>
```

## 7. Kullanılması planlanan tema ekranları

Tema içerisinden aşağıdaki ekranlara uygun tasarımlar seçilecektir:

- Ana sayfa
- Blog yazıları listesi
- Blog yazısı detay sayfası
- Kategori sayfası
- Etiket sayfası
- Arama sonuçları
- Kullanıcı profil sayfası
- Giriş ve kayıt sayfaları
- AI Bot yazı üretme sayfası
- Yönetim paneli için gerekli ortak bileşenler
- 404 ve 500 hata sayfaları

Tema paketindeki bütün demo sayfalarını kullanmak zorunda değiliz.

## 8. Güvenlik kuralları

Tema paketinde bulunan hazır PHP dosyaları doğrudan kullanılmayacaktır. Özellikle iletişim, abonelik ve form gönderme scriptleri Laravel’e taşınacaktır.

Bütün formlarda:

- Laravel CSRF koruması kullanılacak.
- Form verileri `FormRequest` sınıflarıyla doğrulanacak.
- Kullanıcı çıktıları Blade’in güvenli `{{ }}` söz dizimiyle gösterilecek.
- Yetkili işlemler middleware ve policy sınıflarıyla korunacak.
- Dosya yüklemelerinde tür, boyut ve uzantı kontrolü yapılacak.
- AI API anahtarı yalnızca `.env` dosyasında saklanacak.
- Kullanıcı tarafından oluşturulan HTML içerikleri güvenli biçimde filtrelenecek.
- Tema paketindeki harici bağlantılar ve scriptler kontrol edilecek.

## 9. JavaScript ve React kullanımı

Tema davranışları için Bootstrap, jQuery ve Swiper kullanılabilir. Ancak aynı görevi yapan birden fazla JavaScript eklentisi projeye eklenmeyecektir.

React kullanılırsa yalnızca AI yazı üretme ekranına bağlanacaktır:

```text
Blade sayfası
└── AI Writer mount alanı
    └── React bileşeni
        ├── Anahtar kelime girişi
        ├── Üretim seçenekleri
        ├── API isteği
        └── Üretilen içerik önizlemesi
```

React uygulamanın tamamını yönetmeyecek ve Laravel Blade yapısıyla çakışmayacaktır.

## 10. Performans kuralları

- Kullanılmayan CSS ve JavaScript dosyaları eklenmeyecek.
- Görseller uygun boyutlara getirilecek.
- Görsellerde lazy loading kullanılacak.
- Vite ile proje dosyaları derlenecek.
- Tema varlıklarının tekrar yüklenmesi önlenecek.
- Veritabanı sorgularında eager loading kullanılacak.
- Ana sayfada sayfalama uygulanacak.
- Üretim ortamında Laravel önbellekleri kullanılacak.

## 11. Entegrasyon aşamaları

### Aşama 1 — Hazırlık

- Tema arşivini inceleme
- Kullanılacak sayfaları belirleme
- Gerekli varlıkları ayırma
- Blade klasör yapısını hazırlama

### Aşama 2 — Ortak yerleşim

- Ana layout
- Header
- Navigation
- Footer
- Sidebar
- Ortak mesaj bileşenleri

### Aşama 3 — Blog ekranları

- Ana sayfa
- Yazı listesi
- Yazı detayı
- Kategori ve etiket sayfaları
- Arama sonuçları

### Aşama 4 — Kullanıcı ekranları

- Kayıt
- Giriş
- Profil
- Kaydedilen yazılar
- Bildirimler

### Aşama 5 — AI Bot ekranı

- Anahtar kelime girişi
- Günlük kullanım limiti
- İçerik üretimi
- Önizleme
- Yazıyı taslak olarak kaydetme

### Aşama 6 — Yönetim ekranları

- Dashboard
- Yazı yönetimi
- Kategori ve etiket yönetimi
- Yorum moderasyonu
- Şikâyet yönetimi
- Kullanıcı yönetimi

### Aşama 7 — Son kontroller

- Responsive tasarım
- Güvenlik kontrolleri
- Erişilebilirlik
- Performans
- Form doğrulama mesajları
- Hata sayfaları
- Tarayıcı testleri

## 12. Entegrasyonun başlayacağı zaman

Önce backend veri modeli, kullanıcı kimlik doğrulama sistemi ve temel controller yapısı tamamlanacaktır. Ardından InkVoice tema entegrasyonuna geçilecektir.

Tema entegrasyonu da diğer görevler gibi her seferinde yalnızca tek adımla yapılacaktır.