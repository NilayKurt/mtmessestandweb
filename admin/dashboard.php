<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin-layout.php';
require_login();

$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'tr';
$_SESSION['lang'] = $lang;

$blog_dir = __DIR__ . "/../data/$lang/blog";
$posts = [];
if (is_dir($blog_dir)) {
    foreach (glob("$blog_dir/*.json") as $f) {
        $data = json_decode(file_get_contents($f), true);
        if ($data) $posts[] = $data;
    }
}
usort($posts, function($a, $b) { return strcmp($b['date'] ?? '', $a['date'] ?? ''); });

$cards = '';
$token = csrf_token();
foreach ($posts as $p) {
    $t = htmlspecialchars($p['title'] ?? '');
    $d = htmlspecialchars($p['date'] ?? '');
    $s = htmlspecialchars($p['slug'] ?? '');
    $cards .= <<<CARD
<div class="col-md-4 mb-3">
  <div class="card p-3">
    <h6 class="mb-1">$t</h6>
    <small class="text-muted">$d</small>
    <div class="mt-2 d-flex gap-2">
      <a href="editor-blog.php?slug=$s" class="btn btn-sm btn-outline-secondary">✏️</a>
      <form method="post" action="actions/delete-blog.php" onsubmit="return confirm('Emin misin?')" style="display:inline">
        <input type="hidden" name="csrf_token" value="$token">
        <input type="hidden" name="slug" value="$s">
        <button class="btn btn-sm btn-outline-danger">🗑️</button>
      </form>
    </div>
  </div>
</div>
CARD;
}

$lang_name = LANGUAGES[$lang] ?? $lang;

$content = <<<HTML
<h4 class="mb-3">📝 Blog — $lang_name</h4>
<div class="mb-4">
  <a href="editor-blog.php" class="btn btn-accent">+ Yeni Blog</a>
</div>
<div class="row">$cards</div>
<div class="mt-4 pt-3 border-top d-flex gap-2">
  <a href="editor-page.php?page=about" class="btn btn-outline-secondary">📄 About</a>
  <a href="editor-page.php?page=contact" class="btn btn-outline-secondary">📄 Contact</a>
  <a href="media.php" class="btn btn-outline-secondary">🖼️ Medya</a>
</div>
HTML;

render_admin_layout('Dashboard — MT Messe Admin', $content, 'blog');
