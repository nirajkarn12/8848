<?php
/**
 * Shared About Us block (image + DB content).
 * Used on index.php and about.php.
 *
 * Optional vars:
 *   $aboutPage   - row from tbl_page (fetched if missing)
 *   $aboutCompact - if true, clamp content height and show link to about.php
 */
if (!isset($aboutPage) || !is_array($aboutPage)) {
    global $pdo;
    $aboutPage = $pdo->query('SELECT about_title, about_content, about_banner FROM tbl_page LIMIT 1')->fetch(PDO::FETCH_ASSOC) ?: [];
}

$aboutTitle = trim((string)($aboutPage['about_title'] ?? ''));
if ($aboutTitle === '') {
    $aboutTitle = loadLang('about_koshi_supplier');
}

$aboutContent = (string)($aboutPage['about_content'] ?? '');
$aboutBanner = trim((string)($aboutPage['about_banner'] ?? ''));
$aboutImageUrl = $aboutBanner !== '' ? getProductImage($aboutBanner) : ASSET_URL . 'images/cleaning-hero.jpg';
$aboutCompact = !empty($aboutCompact);
?>
<section class="section-block about-section reveal<?php echo $aboutCompact ? ' about-section--home' : ''; ?>">
  <div class="about-split">
    <figure class="about-media">
      <img src="<?php echo e($aboutImageUrl); ?>" alt="<?php echo e($aboutTitle); ?>" loading="lazy">
    </figure>
    <div class="about-copy">
      <div class="about-copy-inner">
        <div class="section-kicker"><?php echo t('our_story'); ?></div>
        <h2 class="section-title"><?php echo e($aboutTitle); ?></h2>
        <p class="section-subtitle"><?php echo t('crafted_with_care'); ?></p>
        <?php if ($aboutContent !== '') { ?>
        <div class="text-muted rich-content about-content<?php echo $aboutCompact ? ' about-content--clamp' : ''; ?>">
          <?php echo renderRichHtml($aboutContent); ?>
        </div>
        <?php } ?>
        <?php if ($aboutCompact) { ?>
        <div class="about-actions">
          <a href="<?php echo BASE_URL; ?>about.php" class="btn btn-dark"><?php echo t('read_more'); ?></a>
          <a href="<?php echo BASE_URL; ?>book-service.php" class="btn btn-outline-dark"><?php echo t('book_now'); ?></a>
        </div>
        <?php } ?>
      </div>
    </div>
  </div>
</section>
