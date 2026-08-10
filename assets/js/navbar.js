// MT Messe Stand — Shared Navbar
// Include: <div id="navbar-placeholder"></div> + <script src="../assets/js/navbar.js"></script>
(function() {
  var path = window.location.pathname;

  // Detect language: /en/... -> en, /tr/... -> tr
  var langMatch = path.match(new RegExp('^/(en|tr|de|fr|es|ar|zh)/'));
  var currentLang = langMatch ? langMatch[1] : null;

  // Page slug: everything after /en/ or /tr/ etc.
  var pageSlug = langMatch ? '/' + path.slice(langMatch[0].length) : path;
  // Clean trailing slash
  if (pageSlug.length > 1 && pageSlug.slice(-1) === '/') pageSlug = pageSlug.slice(0, -1);

  // For blog posts, resolve target filename per language
  // because filenames differ (e.g., germany-hidden-costs.html vs almanya-hidden-costs.html)
  var blogMap = {
    'germany-hidden-costs.html': { en: 'germany-hidden-costs.html', tr: 'almanya-hidden-costs.html' },
    'almanya-hidden-costs.html':  { en: 'germany-hidden-costs.html', tr: 'almanya-hidden-costs.html' },
    'first-time-exhibitor-guide.html': { en: 'first-time-exhibitor-guide.html', tr: 'ilk-kez-katilacaklar-rehberi.html' },
    'ilk-kez-katilacaklar-rehberi.html': { en: 'first-time-exhibitor-guide.html', tr: 'ilk-kez-katilacaklar-rehberi.html' }
  };

  // Active page detection
  var active = 'home';
  if (pageSlug.indexOf('/about') >= 0) active = 'about';
  else if (pageSlug.indexOf('/blog') >= 0) active = 'blog';
  else if (pageSlug.indexOf('/contact') >= 0) active = 'contact';

  // All 6 languages — only EN has pages now
  var allLangs = [
    { name: 'EN', code: 'en', available: true },
    { name: 'TR', code: 'tr', available: true },
    { name: 'DE', code: 'de', available: false },
    { name: 'FR', code: 'fr', available: false },
    { name: 'ES', code: 'es', available: false },
    { name: 'AR', code: 'ar', available: false },
    { name: 'ZH', code: 'zh', available: false }
  ];

  var currentLabel = 'EN';
  for (var i = 0; i < allLangs.length; i++) {
    if (allLangs[i].code === currentLang) { currentLabel = allLangs[i].name; break; }
  }

  // Language dropdown: switch preserves current page
  var langOptions = '';
  for (var i = 0; i < allLangs.length; i++) {
    var l = allLangs[i];
    if (l.code === currentLang) continue;
    // Resolve target slug: for blog posts, map filename to target language
    var targetSlug = pageSlug;
    if (pageSlug.indexOf('/blog/') >= 0 && pageSlug.indexOf('.html') >= 0) {
      var fn = pageSlug.split('/').pop();
      if (blogMap[fn] && blogMap[fn][l.code]) {
        targetSlug = pageSlug.replace(fn, blogMap[fn][l.code]);
      }
    }
    var target = l.available ? '/' + l.code + targetSlug : '#';
    var label = l.name + (l.available ? '' : ' (soon)');
    var cls = l.available ? '' : ' text-muted';
    langOptions += '<li><a class="dropdown-item' + cls + '" href="' + target + '">' + label + '</a></li>\n';
  }

  var navBase = active==='blog' ? '../' : '';

  var nav = '\n<header id="header" class="fixed-top">\n  <nav class="navbar navbar-expand-lg">\n    <div class="container">\n      <a class="navbar-brand" href="' + navBase + 'index.html">\n        <img src="/assets/img/logo.webp" alt="MT Messe Stand" width="186" height="36">\n        <span class="brand-text">MT Messe Stand</span>\n      </a>\n      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar" aria-label="Toggle navigation">\n        <span class="navbar-toggler-icon"></span>\n      </button>\n      <div class="collapse navbar-collapse" id="navbar">\n        <ul class="navbar-nav ms-auto">\n          <li class="nav-item"><a class="nav-link' + (active==='home'?' active':'') + '" href="' + navBase + 'index.html">Home</a></li>\n          <li class="nav-item"><a class="nav-link' + (active==='about'?' active':'') + '" href="' + navBase + 'about.html">About</a></li>\n          <li class="nav-item"><a class="nav-link' + (active==='blog'?' active':'') + '" href="' + (active==='blog' ? './' : 'blog/') + '">Blog</a></li>\n          <li class="nav-item"><a class="nav-link' + (active==='contact'?' active':'') + '" href="' + navBase + 'contact.html">Contact</a></li>\n          <li class="nav-item dropdown">\n            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">' + currentLabel + '</a>\n            <ul class="dropdown-menu">\n' + langOptions + '            </ul>\n          </li>\n        </ul>\n      </div>\n    </div>\n  </nav>\n</header>';

  var placeholder = document.getElementById('navbar-placeholder');
  if (placeholder) {
    placeholder.outerHTML = nav;
  }
})();
