# SEO/GEO Roadmap — Remaining Items Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Complete remaining 3 roadmap items (#3, #5, #8) + RAG chunk fix (#7) + TR language page + cPanel guide update.

**Architecture:** All changes are homepage edits to `index.html`, supporting files (`llms.txt`, `cpanel-cron-rehberi.txt`), and one new page (`tr/index.html`). No new dependencies.

**Tech Stack:** HTML + Bootstrap 5, JSON-LD schema, static files.

## Global Constraints

- B2B profesyonel ton, ajite etmeyen, veri odaklı
- "Turkey" → "Turkiye" (tüm metinde)
- Octanorm geçmez
- Çok dilli hreflang: en/de/fr/ar/zh (tr eklenmeyecek — TR sayfası ayrı)
- Tüm değişiklikler build.php ile uyumlu olmalı

---

### Task 1: Keyword Diversity Fix (#3)

**Files:**
- Modify: `index.html` (service cards, craft section, why section)

**Interfaces:**
- Consumes: Current homepage text
- Produces: Same meaning, more varied vocabulary

**Goal:** "exhibition" kelimesini düşür, synonyms ekle. Hedef: %3.8 → %2 altı.

- [ ] **Step 1: Services bölümünde "exhibition" yerine geçen kelimeler**

Current: "exhibition stand" her kartta tekrarlanıyor.
Fix: "trade show booth", "fair stand", "display stand", "event space" ile rotasyon.

Custom Wooden Stands paragrafı:
```
Full custom builds — curved walls, integrated LED screens, suspended ceilings, branded floor finishes. We work from your 3D design files or create everything from scratch. Typical project: 18–800m², 3–6 weeks from approval to opening day. Every stand is manufactured in our own workshops, built to specification, and delivered on time.
```
→ "exhibition stand" 3 kez geçiyor → hepsi kaldırılacak.

- [ ] **Step 2: Craft & Quality bölümünde "exhibition" çeşitlendir**

Current: "Every exhibition stand is designed..."
Fix: "Every project — from a 9m² booth to an 800m² country pavilion — is designed..."

- [ ] **Step 3: Why MT bölümü**

"Built Near Your Fair" → "exhibition venue" yerine "fair venue" veya "event location"
"300+ exhibition stands" → "300+ projects" veya "300+ builds"

- [ ] **Step 4: Verify with grep**

```bash
grep -oi 'exhibition' index.html | wc -l
# Target: ~15 occurrences (was ~25)
```

- [ ] **Step 5: Restore BLOG_CARDS placeholder and commit**

---

### Task 2: KG Links — SameAs (#5)

**Files:**
- Modify: `index.html` (Organization JSON-LD sameAs array)

**Interfaces:**
- Consumes: Wikidata/Crunchbase URLs from user
- Produces: 3+ sameAs entries in Organization schema

**⚠️ BLOCKED:** User must provide Wikidata ID or Crunchbase URL. Without them:
- LinkedIn: already present
- Wikidata: e.g., `https://www.wikidata.org/wiki/QXXXXXXX`
- Crunchbase: e.g., `https://www.crunchbase.com/organization/mt-messe-stand`

- [ ] **Step 1: Ask user for Wikidata/Crunchbase URLs**

- [ ] **Step 2: Add to sameAs array**

Current:
```json
"sameAs": ["https://www.linkedin.com/company/mt-messe-stand"]
```

Target:
```json
"sameAs": [
  "https://www.linkedin.com/company/mt-messe-stand",
  "https://www.wikidata.org/wiki/QXXXXXXX",
  "https://www.crunchbase.com/organization/mt-messe-stand"
]
```

---

### Task 3: External Link Authority (#8)

**Files:**
- Modify: `index.html` (body content + footer)

**Interfaces:**
- Consumes: Current text
- Produces: 7+ new authority links embedded in content

- [ ] **Step 1: Where We Work bölümüne fuar alanı linkleri**

İstanbul: TUYAP, IFM, CNR Expo linkleri
Dubai: DWTC, ADNEC linkleri
Moscow: Crocus Expo, Expocentre linkleri

```html
<p>Nearly every major trade fair venue in Istanbul — <a href="https://www.tuyap.com.tr/en" target="_blank" rel="noopener">TÜYAP</a>, <a href="https://www.ifmistanbul.com/en" target="_blank" rel="noopener">IFM</a>, <a href="https://cnrexpo.com/en" target="_blank" rel="noopener">CNR Expo</a> — is within 45 minutes of our workshop.</p>
```

- [ ] **Step 2: Services bölümüne endüstri referansları**

Maxima Modular kartı sonuna ekle:
```html
<p class="small text-muted mt-2">Maxima is a registered modular stand system. <a href="https://www.maximasystem.com" target="_blank" rel="noopener">Learn more →</a></p>
```

- [ ] **Step 3: Craft bölümüne AUMA referansı**

```html
<p>According to <a href="https://www.auma.de/en" target="_blank" rel="noopener">AUMA</a> (Association of the German Trade Fair Industry), Germany alone hosts 180+ international trade fairs annually.</p>
```

