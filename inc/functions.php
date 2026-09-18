<?php
require_once __DIR__ . '/../config/database.php';

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function getCurrentLang() {
    $lang = $_SESSION['lang'] ?? 'en';
    $lang = in_array($lang, ['en', 'ne', 'hi'], true) ? $lang : 'en';
    return $lang;
}

function loadLang($key) {
    $lang = getCurrentLang();
    $file = __DIR__ . '/lang/' . $lang . '.php';
    static $cache = [];
    if (!isset($cache[$lang])) {
        $cache[$lang] = file_exists($file) ? require $file : [];
    }
    if (isset($cache[$lang][$key])) {
        return $cache[$lang][$key];
    }
    if ($lang !== 'en') {
        if (!isset($cache['en'])) {
            $enFile = __DIR__ . '/lang/en.php';
            $cache['en'] = file_exists($enFile) ? require $enFile : [];
        }
        if (isset($cache['en'][$key])) {
            return $cache['en'][$key];
        }
    }
    return $key;
}

function t($key) {
    return e(loadLang($key));
}

function tf($key, ...$args) {
    $text = loadLang($key);
    return e($args ? vsprintf($text, $args) : $text);
}

function langSwitchUrl($code) {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $parts = parse_url($uri);
    $path = $parts['path'] ?? '/';
    $query = [];
    if (!empty($parts['query'])) {
        parse_str($parts['query'], $query);
    }
    $query['lang'] = $code;
    return $path . '?' . http_build_query($query);
}

function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string) $token);
}

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function renderFlash() {
    if (empty($_SESSION['flash'])) {
        return '';
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    $type = $flash['type'] ?? 'info';
    $message = e($flash['message'] ?? '');

    return '<div class="alert alert-' . $type . ' shadow-sm rounded-4">' . $message . '</div>';
}

function getSiteSetting($field, $default = '') {
    global $pdo;
    static $settings = null;

    if ($field === '__refresh__') {
        $settings = null;
        return $default;
    }

    if ($settings === null) {
        $settings = $pdo->query('SELECT * FROM tbl_settings LIMIT 1')->fetch(PDO::FETCH_ASSOC);
        if (!$settings) {
            $settings = [];
        }
    }

    $fieldName = preg_replace('/[^a-zA-Z0-9_]/', '', $field);
    return array_key_exists($fieldName, $settings) && $settings[$fieldName] !== null ? $settings[$fieldName] : $default;
}

function getAdminContactEmail() {
    global $pdo;

    try {
        $row = $pdo->query('SELECT contact_email FROM tbl_settings LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $row = false;
    }

    $email = trim((string) ($row['contact_email'] ?? ''));
    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return $email;
    }

    return trim((string) (defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : ''));
}

function refreshSiteSettingsCache() {
    getSiteSetting('__refresh__');
}

function ensureAuthIntegrations() {
    global $pdo;
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }

    $ready = false;
    $altered = false;

    try {
        $custCol = $pdo->query("SHOW COLUMNS FROM `tbl_customer` LIKE 'cust_google_id'");
        if ($custCol && $custCol->rowCount() === 0) {
            $pdo->exec("ALTER TABLE `tbl_customer` ADD COLUMN `cust_google_id` varchar(64) NOT NULL DEFAULT '' AFTER `cust_email`");
            $altered = true;
        }

        $tokenTimeCol = $pdo->query("SHOW COLUMNS FROM `tbl_customer` LIKE 'cust_token_time'");
        if ($tokenTimeCol && $tokenTimeCol->rowCount() === 0) {
            $pdo->exec("ALTER TABLE `tbl_customer` ADD COLUMN `cust_token_time` bigint NOT NULL DEFAULT 0 AFTER `cust_token`");
            $altered = true;
        }

        $settingCols = [
            'google_client_id' => "varchar(255) NOT NULL DEFAULT ''",
            'google_client_secret' => "varchar(255) NOT NULL DEFAULT ''",
            'recaptcha_site_key' => "varchar(255) NOT NULL DEFAULT ''",
            'recaptcha_secret_key' => "varchar(255) NOT NULL DEFAULT ''",
        ];
        foreach ($settingCols as $column => $definition) {
            $stmt = $pdo->query("SHOW COLUMNS FROM `tbl_settings` LIKE " . $pdo->quote($column));
            if ($stmt && $stmt->rowCount() === 0) {
                $pdo->exec("ALTER TABLE `tbl_settings` ADD COLUMN `{$column}` {$definition}");
                $altered = true;
            }
        }

        if ($altered) {
            refreshSiteSettingsCache();
        }
        $ready = true;
    } catch (Throwable $e) {
        $ready = false;
    }

    return $ready;
}

function getAuthConfig($key, $default = '') {
    ensureAuthIntegrations();

    $envMap = [
        'google_client_id' => 'GOOGLE_CLIENT_ID',
        'google_client_secret' => 'GOOGLE_CLIENT_SECRET',
        'recaptcha_site_key' => 'RECAPTCHA_SITE_KEY',
        'recaptcha_secret_key' => 'RECAPTCHA_SECRET_KEY',
    ];

    $fromSettings = trim((string) getSiteSetting($key, ''));
    if ($fromSettings !== '') {
        return $fromSettings;
    }

    $envName = $envMap[$key] ?? '';
    if ($envName !== '') {
        $fromEnv = getenv($envName);
        if ($fromEnv !== false && trim((string) $fromEnv) !== '') {
            return trim((string) $fromEnv);
        }
    }

    return $default;
}

function isGoogleAuthEnabled() {
    return getAuthConfig('google_client_id') !== '' && getAuthConfig('google_client_secret') !== '';
}

function isRecaptchaEnabled() {
    return getAuthConfig('recaptcha_site_key') !== '' && getAuthConfig('recaptcha_secret_key') !== '';
}

function verifyRecaptcha($response) {
    if (!isRecaptchaEnabled()) {
        return true;
    }

    $response = trim((string) $response);
    if ($response === '') {
        return false;
    }

    $payload = http_build_query([
        'secret' => getAuthConfig('recaptcha_secret_key'),
        'response' => $response,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
    ]);

    $raw = authHttpRequest('https://www.google.com/recaptcha/api/siteverify', $payload, 'application/x-www-form-urlencoded');
    if ($raw === null) {
        return false;
    }

    $data = json_decode($raw, true);
    return !empty($data['success']);
}

function authHttpRequest($url, $body = null, $contentType = null, $method = null) {
    $method = $method ?: ($body === null ? 'GET' : 'POST');
    $headers = ['Accept: application/json'];
    if ($contentType) {
        $headers[] = 'Content-Type: ' . $contentType;
    }

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $method,
        ]);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($raw === false || $code >= 400) {
            return null;
        }
        return $raw;
    }

    $opts = [
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headers),
            'timeout' => 20,
            'ignore_errors' => true,
        ],
    ];
    if ($body !== null) {
        $opts['http']['content'] = $body;
    }
    $raw = @file_get_contents($url, false, stream_context_create($opts));
    return $raw === false ? null : $raw;
}

function googleAuthRedirectUri() {
    return BASE_URL . 'account/google-callback.php';
}

function getGoogleAuthUrl($redirect = '') {
    if (!isGoogleAuthEnabled()) {
        return '';
    }

    $state = bin2hex(random_bytes(16));
    $_SESSION['google_oauth_state'] = $state;
    $_SESSION['google_oauth_redirect'] = trim((string) $redirect);

    $params = [
        'client_id' => getAuthConfig('google_client_id'),
        'redirect_uri' => googleAuthRedirectUri(),
        'response_type' => 'code',
        'scope' => 'openid email profile',
        'access_type' => 'online',
        'prompt' => 'select_account',
        'state' => $state,
    ];

    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
}

