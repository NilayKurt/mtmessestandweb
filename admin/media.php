<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin-layout.php';
require_login();

$lang = $_SESSION['lang'] ?? 'tr';
$token = csrf_token();

$img_dir = __DIR__ . '/../assets/img/blog';
$images = [];
if (is_dir($img_dir)) {
    foreach (glob("$img_dir/*.{webp,jpg,jpeg,png}", GLOB_BRACE) as $f) {
        $images[] = '/assets/img/blog/' . basename($f);
    }
}

// Build usage map: scan all blog JSONs, keyed by filename
$usage = [];
$data_base = __DIR__ . '/../data/';
foreach (glob("$data_base/*/blog/*.json") as $jf) {
    $d = json_decode(file_get_contents($jf), true);
    if (!$d) continue;
    $img_path = $d['image'] ?? '';
    if (!$img_path) continue;
    $fn = basename($img_path); // normalize to just filename
    $rel = str_replace($data_base, '', $jf);
    $parts = explode('/', $rel);
    $blog_lang = strtoupper($parts[0] ?? '');
    if (!isset($usage[$fn])) $usage[$fn] = [];
    $usage[$fn][] = ['slug' => $d['slug'] ?? '', 'title' => $d['title'] ?? '', 'lang' => $blog_lang];
}

$img_html = '';
foreach ($images as $img) {
    $i = htmlspecialchars($img);
    $fn = basename($img); // normalize lookup key
    $entries = $usage[$fn] ?? [];
    $usage_html = '';
    if ($entries) {
        $links = [];
        foreach ($entries as $e) {
            $t = htmlspecialchars($e['title']);
            $s = htmlspecialchars($e['slug']);
            $l = htmlspecialchars($e['lang']);
            $links[] = "<a href='editor-blog.php?slug=$s'>$t</a> ($l)";
        }
        $usage_html = "<div class='mt-2'><small class='text-muted'>📝 " . implode(', ', $links) . "</small></div>";
    } else {
        $usage_html = "<div class='mt-2'><small class='text-warning'>⚠ Kullanılmıyor</small></div>";
    }
    $in_use = !empty($entries);
    $del_btn = !$in_use
        ? "<form method=\"post\" action=\"actions/delete-media.php\" onsubmit=\"return confirm('Bu görseli silmek istediğine emin misin?')\" class=\"mt-1\">
        <input type=\"hidden\" name=\"csrf_token\" value=\"$token\">
        <input type=\"hidden\" name=\"file\" value=\"$i\">
        <button class=\"btn btn-sm btn-outline-danger w-100\" style=\"font-size:.7rem\">🗑️ Sil</button>
      </form>"
        : "<div class='mt-1'><small class='text-muted' style='font-size:.65rem'>Kullanımda — silinemez</small></div>";
    $img_html .= <<<CARD
<div class="col-md-3 col-sm-4 mb-3">
  <div class="card h-100">
    <img src="..$i" class="card-img-top" style="height:120px;object-fit:cover" loading="lazy">
    <div class="card-body p-2">
      <small class="text-muted d-block text-truncate">$i</small>
      $usage_html
      <form method="post" action="actions/upload.php" enctype="multipart/form-data" class="mt-1">
        <input type="hidden" name="csrf_token" value="$token">
        <input type="hidden" name="replace" value="$i">
        <input type="file" name="image" class="form-control form-control-sm mb-1" accept=".webp,.jpg,.jpeg,.png" style="font-size:.7rem" onchange="this.form.submit()">
        <button type="submit" class="btn btn-sm btn-outline-primary w-100" style="font-size:.7rem">🔄 Değiştir</button>
      </form>
      $del_btn
    </div>
  </div>
</div>
CARD;
}

$picker_js = isset($_GET['picker']) ? "<script>window.onload=function(){document.querySelectorAll('.pick-btn').forEach(function(b){b.style.display='inline-block';b.onclick=function(){if(window.opener){window.opener.document.getElementById('image-input').value=this.dataset.path;window.close();}}});}</script>" : '';

$uploaded = isset($_GET['uploaded']);
$deleted = isset($_GET['deleted']);
$error_msg = $_GET['error'] ?? '';
$toast = '';
if ($uploaded) $toast = '<div class="toast show align-items-center text-bg-success border-0 position-fixed top-0 end-0 m-3" role="alert"><div class="d-flex"><div class="toast-body">✓ Yüklendi</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>';
if ($deleted) $toast = '<div class="toast show align-items-center text-bg-success border-0 position-fixed top-0 end-0 m-3" role="alert"><div class="d-flex"><div class="toast-body">✓ Silindi</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>';
if ($error_msg) $toast = '<div class="toast show align-items-center text-bg-danger border-0 position-fixed top-0 end-0 m-3" role="alert"><div class="d-flex"><div class="toast-body">⚠ ' . htmlspecialchars($error_msg) . '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>';

$lang_name = LANGUAGES[$lang] ?? $lang;

$content = <<<HTML
$toast
<h4 class="mb-3">🖼️ Medya Kütüphanesi <span class="text-muted small">($lang_name)</span></h4>
<form method="post" action="actions/upload.php" enctype="multipart/form-data" class="mb-4">
  <input type="hidden" name="csrf_token" value="$token">
  <div class="input-group">
    <input type="file" name="image" class="form-control" accept=".webp,.jpg,.jpeg,.png" required>
    <button type="submit" class="btn btn-accent">Yükle</button>
  </div>
  <small class="text-muted">Max 2MB — .webp, .jpg, .png — Aynı isim varsa reddedilir</small>
</form>
<div class="row">$img_html</div>
$picker_js
HTML;

render_admin_layout('Media — MT Messe Admin', $content, 'media');
