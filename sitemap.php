<?php
/**
 * Dynamic XML Sitemap
 *
 * URL:
 * https://8848cleaningservice.com/sitemap.php
 *
 * This file generates an XML sitemap dynamically from:
 * - Static public pages
 * - Categories
 * - Active products
 * - Blog posts
 */

require_once __DIR__ . '/inc/functions.php';

/*
|--------------------------------------------------------------------------
| Sitemap configuration
|--------------------------------------------------------------------------
*/

$base = rtrim((string) BASE_URL, '/');

/*
|--------------------------------------------------------------------------
| Store sitemap URLs
|--------------------------------------------------------------------------
*/

$urls = [];

/*
|--------------------------------------------------------------------------
| Add URL helper
|--------------------------------------------------------------------------
*/

$add = static function (
    array &$urls,
    string $loc,
    ?string $lastmod = null,
    ?string $changefreq = null,
    ?string $priority = null
): void {
    $loc = trim($loc);

    if ($loc === '') {
        return;
    }

    $urls[] = [
        'loc'        => $loc,
        'lastmod'    => $lastmod,
        'changefreq' => $changefreq,
        'priority'   => $priority,
    ];
};

/*
|--------------------------------------------------------------------------
| Static pages
|--------------------------------------------------------------------------
|
| Do not use today's date as lastmod unless the page was actually changed.
| lastmod is optional, so we can safely omit it.
|
*/

$add($urls, $base . '/', null, 'weekly', '1.0');

$add($urls, $base . '/about.php', null, 'monthly', '0.8');

$add($urls, $base . '/contact.php', null, 'monthly', '0.8');

$add($urls, $base . '/products.php', null, 'weekly', '0.9');

$add($urls, $base . '/book-service.php', null, 'weekly', '0.9');

$add($urls, $base . '/gallery.php', null, 'weekly', '0.7');

$add($urls, $base . '/blog.php', null, 'weekly', '0.8');

$add($urls, $base . '/reviews.php', null, 'weekly', '0.6');

/*
|--------------------------------------------------------------------------
| Categories
|--------------------------------------------------------------------------
*/

try {
    $categories = $pdo->query(
        'SELECT mcat_id
         FROM tbl_mid_category
         ORDER BY mcat_id ASC'
    )->fetchAll(PDO::FETCH_ASSOC);

    foreach ($categories as $category) {

        $categoryId = (int) ($category['mcat_id'] ?? 0);

        if ($categoryId <= 0) {
            continue;
        }

        $url = $base . '/category.php?id=' . $categoryId;

        $add(
            $urls,
            $url,
            null,
            'weekly',
            '0.7'
        );
    }

} catch (Throwable $e) {
    /*
     * If the category table is unavailable,
     * continue generating the rest of the sitemap.
     */
}

/*
|--------------------------------------------------------------------------
| Active products
|--------------------------------------------------------------------------
*/

try {
    $products = $pdo->query(
        'SELECT p_id
         FROM tbl_product
         WHERE p_is_active = 1
         ORDER BY p_id DESC'
    )->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $product) {

        $productId = (int) ($product['p_id'] ?? 0);

        if ($productId <= 0) {
            continue;
        }

        $url = $base . '/product.php?id=' . $productId;

        $add(
            $urls,
            $url,
            null,
            'weekly',
            '0.8'
        );
    }

} catch (Throwable $e) {
    /*
     * Continue even if the product query fails.
     */
}

/*
|--------------------------------------------------------------------------
| Blog posts
|--------------------------------------------------------------------------
*/

try {
    $posts = $pdo->query(
        'SELECT post_id, post_date
         FROM tbl_post
         ORDER BY post_id DESC'
    )->fetchAll(PDO::FETCH_ASSOC);

    foreach ($posts as $post) {

        $postId = (int) ($post['post_id'] ?? 0);

        if ($postId <= 0) {
            continue;
        }

        /*
         * Try to convert the database date into YYYY-MM-DD.
         */
        $lastmod = null;

        $rawDate = trim(
            (string) ($post['post_date'] ?? '')
        );

        if ($rawDate !== '') {

            $parsed = DateTime::createFromFormat(
                'd-m-Y',
                $rawDate
            );

            if (!$parsed) {
                $parsed = DateTime::createFromFormat(
                    'Y-m-d',
                    $rawDate
                );
            }

            if (!$parsed) {
                try {
                    $parsed = new DateTime($rawDate);
                } catch (Throwable $e) {
                    $parsed = null;
                }
            }

            if ($parsed instanceof DateTime) {
                $lastmod = $parsed->format('Y-m-d');
            }
        }

        $url = $base . '/blog.php?id=' . $postId;

        $add(
            $urls,
            $url,
            $lastmod,
            'monthly',
            '0.6'
        );
    }

} catch (Throwable $e) {
    /*
     * Continue even if the blog query fails.
     */
}

/*
|--------------------------------------------------------------------------
| Remove duplicate URLs
|--------------------------------------------------------------------------
*/

$uniqueUrls = [];

foreach ($urls as $entry) {

    $uniqueUrls[$entry['loc']] = $entry;
}

$urls = array_values($uniqueUrls);

/*
|--------------------------------------------------------------------------
| XML response headers
|--------------------------------------------------------------------------
*/

header(
    'Content-Type: application/xml; charset=UTF-8'
);

header(
    'Cache-Control: public, max-age=3600'
);

/*
|--------------------------------------------------------------------------
| XML declaration
|--------------------------------------------------------------------------
*/

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

<?php foreach ($urls as $entry): ?>

    <url>

        <loc>
            <?php
            echo htmlspecialchars(
                $entry['loc'],
                ENT_XML1 | ENT_QUOTES,
                'UTF-8'
            );
            ?>
        </loc>

        <?php if (!empty($entry['lastmod'])): ?>
            <lastmod>
                <?php
                echo htmlspecialchars(
                    $entry['lastmod'],
                    ENT_XML1 | ENT_QUOTES,
                    'UTF-8'
                );
                ?>
            </lastmod>
        <?php endif; ?>

        <?php if (!empty($entry['changefreq'])): ?>
            <changefreq>
                <?php
                echo htmlspecialchars(
                    $entry['changefreq'],
                    ENT_XML1 | ENT_QUOTES,
                    'UTF-8'
                );
                ?>
            </changefreq>
        <?php endif; ?>

        <?php if (!empty($entry['priority'])): ?>
            <priority>
                <?php
                echo htmlspecialchars(
                    $entry['priority'],
                    ENT_XML1 | ENT_QUOTES,
                    'UTF-8'
                );
                ?>
            </priority>
        <?php endif; ?>

    </url>

<?php endforeach; ?>

</urlset>