function exchangeGoogleAuthCode($code) {
    $payload = http_build_query([
        'code' => $code,
        'client_id' => getAuthConfig('google_client_id'),
        'client_secret' => getAuthConfig('google_client_secret'),
        'redirect_uri' => googleAuthRedirectUri(),
        'grant_type' => 'authorization_code',
    ]);

    $raw = authHttpRequest('https://oauth2.googleapis.com/token', $payload, 'application/x-www-form-urlencoded');
    if ($raw === null) {
        return null;
    }

    $token = json_decode($raw, true);
    if (empty($token['access_token'])) {
        return null;
    }

    $accessToken = $token['access_token'];
    $profileRaw = null;

    if (function_exists('curl_init')) {
        $ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Accept: application/json',
            ],
        ]);
        $profileRaw = curl_exec($ch);
        curl_close($ch);
        if ($profileRaw === false) {
            return null;
        }
    } else {
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => "Authorization: Bearer {$accessToken}\r\nAccept: application/json\r\n",
                'timeout' => 20,
                'ignore_errors' => true,
            ],
        ];
        $profileRaw = @file_get_contents('https://www.googleapis.com/oauth2/v3/userinfo', false, stream_context_create($opts));
        if ($profileRaw === false) {
            return null;
        }
    }

    $profile = json_decode($profileRaw, true);
    if (empty($profile['email'])) {
        return null;
    }

    return $profile;
}

function loginOrRegisterGoogleUser(array $profile) {
    global $pdo;
    ensureAuthIntegrations();

    $email = trim((string) ($profile['email'] ?? ''));
    $googleId = trim((string) ($profile['sub'] ?? ''));
    $name = trim((string) ($profile['name'] ?? ''));
    if ($name === '') {
        $name = trim((string) (($profile['given_name'] ?? '') . ' ' . ($profile['family_name'] ?? '')));
    }
    if ($name === '') {
        $name = strstr($email, '@', true) ?: 'Customer';
    }

    if ($email === '' || $googleId === '') {
        return ['ok' => false, 'error' => 'google_auth_failed'];
    }

    $customer = null;
    $stmt = $pdo->prepare('SELECT * FROM tbl_customer WHERE cust_google_id = ? LIMIT 1');
    $stmt->execute([$googleId]);
    $customer = $stmt->fetch();

    if (!$customer) {
        $stmt = $pdo->prepare('SELECT * FROM tbl_customer WHERE cust_email = ? LIMIT 1');
        $stmt->execute([$email]);
        $customer = $stmt->fetch();
    }

    if ($customer) {
        if ((string) ($customer['cust_status'] ?? '1') !== '1') {
            return ['ok' => false, 'error' => 'account_inactive'];
        }
        if (empty($customer['cust_google_id'])) {
            $pdo->prepare('UPDATE tbl_customer SET cust_google_id = ? WHERE cust_id = ?')
                ->execute([$googleId, $customer['cust_id']]);
        }
        $_SESSION['customer_id'] = $customer['cust_id'];
        $_SESSION['customer_name'] = $customer['cust_name'];
        linkGuestBookingsByEmail((int) $customer['cust_id'], $customer['cust_email']);
        return ['ok' => true, 'new' => false];
    }

    $stmt = $pdo->prepare('INSERT INTO tbl_customer (cust_name, cust_cname, cust_email, cust_google_id, cust_phone, cust_country, cust_address, cust_city, cust_state, cust_zip, cust_b_name, cust_b_cname, cust_b_phone, cust_b_country, cust_b_address, cust_b_city, cust_b_state, cust_b_zip, cust_s_name, cust_s_cname, cust_s_phone, cust_s_country, cust_s_address, cust_s_city, cust_s_state, cust_s_zip, cust_password, cust_token, cust_datetime, cust_timestamp, cust_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $name, '', $email, $googleId, '', 0, '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', 0, '', '', '', '',
        hashCustomerPassword(bin2hex(random_bytes(16))),
        '', date('Y-m-d H:i:s'), time(), 1,
    ]);

    $customerId = (int) $pdo->lastInsertId();
    linkGuestBookingsByEmail($customerId, $email);
    $_SESSION['customer_id'] = $customerId;
    $_SESSION['customer_name'] = $name;

    return ['ok' => true, 'new' => true];
}

function renderRecaptchaWidget() {
    if (!isRecaptchaEnabled()) {
        return '';
    }
    $siteKey = e(getAuthConfig('recaptcha_site_key'));
    return '<div class="g-recaptcha" data-sitekey="' . $siteKey . '"></div>';
}

function renderRecaptchaScript() {
    if (!isRecaptchaEnabled()) {
        return '';
    }
    return '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
}

function renderGoogleAuthButton($redirect = '') {
    if (!isGoogleAuthEnabled()) {
        return '';
    }
    $url = e(getGoogleAuthUrl($redirect));
    $label = t('continue_with_google');
    return '<a href="' . $url . '" class="btn btn-outline-dark d-flex align-items-center justify-content-center gap-2">'
        . '<i class="fa-brands fa-google" aria-hidden="true"></i><span>' . $label . '</span></a>';
}

function renderAuthDivider() {
    return '<div class="d-flex align-items-center gap-3 my-1">'
        . '<hr class="flex-grow-1 m-0">'
        . '<span class="small text-muted text-uppercase">' . t('or') . '</span>'
        . '<hr class="flex-grow-1 m-0">'
        . '</div>';
}

function seoCleanText($value, $maxLen = 0) {
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $value)));
    if ($maxLen > 0 && $text !== '' && mb_strlen($text) > $maxLen) {
        $text = rtrim(mb_substr($text, 0, $maxLen - 1)) . '…';
    }
    return $text;
}

function seoPick($value, $fallback = '', $maxLen = 0) {
    $text = seoCleanText($value, $maxLen);
    if ($text !== '') {
        return $text;
    }
    return seoCleanText($fallback, $maxLen);
}

function getHomeSeo() {
    $siteName = (string) getSiteSetting('site_name', SITE_NAME);
    return [
        'title' => seoPick(
            getSiteSetting('meta_title_home', ''),
            'Professional Cleaning Services in Auckland | 8848 Cleaning Service'
        ),
        'keywords' => seoPick(
            getSiteSetting('meta_keyword_home', ''),
            'cleaning service Auckland, professional cleaning Auckland, home cleaning Auckland, office cleaning Auckland, commercial cleaning Auckland, deep cleaning Auckland, carpet cleaning Auckland, end of lease cleaning Auckland, move out cleaning Auckland, spring cleaning Auckland, house cleaning Auckland, business cleaning Auckland, building cleaning Auckland, professional cleaner Auckland, cleaning company Auckland, trusted cleaning service, affordable cleaning Auckland, insured cleaning, experienced cleaners Auckland'
        ),
        'description' => seoPick(
            getSiteSetting('meta_description_home', ''),
            loadLang('meta_home_description'),
            160
        ),
    ];
}

function getSocialProfileUrls() {
    global $pdo;
    static $urls = null;
    if ($urls !== null) {
        return $urls;
    }
    $urls = [];
    try {
        $rows = $pdo->query('SELECT social_name, social_url FROM tbl_social')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $url = trim((string) ($row['social_url'] ?? ''));
            if ($url !== '' && preg_match('#^https?://#i', $url)) {
                $urls[] = $url;
            }
        }
    } catch (Throwable $e) {
        $urls = [];
    }
    return $urls;
}

