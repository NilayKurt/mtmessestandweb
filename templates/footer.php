<?php
function render_footer(string $lang, int $depth, string $page_type): string {
    // Asset prefix by depth: 1 = en/, tr/ etc; 2 = en/blog/ etc
    $ap = [1 => '../assets/', 2 => '../../assets/'][$depth];

    // Link paths by page_type
    if ($page_type === 'home') {
        $hp = '#services';
        $hp_projects = '#services';
        $about_href = 'about.html';
        $contact_href = '#contact';
    } elseif ($page_type === 'blog' || $page_type === 'blog_index') {
        $hp = '../index.html#services';
        $hp_projects = '../index.html#services';
        $about_href = '../about.html';
        $contact_href = '../contact.html';
    } else {
        // about, contact
        $hp = 'index.html#services';
        $hp_projects = 'index.html#services';
        $about_href = 'about.html';
        $contact_href = 'contact.html';
    }

    // Translations
    $t = [
        'en' => [
            'tagline'   => 'MT MesseStand &mdash; Your trade fair partner across three continents. Custom stands, modular systems, and full exhibitor services worldwide.',
            'standards' => 'Built to international trade fair standards.',
            'services_h'=> 'Services',
            'services'  => ['Custom Wooden', 'Maxima Modular', 'Package Upgrade', 'Country Pavilions', 'Exhibitor Services', 'Organizer Services'],
            'company_h' => 'Company',
            'company'   => ['About', 'Projects', 'Contact'],
            'lang_h'    => 'Language',
            'copyright' => '&copy; 2026 MT MesseStand. All Rights Reserved.',
        ],
        'tr' => [
            'tagline'   => 'MT MesseStand &mdash; Üç kıtadaki fuar çözüm ortağınız. Dünya çapında özel ahşap stantlar, modüler sistemler ve eksiksiz katılımcı hizmetleri.',
            'standards' => 'Uluslararası fuarcılık standartlarında üretim.',
            'services_h'=> 'Hizmetlerimiz',
            'services'  => ['Özel Ahşap Stantlar', 'Maxima Modüler', 'Stant Geliştirme', 'Ülke Pavyonları', 'Katılımcı Hizmetleri', 'Organizatör Hizmetleri'],
            'company_h' => 'Kurumsal',
            'company'   => ['Hakkımızda', 'Projelerimiz', 'İletişim'],
            'lang_h'    => 'Dil',
            'copyright' => '&copy; 2026 MT MesseStand. Tüm Hakları Saklıdır.',
        ],
        'de' => [
            'tagline'   => 'MT MesseStand &mdash; Ihr Messepartner auf drei Kontinenten. Maßgefertigte Stände, modulare Systeme und umfassende Ausstellerservices weltweit.',
            'standards' => 'Gefertigt nach internationalen Messestandards.',
            'services_h'=> 'Leistungen',
            'services'  => ['Maßanfertigungen', 'Maxima Modular', 'Paket-Upgrade', 'Länderpavillons', 'Ausstellerservice', 'Veranstalterservice'],
            'company_h' => 'Unternehmen',
            'company'   => ['Über uns', 'Projekte', 'Kontakt'],
            'lang_h'    => 'Sprache',
            'copyright' => '&copy; 2026 MT MesseStand. Alle Rechte vorbehalten.',
        ],
        'fr' => [
            'tagline'   => 'MT MesseStand &mdash; Votre partenaire salons sur trois continents. Stands sur mesure, systèmes modulaires et services exposants complets dans le monde entier.',
            'standards' => 'Fabriqué selon les normes internationales des salons.',
            'services_h'=> 'Services',
            'services'  => ['Sur Mesure', 'Maxima Modulaire', 'Pack Amélioration', 'Pavillons Nationaux', 'Services Exposants', 'Services Organisateurs'],
            'company_h' => 'Entreprise',
            'company'   => ['À Propos', 'Projets', 'Contact'],
            'lang_h'    => 'Langue',
            'copyright' => '&copy; 2026 MT MesseStand. Tous droits réservés.',
        ],
        'es' => [
            'tagline'   => 'MT MesseStand &mdash; Su socio ferial en tres continentes. Stands a medida, sistemas modulares y servicios completos para expositores en todo el mundo.',
            'standards' => 'Fabricado según estándares feriales internacionales.',
            'services_h'=> 'Servicios',
            'services'  => ['A Medida', 'Maxima Modular', 'Mejora de Paquete', 'Pabellones Nacionales', 'Servicios al Expositor', 'Servicios al Organizador'],
            'company_h' => 'Empresa',
            'company'   => ['Nosotros', 'Proyectos', 'Contacto'],
            'lang_h'    => 'Idioma',
            'copyright' => '&copy; 2026 MT MesseStand. Todos los derechos reservados.',
        ],
        'ar' => [
            'tagline'   => 'MT MesseStand &mdash; شريكك في المعارض عبر ثلاث قارات. منصات مخصصة وأنظمة معيارية وخدمات عارضين شاملة حول العالم.',
            'standards' => 'مصنوع وفقًا لمعايير المعارض الدولية.',
            'services_h'=> 'الخدمات',
            'services'  => ['منصات مخصصة', 'ماكسيما المعيارية', 'ترقية الباقة', 'أجنحة الدول', 'خدمات العارضين', 'خدمات المنظمين'],
            'company_h' => 'الشركة',
            'company'   => ['من نحن', 'المشاريع', 'اتصل بنا'],
            'lang_h'    => 'اللغة',
            'copyright' => '&copy; 2026 MT MesseStand. جميع الحقوق محفوظة.',
        ],
        'zh' => [
            'tagline'   => 'MT MesseStand &mdash; 您在三大洲的展会合作伙伴。全球定制展台、模块化系统和全方位参展商服务。',
            'standards' => '按照国际展会标准建造。',
            'services_h'=> '服务',
            'services'  => ['定制木制', 'Maxima模块化', '套餐升级', '国家馆', '参展商服务', '主办方服务'],
            'company_h' => '公司',
            'company'   => ['关于我们', '项目', '联系我们'],
            'lang_h'    => '语言',
            'copyright' => '&copy; 2026 MT MesseStand. 版权所有。',
        ],
    ];
    $ft = $t[$lang] ?? $t['en'];
    $s = $ft['services'];
    $c = $ft['company'];

    // Language badges
    $allLangs = ['en' => 'EN', 'tr' => 'TR', 'de' => 'DE', 'fr' => 'FR', 'es' => 'ES', 'ar' => 'AR', 'zh' => 'ZH'];
    $badges = '';
    foreach ($allLangs as $code => $name) {
        $active = $code === $lang ? ' bg-accent' : ' bg-dark';
        if ($page_type === 'blog' || $page_type === 'blog_index') {
            $bhref = '/' . $code . '/blog/';
        } elseif ($page_type === 'about') {
            $bhref = '/' . $code . '/about.html';
        } elseif ($page_type === 'contact') {
            $bhref = '/' . $code . '/contact.html';
        } else {
            $bhref = '/' . $code . '/';
        }
        $badges .= '              <a href="' . $bhref . '" class="badge' . $active . ' text-white text-decoration-none px-2 py-1">' . $name . '</a>' . "\n";
    }

    return '
<footer class="footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <img src="' . $ap . 'img/logo.webp" alt="MT Messe Stand" width="100" height="42" class="mb-3">
        <p>' . $ft['tagline'] . '</p>
        <p class="small">' . $ft['standards'] . ' <a href="https://www.auma.de/en" target="_blank" rel="noopener">AUMA</a> · <a href="https://www.dguv.de" target="_blank" rel="noopener">DGUV</a></p>
        <div class="social-links mt-3">
          <a href="https://www.linkedin.com/company/mt-messe-stand" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
          <a href="https://www.instagram.com/mtmessestand" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
          <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
        </div>
      </div>
      <div class="col-lg-2 col-md-4">
        <h5>' . $ft['services_h'] . '</h5>
        <ul class="list-unstyled">
          <li><a href="' . $hp . '">' . $s[0] . '</a></li>
          <li><a href="' . $hp . '">' . $s[1] . '</a></li>
          <li><a href="' . $hp . '">' . $s[2] . '</a></li>
          <li><a href="' . $hp . '">' . $s[3] . '</a></li>
          <li><a href="' . $hp . '">' . $s[4] . '</a></li>
          <li><a href="' . $hp . '">' . $s[5] . '</a></li>
        </ul>
      </div>
      <div class="col-lg-2 col-md-4">
        <h5>' . $ft['company_h'] . '</h5>
        <ul class="list-unstyled">
          <li><a href="' . $about_href . '">' . $c[0] . '</a></li>
          <li><a href="' . $hp_projects . '">' . $c[1] . '</a></li>
          <li><a href="' . $contact_href . '">' . $c[2] . '</a></li>
        </ul>
      </div>
      <div class="col-lg-3 col-md-6">
        <h5>' . $ft['lang_h'] . '</h5>
        <div class="d-flex gap-2">
' . $badges . '        </div>
      </div>
    </div>
    <div class="text-center">
      <small>' . $ft['copyright'] . '</small>
    </div>
  </div>
</footer>';
}
