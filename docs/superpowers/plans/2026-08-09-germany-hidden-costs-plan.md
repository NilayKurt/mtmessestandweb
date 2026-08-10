# Almanya Fuar Standı Operasyonel Maliyet Rehberi — Uygulama Planı

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Almanya fuar standı hidden costs blog yazısını yaz, siteye ekle, LinkedIn özetini hazırla

**Architecture:** Tek HTML sayfası (blog post template), site /blog/ dizinine eklenecek. İçerik Markdown'dan HTML'e dönüştürülecek. SEO meta etiketleri ve schema eklenecek.

**Tech Stack:** HTML + Bootstrap 5, JSON-LD schema

## Global Constraints

- B2B profesyonel ton, ajite etmeyen, veri odaklı
- "Sizi bekleyen tehlike" değil "operasyonel gerçekler"
- MT deneyimi doğal akışta, reklam gibi değil
- Rakamlar spesifik (kaynaklı: resmi kılavuz sayfa no)
- Türkçe
- Sayfada MT Messe Stand'e özgü saha anekdotları her bölümde
- Kopyalanmaya karşı MT referansları içeriğe gömülü
- Canonical URL: https://mtmessestand.com/blog/almanya-fuar-standi-hidden-costs

---

### Task 1: Blog sayfası HTML iskeleti

**Files:**
- Create: `blog/almanya-hidden-costs.html`

**Interfaces:**
- Produces: HTML iskeleti, header/footer include edilmiş, SEO meta hazır

- [ ] **Step 1: Blog HTML şablonunu oluştur**

```html
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Almanya Fuar Standı Operasyonel Maliyet Rehberi | MT Messe Stand</title>
  <meta name="description" content="Almanya'da fuar standı kurulumunda resmi kılavuzlara yansımayan operasyonel maliyetler. Messe Frankfurt, Hannover, Düsseldorf, Stuttgart, Münih, Berlin teknik kılavuzlarından derlenmiştir.">
  <link rel="canonical" href="https://mtmessestand.com/blog/almanya-hidden-costs/">
  <!-- OG + Twitter + JSON-LD + Bootstrap CSS -->
</head>
<body>
  <!-- Header (site genel navbar) -->
  <!-- Article content -->
  <!-- Footer -->
</body>
</html>
```

- [ ] **Step 2: Header ve footer'ı ana sayfadan kopyala, body class="blog" ekle**

- [ ] **Step 3: Dosyayı kaydet ve canlıda kontrol et**

Run: `python3 -m http.server 8080 --directory ~/projects/mtmesse`
Check: http://localhost:8080/blog/almanya-hidden-costs.html

- [ ] **Step 4: Commit**

```bash
git add blog/almanya-hidden-costs.html
git commit -m "blog: add germany hidden costs page skeleton"
```

---

### Task 2: Giriş ve Bölüm

**Files:**
- Modify: `blog/almanya-hidden-costs.html`

**Interfaces:**
- Consumes: HTML iskeleti (Task 1)
- Produces: Giriş metni ve içindekiler

- [ ] **Step 1: Giriş metnini yaz (3-4 paragraf)**

Almanya'daki fuar pazarı büyüklüğü, ortalama stand maliyeti, "ancak bu rakama dahil olmayan kalemler", bir MT anekdotu.

- [ ] **Step 2: İçindekiler (hızlı linkler)**

6 şehir + genel kurallar için anchor linkler

- [ ] **Step 3: Kaydet ve ön izleme yap**

- [ ] **Step 4: Commit**

```bash
git add blog/almanya-hidden-costs.html
git commit -m "blog: add germany intro and table of contents"
```

---

### Task 3: Bölüm

**Files:**
- Modify: `blog/almanya-hidden-costs.html`

**Interfaces:**
- Consumes: Giriş (Task 2)
- Produces: ~18 teknik düzenleme maddesi

- [ ] **Step 1: Genel kurallar alt bölümü**

Tüm Almanya'da geçerli 8-10 kural: B1 yangın, strafor yasağı, yapay çiçek yasağı, 3/1 açık köşe, rampa, kapalı tavan sprinkler, çöp cezası, 70 dB ses limiti, KDV iadesi, TÜV kontrolü.

Her kural formatı:
```
<h3>[Kural]</h3>
<p>[Resmi kılavuz referansıyla açıklama]</p>
<blockquote>MT Messe Stand olarak [şehir]'deki [fuar]'da [deneyim]</blockquote>
```

- [ ] **Step 2: Şehir bazlı özel kurallar**

6 şehir için her birine özgü farklar:
- Frankfurt: Catering tekeli, 2.5m TÜV eşiği
- Hannover: Hall 8/9/26 askı yok, hot work permit
- Düsseldorf: Askı max 50 kg, Brandwache, 4.00m araç limiti
- Stuttgart: Brandwache
- Münih: 18:00 deadline
- Berlin: GewAbfV atık, hot work permit

- [ ] **Step 3: Kaydet ve ön izleme yap**

- [ ] **Step 4: Commit**

```bash
git add blog/almanya-hidden-costs.html
git commit -m "blog: add germany section A — technical regulations"
```

---

### Task 4: Bölüm

**Files:**
- Modify: `blog/almanya-hidden-costs.html`

**Interfaces:**
- Consumes: Bölüm A (Task 3)
- Produces: Maliyet analizi bölümü

- [ ] **Step 1: Maliyet kalemlerini yaz**

Aynı düzenlemeler, bu sefer para olarak karşılığı. Her kalem için:
- Düzenleme ne
- İhlal/uygulama maliyeti ne
- MT nasıl yönetiyor

- [ ] **Step 2: Örnek vaka kutusu**

"Frankfurt'ta 100m² bir stand için tipik ek maliyet kalemleri" — tablo formatında

- [ ] **Step 3: Kaydet ve ön izleme yap**

- [ ] **Step 4: Commit**

```bash
git add blog/almanya-hidden-costs.html
git commit -m "blog: add germany section B — operational cost analysis"
```

---

### Task 5: Kapanış, SEO, LinkedIn

**Files:**
- Modify: `blog/almanya-hidden-costs.html`
- Create: `linkedin/germany-hidden-costs.md`

**Interfaces:**
- Consumes: Tam içerik (Task 1-4)
- Produces: Kapanış + CTA, JSON-LD schema, LinkedIn özeti

- [ ] **Step 1: Kapanış ve CTA yaz**

"MT Messe Stand olarak Almanya'daki 6 büyük fuar şehrinde..." + teklif CTA

- [ ] **Step 2: JSON-LD Article + FAQPage schema ekle**

5 FAQ (Messe Frankfurt catering, B1 belgesi, TÜV onayı, strafor yasağı, KDV iadesi)

- [ ] **Step 3: Breadcrumb schema ekle**

Home > Blog > Almanya Hidden Costs

- [ ] **Step 4: LinkedIn özeti hazırla**

En şaşırtıcı 3 kalem + site linki. Ayrı dosyaya kaydet.

- [ ] **Step 5: Son ön izleme ve deploy**

```bash
cd ~/projects/mtmesse && netlify deploy --dir=.
netlify api restoreSiteDeploy ...
```

- [ ] **Step 6: Commit**

```bash
git add blog/almanya-hidden-costs.html linkedin/germany-hidden-costs.md
git commit -m "blog: finalize germany hidden costs — closing, SEO, LinkedIn"
```

---

**Toplam:** 5 task, ~20 step. Her task 10-20 dakika.
