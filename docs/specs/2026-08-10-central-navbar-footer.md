# Merkezi Navbar & Footer Sistemi

**Tarih:** 2026-08-10
**Proje:** MT Messe Stand (mtmessestand.com)
**Amaç:** Navbar ve footer'ın tek kaynaktan render edilmesi, kod tekrarının bitirilmesi

---

## Mimari

```
templates/
  navbar.php        ← render_navbar($lang, $page_type, $slug='') → HTML
  footer.php        ← render_footer($lang, $depth, $page_type)   → HTML
  page-template.php ← about/contact sayfaları
  blog-template.php ← blog yazıları
```

Tüm çeviriler (7 dil) fonksiyonların içinde tanımlı. Ayrı çeviri dosyası yok — basitlik için inline.

---

## render_navbar($lang, $page_type, $slug='')

**Parametreler:**
- `$lang`: en, tr, de, fr, es, ar, zh
- `$page_type`: home, about, blog_list, blog_post, contact
- `$slug`: sadece blog_post için, dil değiştirince dosya adı eşleşmesi için

**Ürettiği HTML:**
- `<header id="header" class="fixed-top">` + Bootstrap navbar
- Logo 140x27, brand-text "MT Messe Stand"
- Menü: Home/Anasayfa/..., About/Hakkımızda/..., Blog, Contact/İletişim
- Aktif sayfa `active` class'ı
- Dil dropdown'ı (mevcut navbar.js'teki allLangs yapısı)
- Dil değiştirince blogMap ile dosya adı eşleşmesi

---

## render_footer($lang, $depth, $page_type)

**Parametreler:**
- `$lang`: en, tr, de, fr, es, ar, zh
- `$depth`: 0 (ana sayfa), 1 (about/contact), 2 (blog)
- `$page_type`: home, about, contact, blog, blog_index

**Ürettiği HTML:**
- Logo 186x36 + tagline + AUMA/DGUV + social (LinkedIn, Instagram, YouTube)
- Services/Hizmetlerimiz (6 madde, dile göre)
- Company/Kurumsal (3 madde, dile göre)
- Language/Dil badge'leri (7 dil, aktif olan bg-accent)
- Copyright (dile göre)

**Link path'leri depth'e göre:**
- depth 0: `assets/`, `#services`, `about.html`
- depth 1: `../assets/`, `index.html#services`, `about.html`
- depth 2: `../../assets/`, `../../index.html#services`, `../../about.html`

---

## Sayfaların kullanımı

### Statik sayfalar → build.php injection
Ana sayfalar ve blog index `.html` olarak kalır. Navbar ve footer `<!-- NAVBAR -->` / `<!-- FOOTER -->` placeholder'ları ile build.php tarafından enjekte edilir. Mevcut `<!-- BLOG_CARDS -->` pattern'inin aynısı.

### Template sayfalar → direkt fonksiyon çağrısı
About/contact ve blog yazıları template'lerde doğrudan `render_navbar()` / `render_footer()` çağrılır.

| Sayfa | Navbar | Footer | Yöntem |
|---|---|---|---|
| Ana sayfa | `render_navbar($lang, 'home')` | `render_footer($lang, 0, 'home')` | build.php injection |
| About | `render_navbar($lang, 'about')` | `render_footer($lang, 1, 'about')` | page-template.php |
| Contact | `render_navbar($lang, 'contact')` | `render_footer($lang, 1, 'contact')` | page-template.php |
| Blog index | `render_navbar($lang, 'blog_list')` | `render_footer($lang, 2, 'blog_index')` | build.php injection |
| Blog yazısı | `render_navbar($lang, 'blog_post', $slug)` | `render_footer($lang, 2, 'blog')` | blog-template.php |

### build.php genişletme
Mevcut `<!-- BLOG_CARDS -->` injection'ına ek olarak:
```php
$html = str_replace('<!-- NAVBAR -->', render_navbar($lang, $page_type), $html);
$html = str_replace('<!-- FOOTER -->',  render_footer($lang, $depth, $page_type), $html);
```

---

## Kaldırılacak dosyalar

- `assets/js/navbar.js` — navbar PHP'den render
- `templates/footer-data.php` — footer.php içine merge

## Değişecek dosyalar

- `templates/navbar.php` — YENİ
- `templates/footer.php` — YENİ
- `templates/page-template.php` — navbar + footer fonksiyon çağrısı
- `templates/blog-template.php` — navbar + footer fonksiyon çağrısı
- `build.php` — navbar + footer render_footer
- Ana sayfa: manuel footer/navbar HTML kaldır, fonksiyonla değiştir

## Doğrulama

- 7 dilde navbar menü etiketleri doğru
- 7 dilde footer metinleri doğru
- Depth'e göre asset path'leri doğru
- Dil badge link'leri doğru
- Audit: 0 errors
