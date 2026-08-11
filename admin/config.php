<?php
define('ADMIN_PASSWORD', password_hash('CHANGE_ME', PASSWORD_BCRYPT));
define('SITE_URL', 'https://mtmessestand.com');
define('LANGUAGES', ['en' => 'English', 'tr' => 'Türkçe', 'de' => 'Deutsch', 'fr' => 'Français', 'es' => 'Español', 'ar' => 'العربية', 'zh' => '中文', 'ru' => 'Русский']);
define('DATA_DIR', __DIR__ . '/../data');
