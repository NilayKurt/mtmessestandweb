<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin-layout.php';
require_login();

$lang = $_SESSION['lang'] ?? 'tr';
$page = $_GET['page'] ?? 'about';

$data_dir = __DIR__ . "/../data/$lang";
if (!is_dir($data_dir)) mkdir($data_dir, 0755, true);

$json_path = "$data_dir/$page.json";
$data = file_exists($json_path) ? json_decode(file_get_contents($json_path), true) : ['title' => '', 'content' => ''];
$saved = isset($_GET['saved']);

$token = csrf_token();
$lang_name = LANGUAGES[$lang] ?? $lang;
$page_label = ucfirst($page);
$toast = $saved ? '<div class="toast show align-items-center text-bg-success border-0 position-fixed top-0 end-0 m-3" role="alert"><div class="d-flex"><div class="toast-body">✓ Kaydedildi</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>' : '';

$content = <<<HTML
$toast
<h4 class="mb-3">📄 $page_label — $lang_name</h4>
<form method="post" action="actions/save-page.php">
  <input type="hidden" name="csrf_token" value="$token">
  <input type="hidden" name="page" value="$page">
  <div class="mb-3">
    <label class="form-label">Başlık</label>
    <input type="text" name="title" class="form-control" value="{$data['title']}" required>
  </div>
  <div class="mb-3">
    <label class="form-label">İçerik</label>
    <div id="editor" style="height:300px">{$data['content']}</div>
  </div>
  <textarea name="content" id="content-hidden" style="display:none"></textarea>
  <button type="submit" class="btn btn-accent">💾 Kaydet</button>
</form>
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
var quill = new Quill('#editor', {
  theme: 'snow',
  modules: { toolbar: [['bold','italic'],['blockquote'],[{ 'header': [2,3,false] }],[{ 'list': 'ordered'},{ 'list': 'bullet'}],['link'],['clean']] }
});
document.querySelector('form').onsubmit = function() { document.getElementById('content-hidden').value = quill.root.innerHTML; };
</script>
HTML;

render_admin_layout("Edit $page_label — MT Messe Admin", $content, 'page');
