<?php
require_once __DIR__ . '/inc/functions.php';
$pageTitle = loadLang('home');
$metaDescription = loadLang('meta_home_description');
$fullWidth = true;
$showWaterSplash = true;
include __DIR__ . '/inc/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['newsletter_email'])) {
    $email = trim($_POST['newsletter_email'] ?? '');
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $pdo->prepare('SELECT subs_id FROM tbl_subscriber WHERE subs_email = ? LIMIT 1');
        $stmt->execute([$email]);
        if (!$stmt->fetch()) {
            $now = date('Y-m-d');
            $stmt = $pdo->prepare('INSERT INTO tbl_subscriber (subs_email, subs_date, subs_date_time, subs_hash, subs_active) VALUES (?, ?, ?, ?, 1)');
            $stmt->execute([$email, $now, date('Y-m-d H:i:s'), bin2hex(random_bytes(8))]);
        }
        $newsletterMessage = t('newsletter_success');
        $newsletterType = 'success';
    } else {
        $newsletterMessage = t('newsletter_invalid_email');
        $newsletterType = 'danger';
    }
}

$featured = $pdo->query('SELECT p.p_id, p.p_name, p.p_short_description, p.p_is_featured, p.p_qty, p.ecat_id, p.p_featured_photo FROM tbl_product p WHERE p.p_is_active = 1 ORDER BY p.p_is_featured DESC, p.p_id DESC LIMIT 8')->fetchAll();
$popular = $pdo->query('SELECT p.p_id, p.p_name, p.p_short_description, p.p_qty, p.ecat_id, p.p_featured_photo FROM tbl_product p WHERE p.p_is_active = 1 ORDER BY p.p_total_view DESC, p.p_id DESC LIMIT 8')->fetchAll();
$topCategories = getTopCategories();
$posts = $pdo->query('SELECT post_id, post_title, post_content, photo FROM tbl_post ORDER BY post_id DESC LIMIT 3')->fetchAll();
$faqs = $pdo->query('SELECT faq_id, faq_title, faq_content FROM tbl_faq ORDER BY faq_id ASC LIMIT 5')->fetchAll();
$settings = $pdo->query('SELECT * FROM tbl_settings LIMIT 1')->fetch();
$heroSlides = $pdo->query('SELECT * FROM tbl_slider ORDER BY id ASC')->fetchAll();
$newsletterEnabled = (int)getSiteSetting('newsletter_on_off', 1);
$newsletterText = getSiteSetting('newsletter_text', t('newsletter_default_text'));
$brandName = e(getSiteSetting('site_name', SITE_NAME));
$heroFallback = ASSET_URL . 'images/cleaning-hero.jpg';
$phone = e(getSiteSetting('contact_phone', '+977 9869224134'));
$homeReviews = [];
try {
    $homeReviews = $pdo->query("SELECT * FROM tbl_testimonial WHERE status = 'Active' ORDER BY sort_order ASC, id DESC LIMIT 3")->fetchAll();
} catch (Throwable $e) {
    $homeReviews = [];
}
$homeClients = [];
try {
    $homeClients = $pdo->query("SELECT * FROM tbl_client WHERE status = 'Active' ORDER BY sort_order ASC, id DESC")->fetchAll();
} catch (Throwable $e) {
    $homeClients = [];
}
$teamStaff = [];
try {
    $teamStaff = $pdo->query("
        SELECT staff_id, full_name, phone, photo, designation, rating, facebook_url, instagram_url
        FROM tbl_staff
        WHERE status = 'Active' AND show_on_website = 1
        ORDER BY staff_id ASC
    ")->fetchAll();
} catch (Throwable $e) {
    try {
        $teamStaff = $pdo->query("SELECT staff_id, full_name, phone, photo FROM tbl_staff WHERE status = 'Active' ORDER BY staff_id ASC")->fetchAll();
    } catch (Throwable $e2) {
        $teamStaff = [];
    }
}
?>
<div class="hero-notice-stack">
<section class="hero-banner">
  <div class="swiper heroSwiper">
    <div class="swiper-wrapper">
      <?php if ($heroSlides) { foreach ($heroSlides as $i => $slide) {
        $slideImg = !empty($slide['photo']) ? getProductImage($slide['photo']) : $heroFallback;
        $heading = !empty($slide['heading']) ? $slide['heading'] : t('hero_default_title');
        $content = !empty($slide['content']) ? $slide['content'] : t('hero_default_text');
        $btnText = !empty($slide['button_text']) ? $slide['button_text'] : t('book_now');
        $btnUrl = !empty($slide['button_url']) ? $slide['button_url'] : 'book-service.php';
      ?>
      <div class="swiper-slide">
        <div class="hero-slide">
          <img class="hero-slide-bg" src="<?php echo e($slideImg); ?>" alt="<?php echo e($heading); ?>" <?php echo $i === 0 ? 'loading="eager"' : 'loading="lazy"'; ?>>
          <div class="hero-slide-shade"></div>
          <div class="container hero-slide-content">
            <div class="hero-copy">
              <div class="agency-brand"><?php echo $brandName; ?></div>
              <h1><?php echo e($heading); ?></h1>
              <p><?php echo e($content); ?></p>
              <div class="hero-actions">
                <a href="<?php echo e($btnUrl); ?>" class="btn btn-light btn-lg"><?php echo e($btnText); ?></a>
                <a href="contact.php" class="btn btn-outline-light btn-lg"><?php echo t('contact_us_btn'); ?></a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php } } else { ?>
      <div class="swiper-slide">
        <div class="hero-slide">
          <img class="hero-slide-bg" src="<?php echo e($heroFallback); ?>" alt="<?php echo $brandName; ?>" loading="eager">
          <div class="hero-slide-shade"></div>
          <div class="container hero-slide-content">
            <div class="hero-copy">
              <div class="agency-brand"><?php echo $brandName; ?></div>
              <h1><?php echo t('hero_default_title'); ?></h1>
              <p><?php echo t('hero_default_text'); ?></p>
              <div class="hero-actions">
                <a href="book-service.php" class="btn btn-light btn-lg"><?php echo t('book_now'); ?></a>
                <a href="contact.php" class="btn btn-outline-light btn-lg"><?php echo t('contact_us_btn'); ?></a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php } ?>
    </div>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-prev hero-nav"></div>
    <div class="swiper-button-next hero-nav"></div>
  </div>
</section>
<?php include __DIR__ . '/inc/partials/marquee-ribbon.php'; ?>
</div>

<section class="trust-strip">
  <div class="container">
    <div class="trust-grid">
      <div class="trust-item"><i class="fa fa-shield-halved"></i><span><?php echo t('trust_vetted'); ?></span></div>
      <div class="trust-item"><i class="fa fa-clock"></i><span><?php echo t('trust_ontime'); ?></span></div>
      <div class="trust-item"><i class="fa fa-spray-can-sparkles"></i><span><?php echo t('trust_home_office'); ?></span></div>
      <div class="trust-item"><i class="fa fa-phone"></i><a href="tel:<?php echo preg_replace('/\s+/', '', $phone); ?>"><?php echo $phone; ?></a></div>
    </div>
  </div>
</section>

<div class="container page-wrap py-5">

<section class="section-block reveal">
  <div class="section-head">
    <div>
      <div class="section-kicker"><?php echo t('how_it_works'); ?></div>
      <h2 class="section-title"><?php echo t('how_it_works'); ?></h2>
      <p class="section-subtitle"><?php echo t('shop_collection_subtitle'); ?></p>
    </div>
  </div>
  <div class="how-grid">
    <article class="how-card">
      <div class="how-step-num">01</div>
      <h3><?php echo t('how_step_1_title'); ?></h3>
      <p><?php echo t('how_step_1_text'); ?></p>
    </article>
    <article class="how-card">
      <div class="how-step-num">02</div>
      <h3><?php echo t('how_step_2_title'); ?></h3>
      <p><?php echo t('how_step_2_text'); ?></p>
    </article>
    <article class="how-card">
      <div class="how-step-num">03</div>
      <h3><?php echo t('how_step_3_title'); ?></h3>
      <p><?php echo t('how_step_3_text'); ?></p>
    </article>
  </div>
</section>

<?php
$aboutPage = $pdo->query('SELECT about_title, about_content, about_banner FROM tbl_page LIMIT 1')->fetch(PDO::FETCH_ASSOC) ?: [];
$aboutCompact = true;
include __DIR__ . '/inc/partials/about-section.php';
?>

</div>

<section class="site-ribbon site-ribbon-a reveal">
  <div class="site-ribbon-inner">
    <div class="site-ribbon-copy">
      <div class="site-ribbon-kicker"><?php echo t('book_now'); ?></div>
      <h2 class="site-ribbon-title"><?php echo t('ribbon_1_title'); ?></h2>
      <p class="site-ribbon-text"><?php echo t('ribbon_1_text'); ?></p>
    </div>
    <a href="book-service.php" class="btn btn-light btn-lg"><?php echo t('ribbon_1_cta'); ?></a>
  </div>
</section>

<div class="container page-wrap">

<section class="section-block">
  <div class="section-head">
    <div>
      <div class="section-kicker"><?php echo t('featured_picks'); ?></div>
      <h2 class="section-title"><?php echo t('featured_products'); ?></h2>
    </div>
    <a href="book-service.php" class="btn btn-outline-dark"><?php echo t('book_now'); ?></a>
  </div>
  <div class="row g-4">
    <?php foreach ($featured as $product) { include __DIR__ . '/pages/product-card.php'; } ?>
  </div>
</section>

<section class="section-block reveal">
  <div class="section-head">
    <div>
      <div class="section-kicker"><?php echo t('why_choose_us'); ?></div>
      <h2 class="section-title"><?php echo t('why_choose_us'); ?></h2>
    </div>
  </div>
  <div class="why-grid">
    <article class="why-card">
      <div class="why-icon"><i class="fa fa-user-check"></i></div>
      <h3><?php echo t('why_1_title'); ?></h3>
      <p><?php echo t('why_1_text'); ?></p>
    </article>
    <article class="why-card">
      <div class="why-icon"><i class="fa fa-calendar-alt"></i></div>
      <h3><?php echo t('why_2_title'); ?></h3>
      <p><?php echo t('why_2_text'); ?></p>
    </article>
    <article class="why-card">
      <div class="why-icon"><i class="fa fa-comments"></i></div>
      <h3><?php echo t('why_3_title'); ?></h3>
      <p><?php echo t('why_3_text'); ?></p>
    </article>
    <article class="why-card">
      <div class="why-icon"><i class="fa fa-map-marker-alt"></i></div>
      <h3><?php echo t('why_4_title'); ?></h3>
      <p><?php echo t('why_4_text'); ?></p>
    </article>
  </div>
</section>

</div>

<?php
$promoBannerImg = ASSET_URL . 'images/cleaning-hero.jpg';
if (!empty($aboutPage['about_banner'])) {
    $promoBannerImg = getProductImage($aboutPage['about_banner']);
}
?>
<section class="home-promo-banner reveal" style="--promo-banner-image: url('<?php echo e($promoBannerImg); ?>');">
  <div class="home-promo-banner-shade"></div>
  <div class="home-promo-banner-inner">
    <div class="home-promo-banner-copy">
      <div class="home-promo-kicker"><?php echo t('why_choose_us'); ?></div>
      <h2 class="home-promo-title"><?php echo t('cta_ready_title'); ?></h2>
      <p class="home-promo-text"><?php echo t('cta_ready_text'); ?></p>
      <div class="home-promo-actions">
        <a href="book-service.php" class="btn btn-light btn-lg"><?php echo t('book_now'); ?></a>
        <a href="contact.php" class="btn btn-outline-light btn-lg"><?php echo t('contact_us_btn'); ?></a>
      </div>
    </div>
  </div>
</section>

<div class="container page-wrap">

<?php if ($homeReviews) { ?>
<section class="section-block reveal">
  <div class="section-head">
    <div>
      <div class="section-kicker"><?php echo t('reviews'); ?></div>
      <h2 class="section-title"><?php echo t('reviews_title'); ?></h2>
      <p class="section-subtitle"><?php echo t('reviews_subtitle'); ?></p>
    </div>
    <a href="reviews.php" class="btn btn-outline-dark"><?php echo t('reviews'); ?></a>
  </div>
  <div class="row g-4">
    <?php foreach ($homeReviews as $item) {
      $initial = strtoupper(mb_substr($item['name'], 0, 1));
      $role = trim($item['designation'] . ($item['company'] !== '' ? ' · ' . $item['company'] : ''));
      $rating = max(1, min(5, (int)$item['rating']));
    ?>
      <div class="col-md-4">
        <article class="review-card h-100">
          <div class="review-stars mb-3">
            <?php for ($i = 1; $i <= 5; $i++) { ?>
              <i class="fa fa-star<?php echo $i <= $rating ? '' : '-o'; ?>"></i>
            <?php } ?>
          </div>
          <p class="review-text">“<?php echo e($item['review']); ?>”</p>
          <div class="review-author">
            <?php if (!empty($item['photo'])) { ?>
              <img src="<?php echo e(getProductImage($item['photo'])); ?>" alt="<?php echo e($item['name']); ?>">
            <?php } else { ?>
              <span class="review-avatar"><?php echo e($initial); ?></span>
            <?php } ?>
            <div>
              <strong><?php echo e($item['name']); ?></strong>
              <?php if ($role !== '') { ?><div class="text-muted small"><?php echo e($role); ?></div><?php } ?>
            </div>
          </div>
        </article>
      </div>
    <?php } ?>
  </div>
</section>
<?php } ?>

<section class="section-block">
  <div class="section-head">
    <div>
      <div class="section-kicker"><?php echo t('popular_products'); ?></div>
      <h2 class="section-title"><?php echo t('popular_products'); ?></h2>
    </div>
  </div>
  <div class="row g-4">
    <?php foreach ($popular as $product) { include __DIR__ . '/pages/product-card.php'; } ?>
  </div>
</section>

<?php if ($topCategories) { ?>
<section class="section-block reveal">
  <div class="section-head">
    <div>
      <div class="section-kicker"><?php echo t('browse_by_category'); ?></div>
      <h2 class="section-title"><?php echo t('browse_by_category'); ?></h2>
    </div>
  </div>
  <div class="row g-4">
    <?php foreach ($topCategories as $top) { ?>
      <div class="col-md-4">
        <a class="category-tile" href="category.php?id=<?php echo (int)$top['tcat_id']; ?>">
          <h5><?php echo e($top['tcat_name']); ?></h5>
          <p><?php echo t('discover_category_text'); ?></p>
          <span><?php echo t('explore'); ?> <i class="fa fa-arrow-right"></i></span>
        </a>
      </div>
    <?php } ?>
  </div>
</section>
<?php } ?>

</div>

<?php
$quoteBannerImg = ASSET_URL . 'images/cleaning-side.jpg';
$quoteBannerPath = __DIR__ . '/assets/images/cleaning-side.jpg';
if (!is_file($quoteBannerPath)) {
    $quoteBannerImg = ASSET_URL . 'images/cleaning-hero.jpg';
}
?>
<section class="home-promo-banner home-promo-banner--quote reveal" style="--promo-banner-image: url('<?php echo e($quoteBannerImg); ?>');">
  <div class="home-promo-banner-shade"></div>
  <div class="home-promo-banner-inner">
    <div class="home-promo-banner-copy">
      <div class="home-promo-kicker"><?php echo t('contact'); ?></div>
      <h2 class="home-promo-title"><?php echo t('ribbon_3_title'); ?></h2>
      <p class="home-promo-text"><?php echo t('ribbon_3_text'); ?></p>
      <div class="home-promo-actions">
        <a href="contact.php" class="btn btn-light btn-lg"><?php echo t('ribbon_3_cta'); ?></a>
        <a href="book-service.php" class="btn btn-outline-light btn-lg"><?php echo t('book_now'); ?></a>
      </div>
    </div>
  </div>
</section>

<div class="container page-wrap">

<section class="section-block">
  <div class="section-head">
    <div>
      <div class="section-kicker"><?php echo t('visit_us'); ?></div>
      <h2 class="section-title"><?php echo t('visit_us'); ?></h2>
    </div>
  </div>
  <div class="map-shell">
    <iframe loading="lazy" title="Service area" src="https://www.google.com/maps?q=Kathmandu,Nepal&output=embed"></iframe>
  </div>
</section>

<?php if ($newsletterEnabled) { ?>
<section class="section-block">
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="section-head mb-3">
        <div>
          <div class="section-kicker"><?php echo t('from_the_blog'); ?></div>
          <h2 class="section-title"><?php echo t('from_the_blog'); ?></h2>
        </div>
      </div>
      <div class="row g-4">
        <?php foreach ($posts as $post) { ?>
          <div class="col-md-6 col-lg-4">
            <article class="card-hover blog-card h-100">
              <img src="<?php echo getProductImage($post['photo']); ?>" alt="">
              <div class="p-3">
                <h5><?php echo e($post['post_title']); ?></h5>
                <p class="text-muted small"><?php echo excerpt(strip_tags($post['post_content']), 100); ?></p>
                <a class="btn btn-dark btn-sm" href="blog.php?id=<?php echo (int)$post['post_id']; ?>"><?php echo t('read_more'); ?></a>
              </div>
            </article>
          </div>
        <?php } ?>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="newsletter-panel h-100">
        <div class="section-kicker"><?php echo t('newsletter'); ?></div>
        <h3 class="section-title mb-3"><?php echo t('newsletter'); ?></h3>
        <p class="text-muted mb-4"><?php echo e($newsletterText); ?></p>
        <?php if (!empty($newsletterMessage)) { ?>
          <div class="alert alert-<?php echo e($newsletterType); ?> rounded-4 mb-3"><?php echo e($newsletterMessage); ?></div>
        <?php } ?>
        <form method="post" class="d-grid gap-3">
          <input type="email" class="form-control" name="newsletter_email" placeholder="<?php echo t('your_email'); ?>" required>
          <button class="btn btn-dark" type="submit"><?php echo t('subscribe'); ?></button>
        </form>
      </div>
    </div>
  </div>
</section>
<?php } ?>

<?php if ($teamStaff) { ?>
<section class="section-block team-section">
  <div class="section-head">
    <div>
      <div class="section-kicker"><?php echo t('our_team'); ?></div>
      <h2 class="section-title"><?php echo t('professional_team'); ?></h2>
      <p class="section-subtitle"><?php echo t('professional_team_subtitle'); ?></p>
    </div>
  </div>
  <div class="swiper teamSwiper">
    <div class="swiper-wrapper">
      <?php foreach ($teamStaff as $member) {
        $rating = max(1, min(5, (int)($member['rating'] ?? 5)));
        $role = trim($member['designation'] ?? '');
        $fb = trim($member['facebook_url'] ?? '');
        $ig = trim($member['instagram_url'] ?? '');
        $call = preg_replace('/\s+/', '', (string)($member['phone'] ?? ''));
        $photo = !empty($member['photo']) ? getProductImage($member['photo']) : (ASSET_URL . 'images/placeholder.png');
      ?>
      <div class="swiper-slide">
        <article class="team-card">
          <div class="team-photo">
            <img src="<?php echo e($photo); ?>" alt="<?php echo e($member['full_name']); ?>">
          </div>
          <h3><?php echo e($member['full_name']); ?></h3>
          <?php if ($role !== '') { ?><p class="team-role"><?php echo e($role); ?></p><?php } ?>
          <div class="review-stars team-stars" aria-label="<?php echo $rating; ?> stars">
            <?php for ($i = 1; $i <= 5; $i++) { ?>
              <i class="fa fa-star<?php echo $i <= $rating ? '' : '-o'; ?>"></i>
            <?php } ?>
          </div>
          <div class="team-social">
            <?php if ($fb !== '') { ?>
              <a href="<?php echo e($fb); ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <?php } ?>
            <?php if ($ig !== '') { ?>
              <a href="<?php echo e($ig); ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <?php } ?>
            <?php if ($call !== '') { ?>
              <a href="tel:<?php echo e($call); ?>" aria-label="Call"><i class="fa fa-phone"></i></a>
            <?php } ?>
          </div>
        </article>
      </div>
      <?php } ?>
    </div>
    <div class="swiper-pagination team-pagination"></div>
    <div class="swiper-button-prev team-nav"></div>
    <div class="swiper-button-next team-nav"></div>
  </div>
</section>
<?php } ?>

<section class="section-block">
  <div class="section-head">
    <div>
      <div class="section-kicker"><?php echo t('faqs'); ?></div>
      <h2 class="section-title"><?php echo t('faqs'); ?></h2>
    </div>
  </div>
  <div class="accordion faq-accordion" id="faqAccordion">
    <?php if ($faqs) { foreach ($faqs as $faq) { ?>
      <div class="accordion-item">
        <h2 class="accordion-header" id="faqHeading<?php echo $faq['faq_id']; ?>">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse<?php echo $faq['faq_id']; ?>">
            <span class="faq-question-icon"><i class="fa fa-question-circle"></i></span>
            <?php echo e($faq['faq_title']); ?>
          </button>
        </h2>
        <div id="faqCollapse<?php echo $faq['faq_id']; ?>" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
          <div class="accordion-body faq-answer">
            <div class="faq-answer-text rich-content"><?php echo renderRichHtml($faq['faq_content']); ?></div>
          </div>
        </div>
      </div>
    <?php } } else { ?>
      <div class="alert alert-light rounded-4"><?php echo t('no_faqs_yet'); ?></div>
    <?php } ?>
  </div>
</section>

</div>

<?php if ($homeClients) { ?>
<section class="clients-band">
  <div class="clients-band-inner">
    <div class="clients-kicker"><?php echo t('our_clients'); ?></div>
    <h2 class="clients-title"><?php echo t('preferred_by_professionals'); ?></h2>
    <div class="clients-logos">
      <?php foreach ($homeClients as $client) {
        $logoUrl = getProductImage($client['logo']);
        $link = trim($client['website_url'] ?? '');
        $alt = $client['name'] !== '' ? $client['name'] : loadLang('client');
      ?>
        <?php if ($link !== '') { ?>
          <a class="client-logo-item" href="<?php echo e($link); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo e($alt); ?>">
            <img src="<?php echo e($logoUrl); ?>" alt="<?php echo e($alt); ?>">
          </a>
        <?php } else { ?>
          <div class="client-logo-item">
            <img src="<?php echo e($logoUrl); ?>" alt="<?php echo e($alt); ?>">
          </div>
        <?php } ?>
      <?php } ?>
    </div>
  </div>
</section>
<?php } ?>

<?php if (!empty($settings['banner_login'])): ?>
<div class="modal fade" id="welcomePopup" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 bg-transparent shadow-none">
      <button type="button" class="btn-close bg-white rounded-circle position-absolute top-0 end-0 m-2" data-bs-dismiss="modal" aria-label="<?php echo t('close'); ?>" style="z-index:999;"></button>
      <img src="<?php echo getProductImage($settings['banner_login']); ?>" class="img-fluid rounded-4 shadow" alt="<?php echo t('welcome_banner'); ?>">
    </div>
  </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
  if (!sessionStorage.getItem("welcomePopupShown")) {
    new bootstrap.Modal(document.getElementById('welcomePopup')).show();
    sessionStorage.setItem("welcomePopupShown", "true");
  }
});
</script>
<?php endif; ?>
<?php include __DIR__ . '/inc/footer.php'; ?>
