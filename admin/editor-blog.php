<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin-layout.php';
require_login();

$lang = $_GET['lang'] ?? 'tr';
$_SESSION['lang'] = $lang;

$slug = $_GET['slug'] ?? '';
$is_new = empty($slug);

$defaults = ['title' => '', 'date' => date('Y-m-d'), 'summary' => '', 'image' => '', 'slug' => '', 'content' => ''];
$data = $defaults;

if (!$is_new) {
    $json_path = __DIR__ . "/../data/$lang/blog/$slug.json";
    if (file_exists($json_path)) {
        $data = array_merge($defaults, json_decode(file_get_contents($json_path), true));
    }
}

$saved = isset($_GET['saved']);
$token = csrf_token();
$h = fn($k) => htmlspecialchars($data[$k] ?? '');
$lang_name = LANGUAGES[$lang] ?? $lang;
$page_title = $data['title'] ? htmlspecialchars($data['title']) : 'Yeni Yazı';
$toast = $saved ? '<div class="toast show align-items-center text-bg-success border-0 position-fixed top-0 end-0 m-3" role="alert"><div class="d-flex"><div class="toast-body">✓ Kaydedildi</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>' : '';

$content = <<<HTML
$toast
<h4 class="mb-3">📝 Blog — $page_title — $lang_name</h4>
<form method="post" action="actions/save-blog.php" id="blog-form">
  <input type="hidden" name="csrf_token" value="$token">
  <input type="hidden" name="lang" value="$lang">
  <input type="hidden" name="old_slug" value="{$h('slug')}">
  <div class="row mb-3">
    <div class="col-md-7">
      <label class="form-label">Başlık</label>
      <input type="text" name="title" id="title-input" class="form-control" value="{$h('title')}" required>
    </div>
    <div class="col-md-2">
      <label class="form-label">Tarih</label>
      <input type="date" name="date" class="form-control" value="{$h('date')}">
    </div>
    <div class="col-md-3">
      <label class="form-label">Slug (URL)</label>
      <input type="text" name="slug" id="slug-input" class="form-control" value="{$h('slug')}" placeholder="auto" pattern="[a-z0-9-]+">
    </div>
  </div>
  <div class="mb-3">
    <label class="form-label">Özet (SEO + kartlar)</label>
    <input type="text" name="summary" class="form-control" value="{$h('summary')}">
  </div>
  <div class="mb-3">
    <label class="form-label">Görsel</label>
    <div class="input-group">
      <input type="text" name="image" id="image-input" class="form-control" value="{$h('image')}" placeholder="/assets/img/blog/hero.webp">
      <button type="button" class="btn btn-outline-secondary" onclick="window.open('media.php?lang=$lang&picker=1','media-picker','width=800,height=500')">🖼️ Seç</button>
    </div>
  </div>
  <div class="mb-3">
    <label class="form-label">İçerik</label>
    <div id="editor" style="min-height:400px">{$h('content')}</div>
  </div>
  <textarea name="content" id="content-hidden" style="display:none"></textarea>
  <button type="submit" class="btn btn-accent">💾 Kaydet</button>
</form>
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
var quill = new Quill('#editor', {
  theme: 'snow',
  modules: { toolbar: [['bold','italic'],['blockquote'],[{ 'header': [2,3,false] }],[{ 'list': 'ordered'},{ 'list': 'bullet'}],['link','image'],['clean']] }
});
document.getElementById('blog-form').onsubmit = function() {
  document.getElementById('content-hidden').value = quill.root.innerHTML;
  // Auto slug from title
  var slug = document.getElementById('slug-input');
  if (!slug.value) {
    var t = document.getElementById('title-input').value;
    slug.value = t.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
  }
};
</script>
HTML;

render_admin_layout('Blog Editor — MT Messe Admin', $content, 'blog');
