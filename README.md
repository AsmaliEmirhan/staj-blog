# Staj Blog

Staj Blog; kullanıcıların blog yazıları oluşturabildiği, düzenleyebildiği ve yayımlayabildiği yapay zekâ destekli bir blog platformudur.

Proje, bir aylık yazılım stajı kapsamında güvenli yazılım geliştirme, nesne yönelimli programlama ve temiz kod ilkeleri dikkate alınarak geliştirilmektedir.

## Projenin Durumu

Proje aktif geliştirme aşamasındadır.

Geliştirme ortamı, Git repository'si ve temel proje yapılandırmaları hazırlanmıştır. PHP uygulama mimarisi, veritabanı, blog sistemi ve kullanıcı işlemleri sırayla geliştirilecektir.

## Projenin Temel Özellikleri

- Kullanıcı kayıt, giriş ve çıkış sistemi
- Güvenli oturum yönetimi
- Blog yazısı oluşturma, düzenleme ve silme
- Taslak ve yayımlanmış yazı sistemi
- Kategori ve etiket yönetimi
- Blog yazılarında arama ve filtreleme
- Sayfalama
- Güvenli kapak görseli yükleme
- Yapay zekâ ile blog taslağı oluşturma
- Kullanıcı paneli
- Temel yönetici yetkilendirmesi
- Responsive kullanıcı arayüzü

## Kullanılan Mimari

Proje, klasik PHP uygulaması ile izole React bileşenlerini bir arada kullanan bütünleşik bir mimariye sahiptir.

### Genel kullanıcı arayüzü

Sitenin genel tasarımında kurum tarafından sağlanan InkVoice XHTML teması kullanılacaktır.

Ana sayfa, blog detayları, kategoriler, kullanıcı formları ve ortak sayfa düzenleri aşağıdaki teknolojilerle hazırlanacaktır:

- HTML5
- PHP view dosyaları
- Bootstrap 5
- jQuery
- InkVoice tema CSS ve JavaScript dosyaları

### Back-end

Back-end tarafı PHP ile nesne yönelimli ve katmanlı bir yapıda geliştirilecektir.

Temel back-end katmanları:

- Controller
- Service
- Repository
- Model
- Middleware
- Validation
- REST API

### React kullanımı

React bütün siteyi yönetmeyecektir.

React, kullanıcı etkileşiminin ve form durumunun daha yoğun olduğu AI Writer bölümünde izole bir bileşen olarak kullanılacaktır. React yalnızca kendisine ayrılan HTML alanını yönetecek, jQuery ise bu alana müdahale etmeyecektir.

Bu yaklaşım sayesinde InkVoice temasının mevcut tasarımı korunurken, yapay zekâ özelliği modern ve yönetilebilir bir kullanıcı deneyimine sahip olacaktır.

## Yapay Zekâ Özelliği

Kullanıcı aşağıdaki bilgileri girerek yapay zekâ destekli blog taslağı oluşturabilecektir:

- Anahtar kelimeler
- Yazı tonu
- Hedef içerik uzunluğu
- Kategori tercihi

Yapay zekâ aşağıdaki içerikleri önerecektir:

- Blog başlığı
- Kısa özet
- Blog içeriği
- Etiketler

Yapay zekâ tarafından oluşturulan içerik doğrudan yayımlanmayacaktır. Kullanıcı içeriği kontrol edip düzenledikten sonra taslak olarak kaydedebilecek veya yayımlayabilecektir.

## Kullanılan Teknolojiler

### Sunucu tarafı

- PHP 8.3
- MySQL 8.4
- PDO
- Composer
- REST API
- PHP Session

### Kullanıcı arayüzü

- InkVoice XHTML
- Bootstrap 5.3
- jQuery 3.7
- SCSS/CSS
- Swiper
- React
- Vite

### Geliştirme araçları

- Laragon
- Apache
- Visual Studio Code
- Git
- GitHub
- Postman veya Bruno
- PHPUnit

## Güvenlik Yaklaşımı

Projede aşağıdaki güvenlik önlemleri uygulanacaktır:

- Parolaların `password_hash()` ile saklanması
- Parola kontrolünde `password_verify()` kullanılması
- PDO prepared statements kullanılması
- Kullanıcı girdilerinin doğrulanması
- Yetkilendirme ve yazı sahipliği kontrolleri
- CSRF koruması
- Güvenli session ayarları
- Giriş ve AI isteklerinde rate limiting
- Güvenli dosya yükleme kontrolleri
- Ortam değişkenleriyle gizli bilgi yönetimi
- Kullanıcıya hassas sunucu hatalarının gösterilmemesi

Gerçek parolalar, veritabanı bilgileri ve AI API anahtarları GitHub repository'sine gönderilmeyecektir.

## Proje Klasör Yapısı

```text
staj-blog/
├── app/
├── bootstrap/
├── database/
├── docs/
├── public/
├── resources/
├── routes/
├── storage/
├── tests/
├── .editorconfig
├── .gitignore
└── README.md