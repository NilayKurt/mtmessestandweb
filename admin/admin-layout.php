<?php
function render_admin_layout(string $title, string $content, string $current_page = 'blog'): void {
    $lang = $_SESSION['lang'] ?? 'tr';
    $langs = LANGUAGES;
    $lang_name = $langs[$lang] ?? $lang;
    $sidebar_items = [
        'blog'       => ['📝', 'Blog', 'dashboard.php'],
        'about'      => ['📄', 'About', 'editor-page.php?page=about'],
        'contact'    => ['📧', 'Contact', 'editor-page.php?page=contact'],
        'references' => ['🖼️', 'Referanslar', 'editor-references.php'],
        'media'      => ['📁', 'Medya', 'media.php'],
    ];
    $sidebar_html = '';
    foreach ($sidebar_items as $key => [$icon, $label, $href]) {
        $active = $current_page === $key ? ' active' : '';
        $sidebar_html .= "<a href=\"$href\" class=\"sidebar-link$active\">$icon $label</a>\n";
    }
    $lang_options = '';
    foreach ($langs as $code => $name) {
        $sel = $code === $lang ? ' selected' : '';
        $lang_options .= "<option value=\"$code\"$sel>$name</option>\n";
    }
    echo <<<HTML
<!DOCTYPE html>
<html lang="$lang">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>$title — MT Messe Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root { --accent: #cc0000; --dark: #1a1a1a; }
    body { background: #f5f5f5; font-family: system-ui, sans-serif; }
    .sidebar { width: 220px; min-height: 100vh; background: var(--dark); position: fixed; left: 0; top: 0; padding: 1.5rem 0; }
    .sidebar .logo { color: #fff; text-align: center; font-size: 1.1rem; font-weight: 700; padding: 0 1rem 1.5rem; border-bottom: 1px solid #333; margin-bottom: 1rem; }
    .sidebar .logo span { color: var(--accent); }
    .sidebar-link { display: flex; align-items: center; gap: .5rem; color: #999; text-decoration: none; padding: .6rem 1.5rem; font-size: .9rem; transition: .15s; }
    .sidebar-link:hover, .sidebar-link.active { color: #fff; background: rgba(204,0,0,.15); }
    .topbar { background: #fff; border-bottom: 1px solid #eee; padding: .6rem 1.5rem; display: flex; align-items: center; gap: 1rem; position: sticky; top: 0; z-index: 10; }
    .topbar select { width: auto; }
    .main { margin-left: 220px; padding: 1.5rem 2rem; }
    .btn-accent { background: var(--accent); color: #fff; border: none; }
    .btn-accent:hover { background: #aa0000; color: #fff; }
    .toast-container { position: fixed; top: 1rem; right: 1rem; z-index: 9999; }
    @media (max-width: 768px) { .sidebar { width: 100%; min-height: auto; position: static; display: flex; gap: .5rem; padding: .5rem 1rem; flex-wrap: wrap; }
      .sidebar .logo { border: none; margin: 0; padding: 0 .5rem 0 0; }
      .main { margin-left: 0; } }
  </style>
</head>
<body>
<div class="sidebar">
  <div class="logo">MT <span>Messe Stand</span></div>
  $sidebar_html
</div>
<div class="topbar">
  <span class="text-muted small me-2">İçerik dili:</span>
  <form method="post" action="actions/set-lang.php" style="display:inline">
    <select class="form-select form-select-sm" name="lang" onchange="this.form.submit()" style="max-width:140px">$lang_options</select>
  </form>
  <span class="badge bg-accent ms-2">$lang_name</span>
  <div class="ms-auto text-muted small">Admin</div>
  <a href="logout.php" class="btn btn-sm btn-outline-secondary">Çıkış</a>
</div>
<div class="main">
$content
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
HTML;
}