function getDefaultSeoJsonLd() {
    $homeSeo = getHomeSeo();

    $siteName = (string) getSiteSetting('site_name', SITE_NAME);
    $siteUrl = rtrim(BASE_URL, '/');

    $phone = trim((string) getSiteSetting('contact_phone', ''));
    $email = trim((string) getSiteSetting('contact_email', ''));
    $addressText = trim((string) getSiteSetting('contact_address', ''));

    $logo = (string) getSiteSetting('logo', '');
    $logoUrl = $logo !== ''
        ? getProductImage($logo)
        : (ASSET_URL . 'images/og-default.png');

    $social = getSocialProfileUrls();

    $website = [
        '@type' => 'WebSite',
        '@id' => $siteUrl . '/#website',
        'url' => $siteUrl,
        'name' => $siteName,
        'description' => $homeSeo['description'],
        'inLanguage' => 'en',
        'publisher' => [
            '@id' => $siteUrl . '/#business'
        ],
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => $siteUrl . '/search.php?q={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ];

    $business = [
        '@type' => [
            'LocalBusiness',
            'CleaningService'
        ],

        '@id' => $siteUrl . '/#business',

        'name' => $siteName,

        'alternateName' => [
            '8848 Cleaning Service',
            '8848cleaningservice',
            '8848 Cleaning Service Auckland',
            '8848 Cleaning Service New Zealand',
        ],

        'url' => $siteUrl,

        'description' => $homeSeo['description'],

        'image' => $logoUrl,

        'logo' => $logoUrl,

        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => $addressText,
            'addressLocality' => 'Auckland',
            'addressRegion' => 'Auckland',
            'addressCountry' => 'NZ',
        ],

        'areaServed' => [
            [
                '@type' => 'City',
                'name' => 'Auckland',
            ],
        ],

        'priceRange' => '$$',

        'currenciesAccepted' => 'NZD',

        'paymentAccepted' => 'Cash, Bank Transfer, Credit Card, Online Payment',

        'openingHoursSpecification' => [
            [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => [
                    'Monday',
                    'Tuesday',
                    'Wednesday',
                    'Thursday',
                    'Friday',
                    'Saturday',
                    'Sunday',
                ],
                'opens' => '07:00',
                'closes' => '20:00',
            ],
        ],
    ];

    if ($phone !== '') {
        $business['telephone'] = $phone;
    }

    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $business['email'] = $email;
    }

    if ($social) {
        $business['sameAs'] = $social;
    }

    return [
        '@context' => 'https://schema.org',
        '@graph' => [
            $website,
            $business
        ],
    ];
}

function getStaticPageSeo($prefix) {
    global $pdo;
    static $pageRow = null;

    if ($pageRow === null) {
        try {
            $pageRow = $pdo->query('SELECT * FROM tbl_page LIMIT 1')->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            $pageRow = [];
        }
    }

    $prefix = preg_replace('/[^a-z_]/', '', strtolower((string) $prefix));
    $home = getHomeSeo();
    $defaultTitles = [
        'about' => 'About 8848 Cleaning Service | Professional Cleaners Auckland',
        'contact' => 'Contact Professional Cleaning Service in Auckland | 8848',
        'faq' => 'FAQ | Professional Cleaning Services in Auckland - 8848',
    ];
    $defaultKeywords = [
        'about' => 'professional cleaning service Auckland, trusted cleaner, experienced cleaning company, cleaning services New Zealand, about 8848, cleaning team, professional cleaners',
        'contact' => 'contact cleaning service, book cleaning Auckland, professional cleaner contact, cleaning company, call professional cleaner, cleaning quote',
        'faq' => 'cleaning service FAQ, how cleaning works, cleaning questions, professional cleaning help, booking cleaning, cleaning process',
    ];
    $defaultDescriptions = [
        'about' => 'Learn about 8848 Cleaning Service - trusted professional cleaners in Auckland providing home and office cleaning with police-checked, insured staff.',
        'contact' => 'Contact 8848 Cleaning Service in Auckland. Book your professional home or office cleaning. Get a free quote. Phone, email, online form available.',
        'faq' => 'Frequently asked questions about our professional cleaning services in Auckland. Learn about our process, pricing, and how to book.',
    ];

    return [
        'title' => seoPick($pageRow[$prefix . '_meta_title'] ?? '', $defaultTitles[$prefix] ?? $home['title']),
        'keywords' => seoPick($pageRow[$prefix . '_meta_keyword'] ?? '', $defaultKeywords[$prefix] ?? $home['keywords']),
        'description' => seoPick(
            $pageRow[$prefix . '_meta_description'] ?? '',
            seoPick($pageRow[$prefix . '_content'] ?? '', $defaultDescriptions[$prefix] ?? $home['description'], 160),
            160
        ),
    ];
}

function renderHeroHeadline($customHeading = '') {
    $defaultTitle = loadLang('hero_default_title');
    $customHeading = trim((string) $customHeading);
    if ($customHeading !== '' && $customHeading !== $defaultTitle) {
        return '<h1 class="hero-headline hero-headline-plain">' . e($customHeading) . '</h1>';
    }

    $mid = trim(loadLang('hero_word_the'));
    $html = '<h1 class="hero-headline">';
    $html .= '<span class="hero-line">' . e(loadLang('hero_word_cleaner')) . '</span> ';
    $html .= '<span class="hero-chip"><span class="hero-chip-accent">' . e(loadLang('hero_word_when')) . '</span>';
    if ($mid !== '') {
        $html .= ' <span class="hero-chip-soft">' . e($mid) . '</span>';
    }
    $html .= ' <span class="hero-chip-accent">' . e(loadLang('hero_word_brand')) . '</span></span> ';
    $html .= '<span class="hero-line">' . e(loadLang('hero_word_cleans')) . '</span>';
    $html .= '</h1>';
    return $html;
}

function applySeoMeta(array $seo) {
    $result = [
        'title' => (string) ($seo['title'] ?? ''),
        'description' => (string) ($seo['description'] ?? ''),
        'keywords' => (string) ($seo['keywords'] ?? ''),
    ];

    // Populate caller scope via globals for simple page scripts.
    $GLOBALS['pageTitle'] = $result['title'] !== '' ? $result['title'] : ($GLOBALS['pageTitle'] ?? '');
    $GLOBALS['metaDescription'] = $result['description'] !== '' ? $result['description'] : ($GLOBALS['metaDescription'] ?? '');
    $GLOBALS['metaKeywords'] = $result['keywords'] !== '' ? $result['keywords'] : ($GLOBALS['metaKeywords'] ?? '');

    return $result;
}

function getInvoiceCompanyProfile() {
    $logo = (string) getSiteSetting('logo', '');
    $dueDays = (int) getSiteSetting('invoice_due_days', 30);
    if ($dueDays <= 0) {
        $dueDays = 30;
    }
    return [
        'site_name' => (string) getSiteSetting('site_name', SITE_NAME),
        'logo' => $logo,
        'logo_url' => getProductImage($logo ?: 'placeholder.png'),
        'address' => (string) getSiteSetting('contact_address', ''),
        'email' => (string) getSiteSetting('contact_email', ''),
        'phone' => (string) getSiteSetting('contact_phone', ''),
        'copyright' => (string) getSiteSetting('footer_copyright', ''),
        'about' => (string) getSiteSetting('footer_about', ''),
        'vat_no' => (string) getSiteSetting('invoice_vat_no', ''),
        'due_days' => $dueDays,
        'footer_note' => (string) getSiteSetting('invoice_footer_note', 'Thank you for choosing our cleaning service.'),
    ];
}

function getMarqueeNotices() {
    if (!(int)getSiteSetting('marquee_on_off', 1)) {
        return array();
    }

    $raw = trim((string)getSiteSetting('marquee_notices', ''));
    $notices = array();

    if ($raw !== '') {
        foreach (preg_split('/\r\n|\r|\n/', $raw) as $line) {
            $line = trim($line);
            if ($line !== '') {
                $notices[] = $line;
            }
        }
    }

    if (!$notices) {
        $notices = array(
            loadLang('marquee_1'),
            loadLang('marquee_2'),
            loadLang('marquee_3'),
            loadLang('marquee_4'),
        );
    }

    return $notices;
}

function getProductImage($filename) {
    if (empty($filename)) {
        return ASSET_URL . 'images/placeholder.svg';
    }

    if (preg_match('#^https?://#i', $filename)) {
        return $filename;
    }

    $cleanName = ltrim(str_replace('\\', '/', (string) $filename), '/');
    $possiblePaths = [];

    if (strpos($cleanName, 'assets/uploads/') === 0 || strpos($cleanName, 'uploads/') === 0) {
        $possiblePaths[] = __DIR__ . '/../' . $cleanName;
    }

    $possiblePaths[] = __DIR__ . '/../assets/uploads/' . $cleanName;
    $possiblePaths[] = __DIR__ . '/../assets/uploads/product_photos/' . $cleanName;
    $possiblePaths[] = __DIR__ . '/../' . $cleanName;

    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            $relative = ltrim(str_replace('\\', '/', str_replace(__DIR__ . '/../', '', $path)), '/');
            $url = BASE_URL . $relative;
            $mtime = @filemtime($path);
            if ($mtime) {
                $url .= '?v=' . rawurlencode($mtime . '-' . (int) @filesize($path));
            }
            return $url;
        }
    }

    return ASSET_URL . 'images/placeholder.png';
}

