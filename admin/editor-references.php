<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';
require_login();

$refs_file = DATA_DIR . '/references.json';
$refs = file_exists($refs_file) ? json_decode(file_get_contents($refs_file), true) : [];
if (!is_array($refs)) $refs = [];

usort($refs, function($a, $b) { return ($a['position'] ?? 999) - ($b['position'] ?? 999); });

$total_count = count($refs);

$toast = '';
if (isset($_GET['uploaded']))  $toast = '<div class="alert alert-success">Görsel yüklendi ve işlendi.</div>';
if (isset($_GET['deleted']))   $toast = '<div class="alert alert-warning">Görsel silindi.</div>';
if (isset($_GET['reordered'])) $toast = '<div class="alert alert-info">Sıralama güncellendi.</div>';
if (isset($_GET['saved']))     $toast = '<div class="alert alert-success">Görsel güncellendi.</div>';
if (isset($_GET['error'])) {
    $msgs = ['upload_failed'=>'Yükleme başarısız.','too_large'=>'Dosya 5MB\'dan büyük.','invalid_type'=>'Sadece JPG/PNG.','invalid_mime'=>'Geçersiz dosya.','ai_detected'=>'AI üretimi görsel!','corrupt'=>'Bozuk görsel.','system_error'=>'Sistem hatası.'];
    $msg = $msgs[$_GET['error']] ?? 'Hata.';
    $toast = '<div class="alert alert-danger">' . htmlspecialchars($msg) . '</div>';
}

$cards = '';
$csrf = csrf_token();
foreach ($refs as $idx => $r) {
    $src = htmlspecialchars($r['src'] ?? '');
    $pos = $idx + 1;
    $alt_en = htmlspecialchars(mb_substr($r['alt_en'] ?? '', 0, 70));
    $sector = htmlspecialchars($r['sector'] ?? '');
    $fair = htmlspecialchars($r['fair'] ?? '');
    $city = htmlspecialchars($r['city'] ?? '');
    $year = htmlspecialchars($r['year'] ?? '');
    $fn = htmlspecialchars($r['filename'] ?? '');

    $cards .= <<<HTML
    <div class="ref-card col-12" id="ref-{$idx}">
      <div class="d-flex align-items-start gap-3 p-3 bg-white rounded-3 shadow-sm">
        <form method="post" action="actions/move-reference.php" class="d-flex gap-1 align-items-start flex-shrink-0" style="width:80px">
          <input type="hidden" name="csrf_token" value="$csrf">
          <input type="hidden" name="id" value="$idx">
          <input type="number" name="new_pos" value="$pos" min="1" max="$total_count" class="form-control form-control-sm text-center" style="width:50px" title="Pozisyon">
          <button class="btn btn-sm btn-outline-secondary" title="Taşı">→</button>
        </form>
        <img src="$src" alt="$alt_en" style="width:120px;height:90px;object-fit:contain;background:#f8f9fa;border-radius:6px;cursor:pointer" onclick="editRef($idx)" title="Düzenlemek için tıkla">
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
          <button class="btn btn-sm btn-outline-primary" onclick="editRef($idx)" title="Düzenle">✏️</button>
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
    $cards = '<div class="col-12 text-center py-5 text-muted">Henüz referans görseli yok.</div>';
}

