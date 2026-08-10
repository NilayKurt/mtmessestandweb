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

$img_html = '';
foreach ($images as $img) {
    $i = htmlspecialchars($img);
    $img_html .= "<div class='col-md-3 col-sm-4 mb-3'><div class='card'><img src='..$i' class='card-img-top' style='height:120px;object-fit:cover' loading='lazy'><div class='card-body p-1'><small class='text-muted'>$i</small></div></div></div>";
}

$picker_js = isset($_GET['picker']) ? "<script>window.onload=function(){document.querySelectorAll('.pick-btn').forEach(function(b){b.style.display='inline-block';b.onclick=function(){if(window.opener){window.opener.document.getElementById('image-input').value=this.dataset.path;window.close();}}});}</script>" : '';

$uploaded = isset($_GET['uploaded']);
$toast = $uploaded ? '<div class="toast show align-items-center text-bg-success border-0 position-fixed top-0 end-0 m-3" role="alert"><div class="d-flex"><div class="toast-body">✓ Yüklendi</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>' : '';

$content = <<<HTML
$toast
<h4 class="mb-3">🖼️ Medya Kütüphanesi</h4>
<form method="post" action="actions/upload.php" enctype="multipart/form-data" class="mb-4">
  <input type="hidden" name="csrf_token" value="$token">
  <div class="input-group">
    <input type="file" name="image" class="form-control" accept=".webp,.jpg,.jpeg,.png" required>
    <button type="submit" class="btn btn-accent">Yükle</button>
  </div>
  <small class="text-muted">Max 2MB — .webp, .jpg, .png</small>
</form>
<div class="row">$img_html</div>
<style>.pick-btn{display:none;font-size:.7rem;margin-top:2px}</style>
$picker_js
HTML;

render_admin_layout('Media — MT Messe Admin', $content, 'media');
