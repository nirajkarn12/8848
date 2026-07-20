<?php
/**
 * Site notice marquee — place directly below hero (inside .hero-notice-stack).
 *
 * Expects optional $marqueeNotices (array of strings). Falls back to getMarqueeNotices().
 */
if (!isset($marqueeNotices) || !is_array($marqueeNotices)) {
    $marqueeNotices = function_exists('getMarqueeNotices') ? getMarqueeNotices() : array();
}

if (empty($marqueeNotices)) {
    return;
}

$marqueeLoop = array_merge($marqueeNotices, $marqueeNotices);
?>
<div class="site-ribbon-marquee site-ribbon-marquee--hero" role="region" aria-label="<?php echo t('site_notices'); ?>">
  <div class="site-ribbon-marquee-label" aria-hidden="true">
    <i class="fa fa-bullhorn"></i>
    <span><?php echo t('notice'); ?></span>
  </div>
  <div class="site-ribbon-marquee-viewport">
    <div class="site-ribbon-marquee-track">
      <?php foreach ($marqueeLoop as $notice) { ?>
        <span><i class="fa fa-circle-dot"></i> <?php echo e($notice); ?></span>
      <?php } ?>
    </div>
  </div>
</div>
