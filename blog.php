<?php
require_once __DIR__ . '/inc/functions.php';
$postId = (int)($_GET['id'] ?? 0);
$post = null;
$siteName = (string) getSiteSetting('site_name', SITE_NAME);
$pageTitle = loadLang('blog');
$metaDescription = seoCleanText(loadLang('blog') . ' tips and updates from ' . $siteName . '. ' . loadLang('meta_home_description'), 160);
$metaKeywords = seoPick('blog, cleaning tips, ' . $siteName, getHomeSeo()['keywords']);
$ogImage = '';
$ogImageAlt = '';
$canonicalUrl = '';
$ogType = 'website';
$twitterCard = 'summary_large_image';
$publishedTime = '';
$modifiedTime = '';

if ($postId) {
    $stmt = $pdo->prepare('SELECT * FROM tbl_post WHERE post_id = ? LIMIT 1');
    $stmt->execute([$postId]);
    $post = $stmt->fetch();
    if (!$post) {
        header('Location: blog.php');
        exit;
    }
    $pageTitle = seoPick($post['meta_title'] ?? '', $post['post_title']);
    $metaDescription = seoPick($post['meta_description'] ?? '', $post['post_content'] ?? '', 160);
    $metaKeywords = seoPick($post['meta_keyword'] ?? '', 'blog, cleaning tips, ' . $siteName);
    $ogImage = $post['photo'] ?? '';
    $ogImageAlt = $post['post_title'];
    $canonicalUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $ogType = 'article';
    $published = DateTime::createFromFormat('d-m-Y', trim($post['post_date']));
    if ($published !== false) {
        $publishedTime = $published->format('c');
    }
    $modifiedTime = date('c');
}

include __DIR__ . '/inc/header.php';
$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('blog'), 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);

if ($postId) {
    $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $shareUrl = urlencode($currentUrl);
    $shareTitle = urlencode($post['post_title']);
    $facebookShare = "https://www.facebook.com/sharer/sharer.php?u={$shareUrl}";
    $twitterShare = "https://twitter.com/intent/tweet?url={$shareUrl}&text={$shareTitle}";
    $linkedinShare = "https://www.linkedin.com/sharing/share-offsite/?url={$shareUrl}";
    $whatsappShare = "https://api.whatsapp.com/send?text={$shareTitle}%20{$shareUrl}";
    $mailtoShare = "mailto:?subject={$shareTitle}&body={$shareUrl}";
    ?>
    <div class="card card-hover p-4">
      <?php if (!empty($post['photo'])) { ?>
      <img src="<?php echo getProductImage($post['photo']); ?>" alt="<?php echo e($post['post_title']); ?>" class="img-fluid rounded mb-4" style="max-height:420px; object-fit:cover; width:100%;">
      <?php } ?>
      <h2 class="fw-bold mb-3"><?php echo e($post['post_title']); ?></h2>
      <div class="share-buttons d-flex flex-wrap align-items-center gap-2 mb-4">
        <span class="text-muted small fw-semibold"><?php echo t('share'); ?>:</span>
        <a href="<?php echo $facebookShare; ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary btn-sm">
          <i class="fab fa-facebook-f"></i> Facebook
        </a>
        <a href="<?php echo $twitterShare; ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary btn-sm">
          <i class="fab fa-twitter"></i> Twitter
        </a>
        <a href="<?php echo $linkedinShare; ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary btn-sm">
          <i class="fab fa-linkedin-in"></i> LinkedIn
        </a>
        <a href="<?php echo $whatsappShare; ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary btn-sm">
          <i class="fab fa-whatsapp"></i> WhatsApp
        </a>
        <a href="#" onclick="navigator.share ? navigator.share({ title: '<?php echo addslashes($post['post_title']); ?>', text: '<?php echo addslashes($post['post_title']); ?>', url: '<?php echo addslashes($currentUrl); ?>' }).catch(()=>{}) : window.location.href = '<?php echo $mailtoShare; ?>'; return false;" class="btn btn-outline-secondary btn-sm">
          <i class="fa fa-share-alt"></i> <?php echo t('more'); ?>
        </a>
      </div>
      <div class="text-muted rich-content"><?php echo renderRichHtml($post['post_content']); ?></div>
    </div>
    <?php
} else {
    $posts = $pdo->query('SELECT * FROM tbl_post ORDER BY post_id DESC')->fetchAll();
    ?>
    <div class="row g-4">
      <?php foreach ($posts as $post) { 
            $postUrl = BASE_URL . 'blog.php?id=' . (int)$post['post_id'];
            $shareUrl = urlencode($postUrl);
            $shareTitle = urlencode($post['post_title']);
            $facebookShare = "https://www.facebook.com/sharer/sharer.php?u={$shareUrl}";
            $instagramShareJs = "if (navigator.share) { navigator.share({ title: '{$post['post_title']}', url: '{$postUrl}' }); } else { window.location.href='mailto:?subject={$shareTitle}&body={$shareUrl}'; } return false;";
      ?>
        <div class="col-md-6 col-lg-4">
          <div class="card card-hover">
            <img src="<?php echo getProductImage($post['photo']); ?>" alt="" class="img-fluid" style="height:220px; object-fit:cover; width:100%;">
            <div class="card-body">
              <h5 class="fw-bold"><?php echo e($post['post_title']); ?></h5>
              <p class="text-muted small"><?php echo excerpt(strip_tags($post['post_content']), 140); ?></p>
              <div class="d-flex flex-wrap gap-2 align-items-center">
                <a href="blog.php?id=<?php echo (int)$post['post_id']; ?>" class="btn btn-dark btn-sm"><?php echo t('read_more'); ?></a>
                <a href="<?php echo $facebookShare; ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary btn-icon" title="<?php echo t('share_on_facebook'); ?>">
                  <i class="fab fa-facebook-f"></i>
                </a>
                <a href="#" onclick="<?php echo $instagramShareJs; ?>" class="btn btn-outline-secondary btn-icon" title="<?php echo t('share_on_instagram'); ?>">
                  <i class="fab fa-instagram"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php } ?>
    </div>
    <?php
}
include __DIR__ . '/inc/footer.php';