- [ ] **Step 4: Footer'a fuar organizatör linkleri**

Services altına:
```html
<li><a href="https://www.ufi.org" target="_blank" rel="noopener">UFI Member</a></li>
```

- [ ] **Step 5: Verify**

```bash
grep -c 'target="_blank" rel="noopener"' index.html
# Target: 10+ (was 3)
```

---

### Task 4: RAG Chunk Readiness (#7)

**Files:**
- Modify: `index.html` (all section paragraphs)

**Interfaces:**
- Consumes: Current content
- Produces: Sections expanded to 100-150 word blocks

**Goal:** Each section's intro paragraph should be 100-150 words for optimal RAG chunking.

- [ ] **Step 1: Expand Craft & Quality intro**

Current: ~60 words → Target: 120 words
Add: mention of materials (FSC wood, aluminum, LED), quality control process

- [ ] **Step 2: Expand Where We Work intro**

Current: ~90 words → already close. Add one sentence about logistics capabilities.

- [ ] **Step 3: Expand Why MT intro**

Current: ~70 words → Target: 120 words. Add trust signal: repeat clients stat.

- [ ] **Step 4: Add short descriptive paragraphs under each service card h3**

Before each card's main `<p>`, add one opening line:
```html
<h3>Custom Wooden Stands</h3>
<p class="small text-accent">Premium bespoke builds for brands that want full creative control.</p>
<p>Full custom builds — curved walls...</p>
```

- [ ] **Step 5: Verify word count**

```bash
# Strip HTML, count words
python3 -c "import re; t=re.sub('<[^>]+>',' ',open('index.html').read()); print(len(t.split()))"
# Target: 1800+ (was 1154)
```

---

### Task 5: TR Language Page

**Files:**
- Create: `tr/index.html`
- Modify: `index.html` (add hreflang tr)

**Interfaces:**
- Consumes: EN homepage as template
- Produces: Full TR homepage with Turkish content

- [ ] **Step 1: Copy EN index.html as template**

```bash
cp index.html tr/index.html
```

- [ ] **Step 2: Translate meta/head**

```html
<title>MT Messe Stand | Fuar Standı Tasarım ve Üretim</title>
<meta name="description" content="MT Messe Stand — İstanbul, Dubai ve Moskova'da fuar standı üreticisi. Ahşap, Maxima modüler standlar. 300+ proje, 15+ ülke.">
<link rel="canonical" href="https://mtmessestand.com/tr/">
```

- [ ] **Step 3: Translate all content sections** using spec document (already in Turkish context)

Hero → "Fuar Standı Üreticisi — Tasarım ve Üretim"
Craft → "Nerede Sergilerseniz Sergileyin — Biz Zaten Oradayız."
Services → 6 kart Türkçe
Where We Work → "Çalıştığımız Yerler"
How We Work → "Nasıl Çalışırız"
Why MT → "Neden MT MesseStand?"
Contact → "Hadi Konuşalım"

- [ ] **Step 4: Update paths** (`../assets/` → `../assets/`, nav links adjust)

- [ ] **Step 5: Add zh to hreflang in both EN and TR pages**

- [ ] **Step 6: Add BLOG_CARDS placeholder in tr/index.html**

---

### Task 6: cPanel Rehberi Güncelle

**Files:**
- Modify: `docs/cpanel-cron-rehberi.txt`

- [ ] **Step 1: build.py → build.php**

Tüm `build.py` referanslarını `build.php` ile değiştir.

- [ ] **Step 2: Python → PHP komut**

Cron Job komutunu güncelle:
```
/usr/bin/php /home/KULLANICI/public_html/build.php
```

- [ ] **Step 3: Blog kartı referansını güncelle**

blog-cards.js yok artık, sadece build.php var.

---

### Task 7: Final SEO Re-Audit

- [ ] **Step 1: SEO metrikleri kontrol et**

```bash
# Title (50-60c)
grep -o '<title>.*</title>' index.html | wc -c

# Word count (1800+)
python3 -c "import re; t=re.sub('<[^>]+>',' ',open('index.html').read()); print(len(t.split()))"

# External links (10+)
grep -c 'target="_blank"' index.html

# Exhibition density (<2%)
WORDS=$(python3 -c "import re; t=re.sub('<[^>]+>',' ',open('index.html').read()); print(len(t.split()))")
EXPO=$(grep -oi 'exhibition' index.html | wc -l)
echo "scale=1; $EXPO * 100 / $WORDS" | bc
```

- [ ] **Step 2: GEO re-audit**

```bash
uvx --from geo-optimizer-skill geo audit --url "https://mellow-souffle-1db250.netlify.app/"
# Target: 65 → 75+
```

- [ ] **Step 3: Deploy**

---

**Toplam:** 7 task. Task 1 hemen yapılabilir, Task 2 kullanıcı girdisi bekler, Task 3-7 bağımsız paralel yapılabilir.
