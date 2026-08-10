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

// Build usage map: scan all blog JSONs for image references
$usage = [];
foreach (glob(__DIR__ . "/../data/*/blog/*.json") as $jf) {
    $d = json_decode(file_get_contents($jf), true);
    $img = $d['image'] ?? '';
    if ($img && !isset($usage[$img])) {
        $usage[$img] = ['slug' => $d['slug'] ?? '', 'title' => $d['title'] ?? '', 'lang' => explode('/', dirname($jf))[2] ?? ''];
    }
}

$img_html = '';
foreach ($images as $img) {
    $i = htmlspecialchars($img);
    $info = $usage[$img] ?? null;
    $usage_html = '';
    if ($info) {
        $t = htmlspecialchars($info['title']);
        $s = htmlspecialchars($info['slug']);
        $l = htmlspecialchars($info['lang']);
        $usage_html = "<div class='mt-2'><small class='text-muted'>📝 <a href='editor-blog.php?slug=$s' style='font-size:.8rem'>$t</a> ($l)</small></div>";
    } else {
        $usage_html = "<div class='mt-2'><small class='text-warning'>⚠ Kullanılmıyor</small></div>";
    }
    $img_html .= <<<CARD
<div class="col-md-3 col-sm-4 mb-3">
  <div class="card h-100">
    <img src="..$i" class="card-img-top" style="height:120px;object-fit:cover" loading="lazy">
    <div class="card-body p-2">
      <small class="text-muted d-block text-truncate">$i</small>
      $usage_html
      <form method="post" action="actions/delete-media.php" onsubmit="return confirm('Bu görseli silmek istediğine emin misin?')" class="mt-1">
        <input type="hidden" name="csrf_token" value="$token">
        <input type="hidden" name="file" value="$i">
        <button class="btn btn-sm btn-outline-danger w-100" style="font-size:.7rem">🗑️ Sil</button>
      </form>
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

$lang_name = LANGUAGES[$lang] ?? $lang;
render_admin_layout('Media — MT Messe Admin', $content, 'media');
