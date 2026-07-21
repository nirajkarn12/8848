<?php
require_once __DIR__ . '/inc/functions.php';
$items = [];
$categories = [];
try {
    $items = $pdo->query("
        SELECT g.*, m.mcat_name, t.tcat_name
        FROM tbl_gallery g
        LEFT JOIN tbl_mid_category m ON m.mcat_id = g.mcat_id
        LEFT JOIN tbl_top_category t ON t.tcat_id = m.tcat_id
        WHERE g.status = 'Active'
        ORDER BY g.sort_order ASC, g.id DESC
    ")->fetchAll();

    $catMap = [];
    foreach ($items as $item) {
        $cid = (int)($item['mcat_id'] ?? 0);
        if ($cid <= 0 || empty($item['mcat_name'])) {
            continue;
        }
        if (!isset($catMap[$cid])) {
            $catMap[$cid] = [
                'mcat_id' => $cid,
                'mcat_name' => $item['mcat_name'],
                'tcat_name' => $item['tcat_name'] ?? '',
                'count' => 0,
            ];
        }
        $catMap[$cid]['count']++;
    }
    $categories = array_values($catMap);
} catch (Throwable $e) {
    $items = [];
    $categories = [];
}

$siteName = (string) getSiteSetting('site_name', SITE_NAME);
$catNames = array_column($categories, 'mcat_name');
$pageTitle = loadLang('gallery');
$metaDescription = seoCleanText(
    loadLang('gallery_subtitle') . (count($items) ? ' (' . count($items) . ' ' . loadLang('gallery_photos') . ')' : '') .
    ($catNames ? '. ' . implode(', ', array_slice($catNames, 0, 8)) : ''),
    160
);
$metaKeywords = seoPick(
    implode(', ', array_merge(['gallery', 'cleaning photos', $siteName], array_slice($catNames, 0, 10))),
    getHomeSeo()['keywords']
);
if (!empty($items[0]['photo'])) {
    $ogImage = $items[0]['photo'];
}

include __DIR__ . '/inc/header.php';
$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('gallery'), 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);
?>
<div class="section-head mb-4 gallery-page-head">
  <div>
    <div class="section-kicker"><?php echo t('gallery'); ?></div>
    <h1 class="section-title"><?php echo t('gallery_title'); ?></h1>
    <p class="section-subtitle mb-0"><?php echo t('gallery_subtitle'); ?></p>
  </div>
  <?php if ($items) { ?>
  <div class="gallery-count-pill">
    <span data-gallery-count><?php echo count($items); ?></span>
    <small><?php echo t('gallery_photos'); ?></small>
  </div>
  <?php } ?>
</div>

<?php if (!$items) { ?>
  <div class="alert alert-light rounded-4"><?php echo t('no_gallery_yet'); ?></div>
<?php } else { ?>
  <div class="gallery-filter-bar mb-4" id="galleryFilterBar">
    <button type="button" class="gallery-filter-btn is-active" data-filter="all">
      <?php echo t('gallery_all'); ?>
      <span class="gallery-filter-count"><?php echo count($items); ?></span>
    </button>
    <?php foreach ($categories as $cat) { ?>
      <button type="button" class="gallery-filter-btn" data-filter="<?php echo (int)$cat['mcat_id']; ?>">
        <?php echo e($cat['mcat_name']); ?>
        <span class="gallery-filter-count"><?php echo (int)$cat['count']; ?></span>
      </button>
    <?php } ?>
    <?php
    $uncategorized = 0;
    foreach ($items as $item) {
        if ((int)($item['mcat_id'] ?? 0) <= 0 || empty($item['mcat_name'])) {
            $uncategorized++;
        }
    }
    if ($uncategorized > 0) {
    ?>
      <button type="button" class="gallery-filter-btn" data-filter="0">
        <?php echo t('gallery_uncategorized'); ?>
        <span class="gallery-filter-count"><?php echo $uncategorized; ?></span>
      </button>
    <?php } ?>
  </div>

  <div class="gallery-mosaic" id="galleryMosaic">
    <?php foreach ($items as $index => $item) {
      $img = getProductImage($item['photo']);
      $caption = trim((string)$item['content']);
      $title = trim((string)$item['title']);
      $cid = (int)($item['mcat_id'] ?? 0);
      if ($cid > 0 && empty($item['mcat_name'])) {
          $cid = 0;
      }
      $catName = !empty($item['mcat_name']) ? $item['mcat_name'] : t('gallery_uncategorized');
      $fancyGroup = 'gallery-cat-' . $cid;
    ?>
      <a
        href="<?php echo e($img); ?>"
        class="gallery-tile gallery-animate"
        data-fancybox="<?php echo e($fancyGroup); ?>"
        data-caption="<?php echo e($title . ($caption !== '' ? ' — ' . $caption : '')); ?>"
        data-category="<?php echo (int)$cid; ?>"
        data-index="<?php echo (int)$index; ?>"
        style="--gallery-delay: <?php echo min($index, 12) * 55; ?>ms;"
      >
        <span class="gallery-tile-media">
          <img src="<?php echo e($img); ?>" alt="<?php echo e($title); ?>" loading="lazy">
        </span>
        <span class="gallery-tile-overlay">
          <span class="gallery-tile-zoom"><i class="fa fa-search-plus"></i></span>
          <span class="gallery-tile-meta">
            <em class="gallery-tile-cat"><?php echo e($catName); ?></em>
            <strong><?php echo e($title); ?></strong>
            <?php if ($caption !== '') { ?>
              <small><?php echo e(mb_strimwidth($caption, 0, 90, '…')); ?></small>
            <?php } ?>
          </span>
        </span>
      </a>
    <?php } ?>
  </div>
  <div class="alert alert-light rounded-4 mt-3 d-none" id="galleryEmptyFilter"><?php echo t('gallery_no_in_category'); ?></div>
<?php } ?>

<?php include __DIR__ . '/inc/footer.php'; ?>