$refs_json = json_encode($refs, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS);

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
          <div class="col-12"><hr><strong class="text-muted">Alt Metinler (SEO — sayfada görünmez)</strong></div>
          <div class="col-md-6"><input name="alt_en" class="form-control form-control-sm" placeholder="EN alt text"></div>
          <div class="col-md-6"><input name="alt_tr" class="form-control form-control-sm" placeholder="TR alt metin"></div>
          <div class="col-md-6"><input name="alt_de" class="form-control form-control-sm" placeholder="DE alt text"></div>
          <div class="col-md-6"><input name="alt_fr" class="form-control form-control-sm" placeholder="FR alt text"></div>
          <div class="col-md-6"><input name="alt_es" class="form-control form-control-sm" placeholder="ES alt text"></div>
          <div class="col-md-6"><input name="alt_ru" class="form-control form-control-sm" placeholder="RU alt text"></div>
          <div class="col-md-6"><input name="alt_zh" class="form-control form-control-sm" placeholder="ZH 图片描述"></div>
          <div class="col-md-6"><input name="alt_ar" class="form-control form-control-sm" placeholder="AR نص بديل" dir="rtl"></div>
          <div class="col-12"><hr><strong class="text-muted">Metadata (opsiyonel)</strong></div>
          <div class="col-md-3"><input name="sector" class="form-control form-control-sm" placeholder="Sektör"></div>
          <div class="col-md-3"><input name="fair" class="form-control form-control-sm" placeholder="Fuar adı"></div>
          <div class="col-md-2"><input name="city" class="form-control form-control-sm" placeholder="Şehir"></div>
          <div class="col-md-2"><input name="country" class="form-control form-control-sm" placeholder="Ülke"></div>
          <div class="col-md-2"><input name="year" class="form-control form-control-sm" placeholder="Yıl"></div>
          <div class="col-12"><button type="submit" class="btn btn-accent">Yükle ve İşle</button></div>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="row g-3" id="ref-list">
  $cards
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <form method="post" action="actions/save-reference.php">
      <input type="hidden" name="csrf_token" value="$csrf">
      <input type="hidden" name="id" id="edit-id">
      <div class="modal-header"><h5 class="modal-title">Görsel Düzenle</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12"><strong class="text-muted">Alt Metinler</strong></div>
          <div class="col-md-6"><label class="form-label small">EN</label><input name="alt_en" id="edit-alt-en" class="form-control form-control-sm"></div>
          <div class="col-md-6"><label class="form-label small">TR</label><input name="alt_tr" id="edit-alt-tr" class="form-control form-control-sm"></div>
          <div class="col-md-6"><label class="form-label small">DE</label><input name="alt_de" id="edit-alt-de" class="form-control form-control-sm"></div>
          <div class="col-md-6"><label class="form-label small">FR</label><input name="alt_fr" id="edit-alt-fr" class="form-control form-control-sm"></div>
          <div class="col-md-6"><label class="form-label small">ES</label><input name="alt_es" id="edit-alt-es" class="form-control form-control-sm"></div>
          <div class="col-md-6"><label class="form-label small">RU</label><input name="alt_ru" id="edit-alt-ru" class="form-control form-control-sm"></div>
          <div class="col-md-6"><label class="form-label small">ZH</label><input name="alt_zh" id="edit-alt-zh" class="form-control form-control-sm"></div>
          <div class="col-md-6"><label class="form-label small">AR</label><input name="alt_ar" id="edit-alt-ar" class="form-control form-control-sm" dir="rtl"></div>
          <div class="col-12"><hr><strong class="text-muted">Metadata</strong></div>
          <div class="col-md-3"><label class="form-label small">Sektör</label><input name="sector" id="edit-sector" class="form-control form-control-sm"></div>
          <div class="col-md-3"><label class="form-label small">Fuar</label><input name="fair" id="edit-fair" class="form-control form-control-sm"></div>
          <div class="col-md-2"><label class="form-label small">Şehir</label><input name="city" id="edit-city" class="form-control form-control-sm"></div>
          <div class="col-md-2"><label class="form-label small">Ülke</label><input name="country" id="edit-country" class="form-control form-control-sm"></div>
          <div class="col-md-2"><label class="form-label small">Yıl</label><input name="year" id="edit-year" class="form-control form-control-sm"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
        <button type="submit" class="btn btn-accent">Kaydet</button>
      </div>
    </form>
  </div></div>
</div>

<style>.ref-card{transition:transform .15s}.ref-card:hover{transform:translateY(-1px)}</style>
<script>
const refsData = {$refs_json};
function editRef(idx) {
  const r = refsData[idx];
  if (!r) return;
  document.getElementById('edit-id').value = idx;
  document.getElementById('edit-alt-en').value = r.alt_en || '';
  document.getElementById('edit-alt-tr').value = r.alt_tr || '';
  document.getElementById('edit-alt-de').value = r.alt_de || '';
  document.getElementById('edit-alt-fr').value = r.alt_fr || '';
  document.getElementById('edit-alt-es').value = r.alt_es || '';
  document.getElementById('edit-alt-ru').value = r.alt_ru || '';
  document.getElementById('edit-alt-zh').value = r.alt_zh || '';
  document.getElementById('edit-alt-ar').value = r.alt_ar || '';
  document.getElementById('edit-sector').value = r.sector || '';
  document.getElementById('edit-fair').value = r.fair || '';
  document.getElementById('edit-city').value = r.city || '';
  document.getElementById('edit-country').value = r.country || '';
  document.getElementById('edit-year').value = r.year || '';
  new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>
HEREDOC;

require_once __DIR__ . '/admin-layout.php';
render_admin_layout('Referanslar', $content, 'references');
