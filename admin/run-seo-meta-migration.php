<?php
require_once('inc/config.php');

$messages = array();
$errors = array();

$defaultHome = array(
    'meta_title_home' => '8848 Cleaning Service | Home & Office Cleaning in Kathmandu',
    'meta_keyword_home' => '8848 cleaning service, home cleaning Kathmandu, office cleaning, deep cleaning Nepal, book cleaner online',
    'meta_description_home' => 'Professional home and office cleaning in Kathmandu. Book trusted cleaners online with 8848 Cleaning Service — flexible scheduling and reliable results.',
);

$defaultPage = array(
    'about_meta_title' => 'About 8848 Cleaning Service',
    'about_meta_keyword' => 'about 8848 cleaning service, cleaning company Kathmandu, professional cleaners Nepal',
    'about_meta_description' => 'Learn about 8848 Cleaning Service — professional home and office cleaning in Kathmandu with trained staff and easy online booking.',
    'contact_meta_title' => 'Contact 8848 Cleaning Service',
    'contact_meta_keyword' => 'contact 8848 cleaning, cleaning service phone Kathmandu, book cleaning Nepal',
    'contact_meta_description' => 'Contact 8848 Cleaning Service for home and office cleaning in Kathmandu. Call, email, or send a message to book your service.',
    'faq_meta_title' => 'FAQ | 8848 Cleaning Service',
    'faq_meta_keyword' => 'cleaning FAQ, booking questions, 8848 cleaning service help',
    'faq_meta_description' => 'Frequently asked questions about booking, pricing, and cleaning services with 8848 Cleaning Service.',
);

function looksStaleSeo($value) {
    $value = strtolower((string) $value);
    if (trim($value) === '') {
        return true;
    }
    $needles = array('ecommerce', 'garments', 'fashion store', 'sastika', "raise'n", 'resin', 'candle', 'koshi supplier');
    foreach ($needles as $needle) {
        if (strpos($value, $needle) !== false) {
            return true;
        }
    }
    return false;
}

try {
    $row = $pdo->query('SELECT meta_title_home, meta_keyword_home, meta_description_home FROM tbl_settings WHERE id=1')->fetch(PDO::FETCH_ASSOC);
    $updates = array();
    $params = array();
    foreach ($defaultHome as $col => $val) {
        if (looksStaleSeo($row[$col] ?? '')) {
            $updates[] = "$col = ?";
            $params[] = $val;
        }
    }
    if ($updates) {
        $pdo->prepare('UPDATE tbl_settings SET ' . implode(', ', $updates) . ' WHERE id=1')->execute($params);
        $messages[] = 'Updated home SEO fields: ' . implode(', ', array_keys(array_intersect_key($defaultHome, array_flip(array_map(function ($u) {
            return explode(' ', $u)[0];
        }, $updates)))));
    } else {
        $messages[] = 'Home SEO already looks up to date';
    }

    $page = $pdo->query('SELECT about_meta_title, about_meta_keyword, about_meta_description, contact_meta_title, contact_meta_keyword, contact_meta_description, faq_meta_title, faq_meta_keyword, faq_meta_description FROM tbl_page WHERE id=1')->fetch(PDO::FETCH_ASSOC);
    $updates = array();
    $params = array();
    foreach ($defaultPage as $col => $val) {
        if (looksStaleSeo($page[$col] ?? '')) {
            $updates[] = "$col = ?";
            $params[] = $val;
        }
    }
    if ($updates) {
        $pdo->prepare('UPDATE tbl_page SET ' . implode(', ', $updates) . ' WHERE id=1')->execute($params);
        $messages[] = 'Updated page SEO fields (' . count($updates) . ')';
    } else {
        $messages[] = 'Page SEO already looks up to date';
    }
} catch (Throwable $e) {
    $errors[] = $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>SEO Meta Migration</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body class="container" style="padding:40px;">
    <h2>SEO Meta Migration</h2>
    <?php foreach ($messages as $m) { ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($m); ?></div>
    <?php } ?>
    <?php foreach ($errors as $e) { ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div>
    <?php } ?>
    <p><a class="btn btn-primary" href="settings.php">Go to Settings</a>
       <a class="btn btn-default" href="page.php">Go to Page Settings</a></p>
</body>
</html>
