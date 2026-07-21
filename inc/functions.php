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

    if ($settings === null) {
        $settings = $pdo->query('SELECT * FROM tbl_settings LIMIT 1')->fetch(PDO::FETCH_ASSOC);
        if (!$settings) {
            $settings = [];
        }
    }

    $fieldName = preg_replace('/[^a-zA-Z0-9_]/', '', $field);
    return array_key_exists($fieldName, $settings) && $settings[$fieldName] !== null ? $settings[$fieldName] : $default;
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
        'title' => seoPick(getSiteSetting('meta_title_home', ''), $siteName),
        'keywords' => seoPick(
            getSiteSetting('meta_keyword_home', ''),
            'cleaning service, home cleaning, office cleaning, deep clean, Kathmandu, 8848 Cleaning Service'
        ),
        'description' => seoPick(
            getSiteSetting('meta_description_home', ''),
            loadLang('meta_home_description'),
            160
        ),
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
        'about' => loadLang('about'),
        'contact' => loadLang('contact'),
        'faq' => loadLang('faqs'),
    ];

    return [
        'title' => seoPick($pageRow[$prefix . '_meta_title'] ?? '', $defaultTitles[$prefix] ?? $home['title']),
        'keywords' => seoPick($pageRow[$prefix . '_meta_keyword'] ?? '', $home['keywords']),
        'description' => seoPick(
            $pageRow[$prefix . '_meta_description'] ?? '',
            seoPick($pageRow[$prefix . '_content'] ?? '', $home['description'], 160),
            160
        ),
    ];
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
        return ASSET_URL . 'images/placeholder.png';
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
            $relative = ltrim(str_replace(__DIR__ . '/../', '', $path), '/');
            return BASE_URL . $relative;
        }
    }

    return ASSET_URL . 'images/placeholder.png';
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

function sendCustomerEmail($toEmail, $toName, $subject, $htmlBody) {
    $toEmail = trim((string) $toEmail);
    if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $phpMailerPath = __DIR__ . '/../PHPMailer/src/PHPMailer.php';
    if (!is_file($phpMailerPath)) {
        $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\nFrom: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
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
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addReplyTo(SMTP_REPLYTO_EMAIL, SMTP_REPLYTO_NAME);
        $mail->addAddress($toEmail, $toName ?: $toEmail);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));
        $mail->send();
        return true;
    } catch (Throwable $e) {
        return false;
    }
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