function getImageMimeByExtension($ext) {
    $ext = strtolower((string) $ext);
    $map = [
        'ico' => 'image/x-icon',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
    ];
    return $map[$ext] ?? 'image/png';
}

/**
 * Resolve site favicon from settings (falls back to logo).
 * @return array{url:string,type:string,file:string,path:string}
 */
function getSiteFavicon() {
    $candidates = [
        trim((string) getSiteSetting('favicon', '')),
        trim((string) getSiteSetting('logo', '')),
    ];

    foreach ($candidates as $file) {
        if ($file === '') {
            continue;
        }
        $path = __DIR__ . '/../assets/uploads/' . ltrim(str_replace('\\', '/', $file), '/');
        if (!is_file($path)) {
            continue;
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return [
            'url' => getProductImage($file),
            'type' => getImageMimeByExtension($ext),
            'file' => $file,
            'path' => $path,
        ];
    }

    return ['url' => '', 'type' => '', 'file' => '', 'path' => ''];
}

function getSocialLinks() {
    global $pdo;
    $stmt = $pdo->query('SELECT social_name, social_url FROM tbl_social WHERE social_url IS NOT NULL AND TRIM(social_url) <> "" ORDER BY social_name ASC');
    $rows = $stmt->fetchAll();

    $icons = [
        'Facebook' => 'fab fa-facebook-f',
        'Twitter' => 'fab fa-twitter',
        'Instagram' => 'fab fa-instagram',
        'LinkedIn' => 'fab fa-linkedin-in',
        'YouTube' => 'fab fa-youtube',
        'WhatsApp' => 'fab fa-whatsapp',
        'Pinterest' => 'fab fa-pinterest-p',
        'Google Plus' => 'fab fa-google-plus-g',
        'Snapchat' => 'fab fa-snapchat',
        'Quora' => 'fab fa-quora',
        'Reddit' => 'fab fa-reddit-alien',
    ];

    $links = [];
    foreach ($rows as $row) {
        $name = $row['social_name'] ?? '';
        $url = trim((string) ($row['social_url'] ?? ''));
        if ($url === '') {
            continue;
        }
        $links[] = [
            'name' => $name,
            'url' => $url,
            'icon' => $icons[$name] ?? 'fab fa-link',
        ];
    }

    return $links;
}

function normalizeWhatsAppNumber($value) {
    $raw = trim((string) $value);

    if ($raw === '') {
        return '';
    }

    // Already a full WhatsApp / chat URL
    if (preg_match('#^https?://#i', $raw)) {
        return $raw;
    }

    $digits = preg_replace('/\D+/', '', $raw);

    if ($digits === '') {
        return '';
    }

    // New Zealand number already using international country code
    if (strpos($digits, '64') === 0) {
        return $digits;
    }

    // New Zealand local mobile number:
    if (preg_match('/^02\d{7,9}$/', $digits)) {
        $digits = '64' . substr($digits, 1);
        return $digits;
    }

    // New Zealand local landline:
    // 09 123 4567 -> 6491234567
    if (preg_match('/^0\d{8,9}$/', $digits)) {
        $digits = '64' . substr($digits, 1);
        return $digits;
    }

    // Return as-is if already a numeric international number
    return $digits;
}

function getWhatsAppLink() {
    global $pdo;
    static $link = null;
    if ($link !== null) {
        return $link;
    }

    $candidates = [];

    try {
        $stmt = $pdo->prepare("SELECT social_url FROM tbl_social WHERE social_name = 'WhatsApp' AND social_url IS NOT NULL AND TRIM(social_url) <> '' LIMIT 1");
        $stmt->execute();
        $social = trim((string) $stmt->fetchColumn());
        if ($social !== '') {
            $candidates[] = $social;
        }
    } catch (Throwable $e) {
        // ignore and fall back
    }

    $phone = trim((string) getSiteSetting('contact_phone', ''));
    if ($phone !== '') {
        $candidates[] = $phone;
    }

    foreach ($candidates as $candidate) {
        $normalized = normalizeWhatsAppNumber($candidate);
        if ($normalized === '') {
            continue;
        }
        if (preg_match('#^https?://#i', $normalized)) {
            $link = $normalized;
            return $link;
        }
        $link = 'https://wa.me/' . $normalized;
        return $link;
    }

    $link = '';
    return $link;
}

function getProductGallery($productId) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT photo FROM tbl_product_photo WHERE p_id = ? ORDER BY pp_id ASC');
    $stmt->execute([$productId]);
    $photos = $stmt->fetchAll();

    if (!$photos) {
        return [[ 'photo' => getProductImage($productId . '.jpg') ]];
    }

    $gallery = [];
    foreach ($photos as $photo) {
        $gallery[] = [
            'photo' => getProductImage($photo['photo']),
        ];
    }
    return $gallery;
}

function getCategoryName($ecatId) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT e.ecat_name, m.mcat_name, t.tcat_name FROM tbl_end_category e LEFT JOIN tbl_mid_category m ON m.mcat_id = e.mcat_id LEFT JOIN tbl_top_category t ON t.tcat_id = m.tcat_id WHERE e.ecat_id = ?');
    $stmt->execute([$ecatId]);
    return $stmt->fetch();
}

function getTopCategories() {
    global $pdo;
    $stmt = $pdo->prepare('SELECT tcat_id, tcat_name FROM tbl_top_category WHERE show_on_menu = 1 ORDER BY tcat_id ASC');
    $stmt->execute();
    return $stmt->fetchAll();
}

function getMidCategories($tcatId) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT mcat_id, mcat_name FROM tbl_mid_category WHERE tcat_id = ? ORDER BY mcat_id ASC');
    $stmt->execute([$tcatId]);
    return $stmt->fetchAll();
}

function getEndCategories($mcatId) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT ecat_id, ecat_name FROM tbl_end_category WHERE mcat_id = ? ORDER BY ecat_id ASC');
    $stmt->execute([$mcatId]);
    return $stmt->fetchAll();
}

function cartCount() {
    return isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
}

function wishCount() {
    return isset($_SESSION['wishlist']) ? count($_SESSION['wishlist']) : 0;
}

function compareCount() {
    return isset($_SESSION['compare']) ? count($_SESSION['compare']) : 0;
}

function isLoggedIn() {
    return !empty($_SESSION['customer_id']);
}

function currentCustomer() {
    global $pdo;
    if (!isLoggedIn()) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT * FROM tbl_customer WHERE cust_id = ? LIMIT 1');
    $stmt->execute([$_SESSION['customer_id']]);
    return $stmt->fetch();
}

function ensureCustomerProfileColumns() {
    global $pdo;
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }

    try {
        $photoColumn = $pdo->query("SHOW COLUMNS FROM `tbl_customer` LIKE 'cust_photo'");
        if ($photoColumn && $photoColumn->rowCount() === 0) {
            $pdo->exec("ALTER TABLE `tbl_customer` ADD COLUMN `cust_photo` varchar(255) NOT NULL DEFAULT '' AFTER `cust_email`");
        }
        $ready = true;
    } catch (Throwable $e) {
        $ready = false;
    }

    return $ready;
}

function customerProfileImageUrl($filename = '') {
    $filename = basename(trim((string) $filename));
    if ($filename !== '' && is_file(__DIR__ . '/../assets/uploads/' . $filename)) {
        return UPLOAD_URL . rawurlencode($filename);
    }
    return ASSET_URL . 'images/og-default.png';
}

