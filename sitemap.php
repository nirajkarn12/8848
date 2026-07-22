<?php
/**
 * Dynamic XML sitemap for Google / Bing indexing.
 * Served as /sitemap.xml via .htaccess rewrite.
 * Uses BASE_URL from config/database.php (deployment already defines this).
 */
require_once __DIR__ . '/inc/functions.php';

$base = rtrim((string) BASE_URL, '/') . '/';
$today = date('Y-m-d');

$urls = [];

$add = static function (array &$urls, string $loc, string $lastmod, string $changefreq, string $priority): void {
    $loc = trim($loc);
    if ($loc === '') {
        return;
    }
    $urls[] = [
        'loc' => $loc,
        'lastmod' => $lastmod,
        'changefreq' => $changefreq,
        'priority' => $priority,
    ];
};

// Core public pages
$add($urls, $base, $today, 'daily', '1.0');
$add($urls, $base . 'about.php', $today, 'monthly', '0.8');
$add($urls, $base . 'contact.php', $today, 'monthly', '0.8');
$add($urls, $base . 'products.php', $today, 'weekly', '0.9');
$add($urls, $base . 'book-service.php', $today, 'weekly', '0.9');
$add($urls, $base . 'gallery.php', $today, 'weekly', '0.7');
$add($urls, $base . 'blog.php', $today, 'weekly', '0.8');
$add($urls, $base . 'reviews.php', $today, 'weekly', '0.6');

try {
    $categories = $pdo->query('SELECT mcat_id FROM tbl_mid_category ORDER BY mcat_id ASC')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($categories as $cat) {
        $add($urls, $base . 'category.php?id=' . (int) $cat['mcat_id'], $today, 'weekly', '0.7');
    }
} catch (Throwable $e) {
    // ignore missing table
}

try {
    $products = $pdo->query(
        'SELECT p_id FROM tbl_product WHERE p_is_active = 1 ORDER BY p_id DESC'
    )->fetchAll(PDO::FETCH_ASSOC);
    foreach ($products as $product) {
        $add($urls, $base . 'product.php?id=' . (int) $product['p_id'], $today, 'weekly', '0.8');
    }
} catch (Throwable $e) {
    // ignore
}

try {
    $posts = $pdo->query(
        'SELECT post_id, post_date FROM tbl_post ORDER BY post_id DESC'
    )->fetchAll(PDO::FETCH_ASSOC);
    foreach ($posts as $post) {
        $lastmod = $today;
        $rawDate = trim((string) ($post['post_date'] ?? ''));
        if ($rawDate !== '') {
            $parsed = DateTime::createFromFormat('d-m-Y', $rawDate)
                ?: DateTime::createFromFormat('Y-m-d', $rawDate)
                ?: date_create($rawDate);
            if ($parsed instanceof DateTime) {
                $lastmod = $parsed->format('Y-m-d');
            }
        }
        $add($urls, $base . 'blog.php?id=' . (int) $post['post_id'], $lastmod, 'monthly', '0.6');
    }
} catch (Throwable $e) {
    // ignore
}

header('Content-Type: application/xml; charset=UTF-8');
header('X-Robots-Tag: noindex');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $entry): ?>
  <url>
    <loc><?php echo htmlspecialchars($entry['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8'); ?></loc>
    <lastmod><?php echo htmlspecialchars($entry['lastmod'], ENT_XML1 | ENT_QUOTES, 'UTF-8'); ?></lastmod>
    <changefreq><?php echo htmlspecialchars($entry['changefreq'], ENT_XML1 | ENT_QUOTES, 'UTF-8'); ?></changefreq>
    <priority><?php echo htmlspecialchars($entry['priority'], ENT_XML1 | ENT_QUOTES, 'UTF-8'); ?></priority>
  </url>
<?php endforeach; ?>
</urlset>
