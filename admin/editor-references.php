<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';
require_login();

$refs_file = DATA_DIR . '/references.json';
$refs = file_exists($refs_file) ? json_decode(file_get_contents($refs_file), true) : [];
if (!is_array($refs)) $refs = [];

usort($refs, function($a, $b) { return ($a['position'] ?? 999) - ($b['position'] ?? 999); });

$total_count = count($refs);

// Toast
$toast = '';
if (isset($_GET['uploaded']))  $toast = '<div class="alert alert-success">Görsel yüklendi ve işlendi.</div>';
if (isset($_GET['deleted']))   $toast = '<div class="alert alert-warning">Görsel silindi.</div>';
if (isset($_GET['reordered'])) $toast = '<div class="alert alert-info">Sıralama güncellendi.</div>';
if (isset($_GET['error'])) {
    $msgs = [
        'upload_failed' => 'Yükleme başarısız.', 'too_large' => 'Dosya 5MB\'dan büyük.',
        'invalid_type' => 'Sadece JPG/PNG kabul edilir.', 'invalid_mime' => 'Geçersiz dosya türü.',
        'ai_detected' => 'AI üretimi görsel tespit edildi!', 'corrupt' => 'Bozuk görsel.',
        'system_error' => 'Sistem hatası.'
    ];
    $msg = $msgs[$_GET['error']] ?? 'Bilinmeyen hata.';
    $toast = '<div class="alert alert-danger">' . htmlspecialchars($msg) . '</div>';
}

// Build cards
$cards = '';
$csrf = csrf_token();
foreach ($refs as $idx => $r) {
    $src = htmlspecialchars($r['src'] ?? '');
    $pos = $idx + 1;
    $alt_en = htmlspecialchars(substr($r['alt_en'] ?? '', 0, 70));
    $sector = htmlspecialchars($r['sector'] ?? '');
    $fair = htmlspecialchars($r['fair'] ?? '');
    $city = htmlspecialchars($r['city'] ?? '');
    $year = htmlspecialchars($r['year'] ?? '');

    $cards .= <<<HTML
    <div class="ref-card col-12">
      <div class="d-flex align-items-start gap-3 p-3 bg-white rounded-3 shadow-sm">
        <form method="post" action="actions/move-reference.php" class="d-flex gap-1 align-items-start flex-shrink-0" style="width:80px">
          <input type="hidden" name="csrf_token" value="$csrf">
          <input type="hidden" name="id" value="$idx">
          <input type="number" name="new_pos" value="$pos" min="1" max="$total_count" class="form-control form-control-sm text-center" style="width:50px" title="Pozisyon">
          <button class="btn btn-sm btn-outline-secondary" title="Taşı">→</button>
        </form>
        <img src="$src" alt="$alt_en" style="width:120px;height:90px;object-fit:contain;background:#f8f9fa;border-radius:6px">
        <div class="flex-grow-1" style="min-width:0">
          <div class="fw-semibold small mb-1">$alt_en</div>
          <div class="text-muted" style="font-size:.75rem">$city $fair $year · $sector</div>
        </div>
        <div class="d-flex gap-1 flex-shrink-0">
          <form method="post" action="actions/reorder-references.php" class="d-flex gap-1">
            <input type="hidden" name="csrf_token" value="$csrf">
            <input type="hidden" name="id" value="$idx">
            <button name="direction" value="up" class="btn btn-sm btn-outline-secondary" title="Yukarı">↑</button>
            <button name="direction" value="down" class="btn btn-sm btn-outline-secondary" title="Aşağı">↓</button>
          </form>
          <form method="post" action="actions/delete-reference.php" onsubmit="return confirm('Bu görseli sil?')">
            <input type="hidden" name="csrf_token" value="$csrf">
            <input type="hidden" name="id" value="$idx">
            <button class="btn btn-sm btn-outline-danger">🗑️</button>
          </form>
        </div>
      </div>
    </div>
HTML;
}

if (empty($cards)) {
    $cards = '<div class="col-12 text-center py-5 text-muted">Henüz referans görseli yok. "Yeni Görsel Ekle" ile başlayın.</div>';
}

$content = <<<HEREDOC
<div class="d-flex justify-content-between align-items-center mb-4">
  <h2 class="m-0">Referanslar</h2>
  <span class="badge bg-secondary fs-6">{$total_count} görsel</span>
</div>

$toast

<div class="mb-4">
  <button class="btn btn-accent" type="button" data-bs-toggle="collapse" data-bs-target="#uploadForm">+ Yeni Görsel Ekle</button>
  <div class="collapse mt-3" id="uploadForm">
    <div class="card card-body bg-light p-4">
      <form method="post" action="actions/upload-reference.php" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="$csrf">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Görsel (JPG/PNG, max 5MB)</label>
            <input type="file" name="image" class="form-control" accept="image/jpeg,image/png" required>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">SEO Dosya Adı (İngilizce)</label>
            <input type="text" name="seo_name" class="form-control" placeholder="exhibition-stand-frankfurt-01">
          </div>
          <div class="col-12"><hr><strong class="text-muted">Alt Metinler (SEO — sayfada görünmez, her dilde farklı)</strong></div>
          <div class="col-md-6"><input name="alt_en" class="form-control form-control-sm" placeholder="EN alt text"></div>
          <div class="col-md-6"><input name="alt_tr" class="form-control form-control-sm" placeholder="TR alt metin"></div>
          <div class="col-md-6"><input name="alt_de" class="form-control form-control-sm" placeholder="DE alt text"></div>
          <div class="col-md-6"><input name="alt_fr" class="form-control form-control-sm" placeholder="FR alt text"></div>
          <div class="col-md-6"><input name="alt_es" class="form-control form-control-sm" placeholder="ES alt text"></div>
          <div class="col-md-6"><input name="alt_ru" class="form-control form-control-sm" placeholder="RU alt text"></div>
          <div class="col-md-6"><input name="alt_zh" class="form-control form-control-sm" placeholder="ZH 图片描述"></div>
          <div class="col-md-6"><input name="alt_ar" class="form-control form-control-sm" placeholder="AR نص بديل" dir="rtl"></div>
          <div class="col-12"><hr><strong class="text-muted">Metadata (opsiyonel)</strong></div>
          <div class="col-md-3"><input name="sector" class="form-control form-control-sm" placeholder="Sektör (otomotiv)"></div>
          <div class="col-md-3"><input name="fair" class="form-control form-control-sm" placeholder="Fuar adı"></div>
          <div class="col-md-2"><input name="city" class="form-control form-control-sm" placeholder="Şehir"></div>
          <div class="col-md-2"><input name="country" class="form-control form-control-sm" placeholder="Ülke"></div>
          <div class="col-md-2"><input name="year" class="form-control form-control-sm" placeholder="Yıl (2024)"></div>
          <div class="col-12">
            <button type="submit" class="btn btn-accent">Yükle ve İşle</button>
            <small class="text-muted ms-3">JPG→WEBP / 1200px / watermark / EXIF temizleme / XMP / steganografi</small>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="row g-3">
  $cards
</div>

<style>
  .ref-card { transition: transform .15s; }
  .ref-card:hover { transform: translateY(-1px); }
</style>
HEREDOC;

require_once __DIR__ . '/admin-layout.php';
render_admin_layout('Referanslar', $content, 'references');