function ensureReferralTables() {
    global $pdo;
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }

    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS tbl_referral_settings (id INT UNSIGNED NOT NULL AUTO_INCREMENT, is_active TINYINT(1) NOT NULL DEFAULT 1, discount_type ENUM('percent','amount') NOT NULL DEFAULT 'percent', discount_value DECIMAL(10,2) NOT NULL DEFAULT 10.00, bonus_points INT UNSIGNED NOT NULL DEFAULT 100, points_per_dollar INT UNSIGNED NOT NULL DEFAULT 100, minimum_order_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00, terms TEXT NULL, updated_at DATETIME NULL, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS tbl_referral (id INT UNSIGNED NOT NULL AUTO_INCREMENT, referral_code VARCHAR(32) NOT NULL, referrer_customer_id INT UNSIGNED NOT NULL, referrer_name VARCHAR(255) NOT NULL, referrer_email VARCHAR(255) NOT NULL, referee_name VARCHAR(255) NOT NULL, referee_email VARCHAR(255) NOT NULL, referee_phone VARCHAR(50) NOT NULL, status ENUM('Pending','Converted','Cancelled') NOT NULL DEFAULT 'Pending', discount_type ENUM('percent','amount') NOT NULL DEFAULT 'percent', discount_value DECIMAL(10,2) NOT NULL DEFAULT 0.00, discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00, awarded_points INT UNSIGNED NOT NULL DEFAULT 0, referee_customer_id INT UNSIGNED NULL, payment_id INT UNSIGNED NULL, created_at DATETIME NOT NULL, converted_at DATETIME NULL, PRIMARY KEY (id), UNIQUE KEY uq_referral_code (referral_code), KEY idx_referral_referrer (referrer_customer_id), KEY idx_referral_status (status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $settingsBonus = $pdo->query("SHOW COLUMNS FROM `tbl_referral_settings` LIKE 'bonus_points'");
        if ($settingsBonus && $settingsBonus->rowCount() === 0) { $pdo->exec("ALTER TABLE `tbl_referral_settings` ADD COLUMN `bonus_points` INT UNSIGNED NOT NULL DEFAULT 100 AFTER `discount_value`"); }
        $pointsRate = $pdo->query("SHOW COLUMNS FROM `tbl_referral_settings` LIKE 'points_per_dollar'");
        if ($pointsRate && $pointsRate->rowCount() === 0) { $pdo->exec("ALTER TABLE `tbl_referral_settings` ADD COLUMN `points_per_dollar` INT UNSIGNED NOT NULL DEFAULT 100 AFTER `bonus_points`"); }
        $awardedPoints = $pdo->query("SHOW COLUMNS FROM `tbl_referral` LIKE 'awarded_points'");
        if ($awardedPoints && $awardedPoints->rowCount() === 0) { $pdo->exec("ALTER TABLE `tbl_referral` ADD COLUMN `awarded_points` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `discount_amount`"); }
        $pdo->exec("CREATE TABLE IF NOT EXISTS tbl_referral_points (id INT UNSIGNED NOT NULL AUTO_INCREMENT, customer_id INT UNSIGNED NOT NULL, referral_id INT UNSIGNED NULL, points INT NOT NULL DEFAULT 0, reason VARCHAR(255) NOT NULL DEFAULT 'Successful referral', created_at DATETIME NOT NULL, PRIMARY KEY (id), UNIQUE KEY uq_referral_points_referral (referral_id), KEY idx_referral_points_customer (customer_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pointsColumn = $pdo->query("SHOW COLUMNS FROM `tbl_referral_points` LIKE 'points'");
        $pointsDefinition = $pointsColumn ? $pointsColumn->fetch(PDO::FETCH_ASSOC) : null;
        if ($pointsDefinition && stripos((string) $pointsDefinition['Type'], 'unsigned') !== false) { $pdo->exec("ALTER TABLE `tbl_referral_points` MODIFY COLUMN `points` INT NOT NULL DEFAULT 0"); }
        $referralIdColumn = $pdo->query("SHOW COLUMNS FROM `tbl_referral_points` LIKE 'referral_id'");
        $referralIdDefinition = $referralIdColumn ? $referralIdColumn->fetch(PDO::FETCH_ASSOC) : null;
        if ($referralIdDefinition && stripos((string) $referralIdDefinition['Null'], 'NO') !== false) { $pdo->exec("ALTER TABLE `tbl_referral_points` MODIFY COLUMN `referral_id` INT UNSIGNED NULL"); }
        $index = $pdo->query("SHOW INDEX FROM `tbl_referral` WHERE Key_name = 'uq_referral_code'");
        if ($index && $index->rowCount() > 0) {
            $pdo->exec('ALTER TABLE `tbl_referral` DROP INDEX `uq_referral_code`');
        }
        $pdo->exec("INSERT INTO tbl_referral_settings (id, is_active, discount_type, discount_value, minimum_order_amount, terms, updated_at) SELECT 1, 1, 'percent', 10.00, 0.00, 'Referral discount applies to the referred customer''s first eligible booking only.', NOW() WHERE NOT EXISTS (SELECT 1 FROM tbl_referral_settings WHERE id = 1)");
        $ready = true;
    } catch (Throwable $e) {
        $ready = false;
    }

    return $ready;
}

function getReferralSettings() {
    global $pdo;
    if (!ensureReferralTables()) {
        return null;
    }
    $row = $pdo->query('SELECT * FROM tbl_referral_settings WHERE id = 1 LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function getReferralPoints($customerId) {
    global $pdo;
    ensureReferralTables();
    $stmt = $pdo->prepare('SELECT COALESCE(SUM(points), 0) FROM tbl_referral_points WHERE customer_id = ?');
    $stmt->execute([(int) $customerId]);
    return (int) $stmt->fetchColumn();
}

function completeReferralForPayment($paymentId) {
    global $pdo;
    $paymentId = (int) $paymentId;
    if ($paymentId <= 0) {
        return false;
    }

    ensureReferralTables();

    $stmt = $pdo->prepare("SELECT id, referrer_customer_id, awarded_points FROM tbl_referral WHERE payment_id = ? AND status = 'Pending' ORDER BY id ASC");
    $stmt->execute([$paymentId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) {
        return false;
    }

    foreach ($rows as $referral) {
        $referralId = (int) ($referral['id'] ?? 0);
        $referrerCustomerId = (int) ($referral['referrer_customer_id'] ?? 0);
        $awardedPoints = (int) ($referral['awarded_points'] ?? 0);

        if ($referralId <= 0) {
            continue;
        }

        $pdo->prepare("UPDATE tbl_referral SET status = 'Converted', converted_at = NOW() WHERE id = ? AND status = 'Pending'")->execute([$referralId]);

        if ($awardedPoints > 0 && $referrerCustomerId > 0) {
            $pdo->prepare("INSERT INTO tbl_referral_points (customer_id, referral_id, points, reason, created_at) VALUES (?, ?, ?, 'Successful referral', NOW()) ON DUPLICATE KEY UPDATE points = VALUES(points), reason = VALUES(reason), created_at = NOW()")
                ->execute([$referrerCustomerId, $referralId, $awardedPoints]);
        }
    }

    return true;
}

function ensurePromoCodeTable() {
    global $pdo;
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }

    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS tbl_promo_code (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            code VARCHAR(50) NOT NULL,
            discount_type ENUM('percent','amount') NOT NULL DEFAULT 'percent',
            discount_value DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            minimum_order_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            usage_limit INT UNSIGNED NOT NULL DEFAULT 0,
            used_count INT UNSIGNED NOT NULL DEFAULT 0,
            valid_from DATETIME NULL,
            valid_to DATETIME NULL,
            description VARCHAR(255) NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uq_promo_code (code)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $ready = true;
    } catch (Throwable $e) {
        $ready = false;
    }

    return $ready;
}

function getPromoCodeDetails($code) {
    global $pdo;
    if (!ensurePromoCodeTable()) {
        return null;
    }

    $code = strtoupper(trim((string) $code));
    if ($code === '') {
        return null;
    }

    $stmt = $pdo->prepare('SELECT * FROM tbl_promo_code WHERE code = ? LIMIT 1');
    $stmt->execute([$code]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row || (int) ($row['is_active'] ?? 0) !== 1) {
        return null;
    }

    $now = new DateTimeImmutable('now');
    if (!empty($row['valid_from']) && new DateTimeImmutable($row['valid_from']) > $now) {
        return null;
    }
    if (!empty($row['valid_to']) && new DateTimeImmutable($row['valid_to']) < $now) {
        return null;
    }

    $usageLimit = (int) ($row['usage_limit'] ?? 0);
    if ($usageLimit > 0 && (int) ($row['used_count'] ?? 0) >= $usageLimit) {
        return null;
    }

    return $row;
}

function promoDiscountAmount($subtotal, $settings) {
    $subtotal = max(0, (float) $subtotal);
    $minimum = max(0, (float) ($settings['minimum_order_amount'] ?? 0));
    if ($subtotal < $minimum) {
        return 0.0;
    }
    $value = max(0, (float) ($settings['discount_value'] ?? 0));
    $amount = (($settings['discount_type'] ?? 'percent') === 'amount') ? $value : ($subtotal * $value / 100);
    return round(min($subtotal, $amount), 2);
}

function referralDiscountAmount($subtotal, $settings) {
    $subtotal = max(0, (float) $subtotal);
    $minimum = max(0, (float) ($settings['minimum_order_amount'] ?? 0));
    if ($subtotal < $minimum) {
        return 0.0;
    }
    $value = max(0, (float) ($settings['discount_value'] ?? 0));
    $amount = (($settings['discount_type'] ?? 'percent') === 'amount') ? $value : ($subtotal * $value / 100);
    return round(min($subtotal, $amount), 2);
}

function generateReferralCode() {
    return '8848-' . strtoupper(bin2hex(random_bytes(4)));
}

function verifyPassword($input, $stored) {
    $stored = (string) $stored;
    if ($stored === '') {
        return false;
    }

    if (password_get_info($stored)['algo'] ?? null) {
        return password_verify($input, $stored);
    }

    if (strlen($stored) === 32 && ctype_xdigit($stored)) {
        return md5($input) === $stored;
    }

    return hash_equals($stored, (string) $input);
}

function hashCustomerPassword($password) {
    return password_hash((string) $password, PASSWORD_DEFAULT);
}

function safeAccountRedirect($redirect = '') {
    $redirect = trim((string) $redirect);
    if ($redirect === '') {
        return BASE_URL . 'account/profile.php';
    }
    if (preg_match('#^https?://#i', $redirect)) {
        $baseHost = parse_url(BASE_URL, PHP_URL_HOST);
        $redirectHost = parse_url($redirect, PHP_URL_HOST);
        if ($redirectHost && $baseHost && strcasecmp($redirectHost, $baseHost) === 0) {
            return $redirect;
        }
        return BASE_URL . 'account/profile.php';
    }
    if (strpos($redirect, '//') === 0 || strpos($redirect, '..') !== false) {
        return BASE_URL . 'account/profile.php';
    }
    return BASE_URL . ltrim($redirect, '/');
}

function linkGuestBookingsByEmail($customerId, $email) {
    global $pdo;
    $customerId = (int) $customerId;
    $email = trim((string) $email);
    if ($customerId <= 0 || $email === '') {
        return 0;
    }
    $stmt = $pdo->prepare('UPDATE tbl_payment SET customer_id = ? WHERE customer_id = 0 AND customer_email = ?');
    $stmt->execute([$customerId, $email]);
    return $stmt->rowCount();
}

function ensureContactInquiryTable() {
    global $pdo;
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `tbl_contact_inquiry` (
              `id` int NOT NULL AUTO_INCREMENT,
              `name` varchar(150) NOT NULL DEFAULT '',
              `email` varchar(190) NOT NULL DEFAULT '',
              `phone` varchar(60) NOT NULL DEFAULT '',
              `subject` varchar(255) NOT NULL DEFAULT '',
              `message` text NOT NULL,
            `promo_code` varchar(100) NOT NULL DEFAULT '',
              `created_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $promoColumn = $pdo->query("SHOW COLUMNS FROM `tbl_contact_inquiry` LIKE 'promo_code'");
        if ($promoColumn && $promoColumn->rowCount() === 0) {
            $pdo->exec("ALTER TABLE `tbl_contact_inquiry` ADD COLUMN `promo_code` varchar(100) NOT NULL DEFAULT '' AFTER `message`");
        }
        $ready = true;
    } catch (Throwable $e) {
        $ready = false;
    }
    return $ready;
}

function handleContactFormSubmission($redirectUrl = '') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['contact_form'])) {
        return null;
    }

    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', loadLang('invalid_request'));
        if ($redirectUrl !== '') {
            header('Location: ' . $redirectUrl);
            exit;
        }
        return ['type' => 'danger', 'message' => loadLang('invalid_request')];
    }

    $name = trim((string) ($_POST['contact_name'] ?? ''));
    $email = trim((string) ($_POST['contact_email'] ?? ''));
    $phone = trim((string) ($_POST['contact_phone'] ?? ''));
    $subject = trim((string) ($_POST['contact_subject'] ?? ''));
    $message = trim((string) ($_POST['contact_message'] ?? ''));
    $promoCode = strtoupper(trim((string) ($_POST['promo_code'] ?? '')));

    if ($name === '' || $email === '' || $message === '') {
        $msg = loadLang('contact_form_required');
        setFlash('danger', $msg);
        if ($redirectUrl !== '') {
            header('Location: ' . $redirectUrl);
            exit;
        }
        return ['type' => 'danger', 'message' => $msg];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = loadLang('newsletter_invalid_email');
        setFlash('danger', $msg);
        if ($redirectUrl !== '') {
            header('Location: ' . $redirectUrl);
            exit;
        }
        return ['type' => 'danger', 'message' => $msg];
    }

    if ($subject === '') {
        $subject = 'Website contact from ' . $name;
    }

    global $pdo;
    if (ensureContactInquiryTable()) {
        try {
            $stmt = $pdo->prepare("INSERT INTO tbl_contact_inquiry (name, email, phone, subject, message, promo_code, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $email, $phone, $subject, $message, $promoCode]);
        } catch (Throwable $e) {
            // continue to email attempt
        }
    }

    $to = getAdminContactEmail();
    $siteName = (string) getSiteSetting('site_name', SITE_NAME);
    $body = '<p><strong>New contact message from the website</strong></p>';
    $body .= '<p><strong>Name:</strong> ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '<br>';
    $body .= '<strong>Email:</strong> ' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '<br>';
    if ($phone !== '') {
        $body .= '<strong>Phone:</strong> ' . htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') . '<br>';
    }
    if ($promoCode !== '') {
        $body .= '<strong>Promo / referral code:</strong> ' . htmlspecialchars($promoCode, ENT_QUOTES, 'UTF-8') . '<br>';
    }
    $body .= '<strong>Subject:</strong> ' . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '</p>';
    $body .= '<p>' . nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) . '</p>';

    $sent = sendCustomerEmail($to, $siteName, $siteName . ' - ' . $subject, $body);
    if (!$sent) {
        $msg = 'Your message could not be sent right now. Please try again later or contact us directly.';
        setFlash('danger', $msg);
        if ($redirectUrl !== '') {
            header('Location: ' . $redirectUrl . '#contact');
            exit;
        }
        return ['type' => 'danger', 'message' => $msg];
    }

    $ok = loadLang('contact_form_success');
    setFlash('success', $ok);
    if ($redirectUrl !== '') {
        header('Location: ' . $redirectUrl . '#contact');
        exit;
    }
    return ['type' => 'success', 'message' => $ok];
}

function sendCustomerEmail($toEmail, $toName, $subject, $htmlBody) {
    $toEmail = trim((string) $toEmail);
    if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        error_log('Contact email failed: invalid recipient email - ' . $toEmail);
        return false;
    }

    // Get admin email from settings, fall back to SMTP_FROM_EMAIL if not set
    $adminEmail = getSiteSetting('contact_email', SMTP_FROM_EMAIL);
    if ($adminEmail === '' || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $adminEmail = SMTP_FROM_EMAIL;
    }

    $phpMailerPath = __DIR__ . '/../PHPMailer/src/PHPMailer.php';
    if (!is_file($phpMailerPath)) {
        $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\nFrom: " . SMTP_FROM_NAME . " <" . $adminEmail . ">\r\n";
        return @mail($toEmail, $subject, $htmlBody, $headers);
    }

    require_once __DIR__ . '/../PHPMailer/src/Exception.php';
    require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($adminEmail, SMTP_FROM_NAME);
        $mail->addReplyTo(SMTP_REPLYTO_EMAIL, SMTP_REPLYTO_NAME);
        $mail->addAddress($toEmail, $toName ?: $toEmail);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $company = getInvoiceCompanyProfile();
        $logoUrl = trim((string) ($company['logo_url'] ?? ''));
        $emailBody = '<div style="max-width:680px;margin:0 auto;font-family:Arial,sans-serif;color:#1f2937;">';
        if ($logoUrl !== '' && filter_var($logoUrl, FILTER_VALIDATE_URL)) {
            $emailBody .= '<div style="padding:18px 0;border-bottom:1px solid #e5e7eb;margin-bottom:24px;"><img src="' . htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars((string) $company['site_name'], ENT_QUOTES, 'UTF-8') . '" style="max-width:220px;max-height:70px;object-fit:contain;"></div>';
        }
        $emailBody .= $htmlBody;
        $emailBody .= '<p style="margin-top:28px;padding-top:14px;border-top:1px solid #e5e7eb;color:#6b7280;font-size:12px;">🌿 Thank you for helping us save paper. Please do not print this email unless necessary.</p></div>';
        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody)) . "\n\nThank you for helping us save paper. Please do not print this email unless necessary.";
        $mail->send();
        return true;
    } catch (Throwable $e) {
        error_log('Contact email failed: ' . $e->getMessage());
        return false;
    }
}

function sendAdminEmail($subject, $htmlBody) {
    $adminEmail = getAdminContactEmail();
    if ($adminEmail === '' || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        error_log('Admin email failed: invalid admin email - ' . $adminEmail);
        return false;
    }
    $siteName = (string) getSiteSetting('site_name', SITE_NAME);
    return sendCustomerEmail($adminEmail, $siteName, $subject, $htmlBody);
}

function notifyAdminNewsletter($subscriberEmail) {
    $subscriberEmail = trim((string) $subscriberEmail);
    if (!filter_var($subscriberEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    $siteName = (string) getSiteSetting('site_name', SITE_NAME);
    $htmlBody = '<h3>New Newsletter Subscriber</h3>';
    $htmlBody .= '<p><strong>Email:</strong> ' . htmlspecialchars($subscriberEmail) . '</p>';
    $htmlBody .= '<p><strong>Date:</strong> ' . date('Y-m-d H:i:s') . '</p>';
    $htmlBody .= '<p>A new user has subscribed to your newsletter.</p>';
    return sendAdminEmail('New Newsletter Subscriber - ' . $siteName, $htmlBody);
}

function notifyAdminBooking($paymentId, $customerName, $customerEmail, $customerPhone, $lineItems, $grandTotal, $serviceAddress, $preferredDate, $preferredTime, $remarks = '', $notes = '') {
    $siteName = (string) getSiteSetting('site_name', SITE_NAME);
    $lineItemsHtml = '<table style="width:100%; border-collapse:collapse; margin-bottom:20px;">';
    $lineItemsHtml .= '<tr style="background:#f0f0f0; border-bottom:1px solid #ddd;"><th style="padding:10px; text-align:left;">Service</th><th style="padding:10px; text-align:right;">Price</th></tr>';
    foreach ($lineItems as $item) {
        $lineItemsHtml .= '<tr style="border-bottom:1px solid #ddd;"><td style="padding:10px;">' . htmlspecialchars($item['product_name'] ?? '') . '</td><td style="padding:10px; text-align:right;">NZ$' . number_format($item['line_total'] ?? 0, 2) . '</td></tr>';
    }
    $lineItemsHtml .= '<tr style="background:#f0f0f0; font-weight:bold;"><td style="padding:10px;">Total</td><td style="padding:10px; text-align:right;">NZ$' . number_format($grandTotal, 2) . '</td></tr>';
    $lineItemsHtml .= '</table>';
    
    $htmlBody = '<h3>New Booking Request</h3>';
    $htmlBody .= '<p><strong>Booking ID:</strong> ' . htmlspecialchars($paymentId) . '</p>';
    $htmlBody .= '<h4>Customer Details</h4>';
    $htmlBody .= '<p><strong>Name:</strong> ' . htmlspecialchars($customerName) . '</p>';
    $htmlBody .= '<p><strong>Email:</strong> ' . htmlspecialchars($customerEmail) . '</p>';
    $htmlBody .= '<p><strong>Phone:</strong> ' . htmlspecialchars($customerPhone) . '</p>';
    $htmlBody .= '<h4>Service Details</h4>';
    $htmlBody .= '<p><strong>Address:</strong> ' . htmlspecialchars($serviceAddress) . '</p>';
    if ($preferredDate !== '' && $preferredDate !== null) {
        $htmlBody .= '<p><strong>Preferred Date:</strong> ' . htmlspecialchars($preferredDate) . '</p>';
    }
    if ($preferredTime !== '' && $preferredTime !== null) {
        $htmlBody .= '<p><strong>Preferred Time:</strong> ' . htmlspecialchars($preferredTime) . '</p>';
    }
    $htmlBody .= '<h4>Services Requested</h4>';
    $htmlBody .= $lineItemsHtml;
    if ($remarks !== '' || $notes !== '') {
        $htmlBody .= '<h4>Additional Notes</h4>';
        $htmlBody .= '<p>' . nl2br(htmlspecialchars($remarks . ' ' . $notes)) . '</p>';
    }
    $htmlBody .= '<p><a href="' . BASE_URL . 'admin/order-show.php?id=' . htmlspecialchars($paymentId) . '">View Full Booking in Admin Panel</a></p>';
    return sendAdminEmail('New Booking Request - ' . $siteName, $htmlBody);
}

function buildProductUrl($productId) {
    return BASE_URL . 'product.php?id=' . $productId;
}

if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ne', 'hi'], true)) {
    $_SESSION['lang'] = $_GET['lang'];
}

/**
 * Decode entity-encoded HTML once (when editor saved &lt;li&gt; instead of real tags).
 */
function decodeEditorHtml($html) {
    $html = (string) $html;
    if ($html === '') {
        return '';
    }
    if (strpos($html, '&lt;') !== false && preg_match('/<(p|ul|ol|li|div|br|strong|em|h[1-6])\b/i', $html) !== 1) {
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    return $html;
}

/**
 * Clean WYSIWYG HTML: strip paste junk (Cursor/selection anchors, data-section-id, etc.)
 * and keep only safe formatting tags.
 */
function cleanRichHtml($html) {
    $html = decodeEditorHtml($html);
    if (trim($html) === '') {
        return '';
    }

    // Remove Cursor / AI paste selection junk
    $html = preg_replace('/<span[^>]*class="[^"]*PDq2pG_selectionAnchor[^"]*"[^>]*>.*?<\/span>/is', '', $html);
    $html = preg_replace('/<span[^>]*aria-hidden="true"[^>]*>\s*<\/span>/is', '', $html);
    $html = preg_replace('/\s+data-(?:section-id|start|end|is-last-node|testid)="[^"]*"/i', '', $html);
    $html = preg_replace('/\s+(?:data-start|data-end|data-section-id|data-is-last-node)(?:=([\'"])[^\'"]*\1)?/i', '', $html);

    // Drop scripts/styles entirely
    $html = preg_replace('/<(script|style|iframe|object|embed|link|meta)[^>]*>.*?<\/\1>/is', '', $html);
    $html = preg_replace('/<(script|style|iframe|object|embed|link|meta)[^>]*\/?>/is', '', $html);

    $allowed = '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6><a><img><span><div><blockquote><table><thead><tbody><tr><th><td><hr>';
    $html = strip_tags($html, $allowed);

    if (class_exists('DOMDocument')) {
        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $wrapped = '<?xml encoding="UTF-8"><div id="rich-root">' . $html . '</div>';
        $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $xpath = new DOMXPath($dom);

        foreach ($xpath->query('//*') as $el) {
            if (!$el->hasAttributes()) {
                continue;
            }
            $remove = [];
            foreach ($el->attributes as $attr) {
                $name = strtolower($attr->nodeName);
                $keep = in_array($name, ['href', 'src', 'alt', 'title', 'target', 'rel', 'class'], true);
                if (!$keep || strpos($name, 'on') === 0 || strpos($name, 'data-') === 0) {
                    $remove[] = $attr->nodeName;
                }
            }
            foreach ($remove as $attrName) {
                $el->removeAttribute($attrName);
            }
        }

        foreach ($xpath->query('//a') as $a) {
            $href = $a->getAttribute('href');
            if ($href !== '' && !preg_match('#^(https?:|mailto:|tel:|/|#)#i', $href)) {
                $a->removeAttribute('href');
            }
            if ($a->hasAttribute('target')) {
                $a->setAttribute('rel', 'noopener noreferrer');
            }
        }

        $root = $dom->getElementById('rich-root');
        $clean = '';
        if ($root) {
            foreach ($root->childNodes as $child) {
                $clean .= $dom->saveHTML($child);
            }
        }
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $html = $clean !== '' ? $clean : $html;
    }

    // Tidy empty paragraphs / leftover anchors
    $html = preg_replace('/<p>(?:\s|&nbsp;)*<\/p>/i', '', $html);
    $html = preg_replace('/\s{2,}/', ' ', $html);

    return trim($html);
}

/** Safe HTML output for frontend pages. */
function renderRichHtml($html, $fallback = '') {
    $clean = cleanRichHtml($html);
    return $clean !== '' ? $clean : $fallback;
}

/** Plain text for cards / meta / excerpts. */
function plainTextFromHtml($html) {
    $text = decodeEditorHtml($html);
    $text = preg_replace('/<span[^>]*class="[^"]*PDq2pG_selectionAnchor[^"]*"[^>]*>.*?<\/span>/is', '', $text);
    $text = strip_tags($text);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', $text);
    return trim($text);
}

function excerpt($text, $limit = 120) {
    $text = plainTextFromHtml($text);
    if (mb_strlen($text) <= $limit) {
        return $text;
    }
    return mb_substr($text, 0, $limit) . '...';
}

function sortOptions($selected = '') {
    $options = [
        'newest' => t('sort_newest'),
        'featured' => t('sort_featured'),
        'popular' => t('sort_popular'),
        'alphabetical' => t('sort_alphabetical'),
    ];

    $html = '';
    foreach ($options as $value => $label) {
        $active = $selected === $value ? 'selected' : '';
        $html .= '<option value="' . $value . '" ' . $active . '>' . $label . '</option>';
    }
    return $html;
}

function ensureServiceLocationColumns(PDO $pdo) {
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    $targets = [
        'tbl_payment' => ['service_lat', 'service_lng'],
        'tbl_booking_assignment' => ['service_lat', 'service_lng'],
    ];
    foreach ($targets as $table => $columns) {
        foreach ($columns as $column) {
            try {
                $stmt = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE " . $pdo->quote($column));
                if ($stmt && $stmt->rowCount() === 0) {
                    $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` DECIMAL(10,7) NULL");
                }
            } catch (Throwable $e) {
                // Ignore if table is unavailable in older installs.
            }
        }
    }
}

