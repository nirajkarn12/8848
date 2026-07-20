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
    if (password_get_info($stored)['algo'] ?? null) {
        return password_verify($input, $stored);
    }

    if (strlen($stored) === 32) {
        return md5($input) === $stored;
    }

    return $input === $stored;
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
