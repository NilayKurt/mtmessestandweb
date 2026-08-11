<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin-layout.php';
require_login();

$lang = $_SESSION['lang'] ?? 'tr';
$page = $_GET['page'] ?? 'about';
$BASE = realpath(__DIR__ . '/..');
$data_dir = "$BASE/data/$lang";
if (!is_dir($data_dir)) mkdir($data_dir, 0755, true);
$json_path = "$data_dir/$page.json";

if (!file_exists($json_path)) {
    $html = '';
    foreach ([$lang, 'en'] as $try) {
        $f = "$BASE/$try/$page.html";
        if (file_exists($f)) { $html = file_get_contents($f); break; }
    }
    if ($html) {
        preg_match('/<h1[^>]*>([^<]+)<\/h1>/', $html, $t);
        preg_match('/<main>(.*?)<\/main>/s', $html, $m);
        $data = ['title' => $t[1] ?? '', 'content' => trim($m[1] ?? ''), 'meta_desc' => ''];
        file_put_contents($json_path, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}

$data = file_exists($json_path) ? json_decode(file_get_contents($json_path), true) : ['title'=>'','content'=>''];
$saved = isset($_GET['saved']);
$token = csrf_token();
$lang_name = LANGUAGES[$lang] ?? $lang;
$t_esc = htmlspecialchars($data['title'] ?? '');
$c_raw = $data['content'] ?? '';
$toast = $saved ? '<div class="toast show align-items-center text-bg-success border-0 position-fixed top-0 end-0 m-3" role="alert"><div class="d-flex"><div class="toast-body">✓ Kaydedildi</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>' : '';

$content = <<<HTML
$toast
<h4 class="mb-3">📄 $page — $lang_name</h4>
<form method="post" action="actions/save-page.php" id="pf">
  <input type="hidden" name="csrf_token" value="$token">
  <input type="hidden" name="page" value="$page">
  <div class="mb-3"><label class="form-label">Başlık</label><input type="text" name="title" class="form-control" value="$t_esc" required></div>
  <div class="mb-3">
    <label class="form-label">İçerik</label>
    <div class="mb-2">
      <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('imgUpload').click()">🖼️ Görsel Ekle</button>
      <input type="file" id="imgUpload" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="uploadImage(this)">
      <span id="uploadStatus" class="text-muted small ms-2"></span>
    </div>
    <div id="editor" style="min-height:400px">$c_raw</div>
  </div>
  <textarea name="content" id="ch" style="display:none"></textarea>
  <button type="submit" class="btn btn-accent">💾 Kaydet</button>
</form>
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
const quill = new Quill("#editor",{theme:"snow",modules:{toolbar:[["bold","italic"],["blockquote"],[{header:[2,3,false]}],[{list:"ordered"},{list:"bullet"}],["link"],["clean"]]}});
document.getElementById("pf").onsubmit=function(){document.getElementById("ch").value=quill.root.innerHTML};

async function uploadImage(input) {
  if (!input.files[0]) return;
  const status = document.getElementById('uploadStatus');
  status.textContent = 'Yükleniyor...';
  const fd = new FormData();
  fd.append('image', input.files[0]);
  fd.append('csrf_token', '$token');
  fd.append('target', 'about');
  fd.append('inline', '1');
  try {
    const resp = await fetch('actions/upload.php', { method: 'POST', body: fd });
    const data = await resp.json();
    if (data.success) {
      const range = quill.getSelection(true);
      quill.insertEmbed(range.index, 'image', data.url);
      quill.setSelection(range.index + 1);
      status.textContent = '✓ Yüklendi';
    } else {
      status.textContent = '✗ Hata';
    }
  } catch(e) {
    status.textContent = '✗ Bağlantı hatası';
  }
  input.value = '';
}
</script>
HTML;

render_admin_layout(ucfirst($page) . ' — MT Messe Admin', $content, $page);