function normalizeMapCoordinate($value, $min, $max) {
    if ($value === null || $value === '') {
        return null;
    }
    if (!is_numeric($value)) {
        return null;
    }
    $num = (float) $value;
    if ($num < $min || $num > $max) {
        return null;
    }
    return round($num, 7);
}

function mapsUrlForCoordinates($lat, $lng, $address = '') {
    if ($lat !== null && $lng !== null) {
        return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($lat . ',' . $lng);
    }
    return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode((string) $address);
}

function osmUrlForCoordinates($lat, $lng, $address = '') {
    if ($lat !== null && $lng !== null) {
        return 'https://www.openstreetmap.org/?mlat=' . rawurlencode((string) $lat) . '&mlon=' . rawurlencode((string) $lng) . '#map=16/' . rawurlencode((string) $lat) . '/' . rawurlencode((string) $lng);
    }
    return 'https://www.openstreetmap.org/search?query=' . rawurlencode((string) $address);
}

function directionsUrlForCoordinates($lat, $lng, $address = '') {
    $destination = ($lat !== null && $lng !== null) ? ($lat . ',' . $lng) : (string) $address;
    return 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($destination);
}

function renderServiceLocationPicker($options = []) {
    $addressInput = $options['address_input'] ?? '#service_address';
    $lat = $options['lat'] ?? '';
    $lng = $options['lng'] ?? '';
    $uid = $options['id'] ?? 'serviceLocationPicker';
    $required = !empty($options['required']);
    $requiredMessage = loadLang('map_pin_required');
    ob_start();
    ?>
    <div class="alert alert-info border-0 rounded-4 py-2 px-3 mb-2 small">
      <i class="fa fa-map-marker-alt me-1"></i> <?php echo t('map_pin_required_hint'); ?>
    </div>
    <div class="service-location-map-wrap" id="<?php echo e($uid); ?>" data-service-map="picker" data-address-input="<?php echo e($addressInput); ?>" <?php echo $required ? 'data-map-required="1"' : ''; ?> data-required-message="<?php echo e($requiredMessage); ?>">
      <div class="service-location-map-toolbar">
        <input type="search" class="form-control form-control-sm" data-map-search placeholder="<?php echo t('map_search_placeholder'); ?>" autocomplete="off">
        <button type="button" class="btn btn-sm btn-outline-dark" data-map-search-btn><?php echo t('map_search'); ?></button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-map-locate-btn><?php echo t('map_use_my_location'); ?></button>
      </div>
      <div class="service-location-map-canvas" data-map-canvas></div>
      <div class="service-location-map-meta" data-map-meta><?php echo t('map_pin_help'); ?></div>
      <div class="service-location-map-meta" data-map-status></div>
      <input type="hidden" name="service_lat" data-map-lat value="<?php echo e((string) $lat); ?>" <?php echo $required ? 'data-required="1"' : ''; ?>>
      <input type="hidden" name="service_lng" data-map-lng value="<?php echo e((string) $lng); ?>" <?php echo $required ? 'data-required="1"' : ''; ?>>
    </div>
    <?php
    return ob_get_clean();
}

function renderServiceLocationViewer($options = []) {
    $lat = $options['lat'] ?? null;
    $lng = $options['lng'] ?? null;
    $address = $options['address'] ?? '';
    $uid = $options['id'] ?? 'serviceLocationViewer';
    $wrapperClass = $options['class'] ?? '';
    $google = mapsUrlForCoordinates($lat, $lng, $address);
    $osm = osmUrlForCoordinates($lat, $lng, $address);
    $directions = directionsUrlForCoordinates($lat, $lng, $address);
    ob_start();
    ?>
    <div class="<?php echo e($wrapperClass); ?>">
      <div class="service-location-map-wrap" id="<?php echo e($uid); ?>" data-service-map="view" data-lat="<?php echo e((string) $lat); ?>" data-lng="<?php echo e((string) $lng); ?>" data-address="<?php echo e($address); ?>">
        <div class="service-location-map-canvas" data-map-canvas></div>
        <div class="service-location-map-meta" data-map-meta></div>
        <div class="service-location-map-actions">
          <a class="btn btn-success btn-sm" data-map-directions href="<?php echo e($directions); ?>" target="_blank" rel="noopener"><i class="fa fa-location-arrow"></i> <?php echo t('map_get_directions'); ?></a>
          <a class="btn btn-primary btn-sm" data-map-google href="<?php echo e($google); ?>" target="_blank" rel="noopener"><i class="fa fa-map-marker"></i> Google Maps</a>
          <a class="btn btn-default btn-sm" data-map-osm href="<?php echo e($osm); ?>" target="_blank" rel="noopener">OpenStreetMap</a>
        </div>
      </div>
    </div>
    <?php
    return ob_get_clean();
}

function serviceLocationAssets() {
    static $printed = false;
    if ($printed) {
        return '';
    }
    $printed = true;
    return '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">'
        . '<link rel="stylesheet" href="' . ASSET_URL . 'css/service-location-map.css?v=20260721b">'
        . '<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>'
        . '<script src="' . ASSET_URL . 'js/service-location-map.js?v=20260721b"></script>';
}
