<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/breadcrumbs.php';
?>
<!DOCTYPE html>
<?php
$currentHtmlLang = getCurrentLang();
$ogLocaleMap = ['en' => 'en_US', 'ne' => 'ne_NP', 'hi' => 'hi_IN'];
$ogLocale = $ogLocaleMap[$currentHtmlLang] ?? 'en_US';
?>
<html lang="<?php echo e($currentHtmlLang); ?>" class="h-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
    $homeSeo = getHomeSeo();
    $siteNameRaw = (string) getSiteSetting('site_name', SITE_NAME);
    $siteName = e($siteNameRaw);
    $defaultDescription = e($homeSeo['description']);
    $defaultKeywords = e($homeSeo['keywords']);
    $defaultAuthor = e(getSiteSetting('site_author', $siteNameRaw));
    $resolvedTitle = seoPick($pageTitle ?? '', $homeSeo['title']);
    $pageTitleTag = e($resolvedTitle !== '' ? $resolvedTitle : $siteNameRaw);
    $pageDescription = e(seoPick($metaDescription ?? '', $homeSeo['description'], 160));
    $pageKeywords = e(seoPick($metaKeywords ?? '', $homeSeo['keywords']));
    $pageAuthor = e(seoPick($metaAuthor ?? '', $siteNameRaw));
    $canonicalUrl = e($canonicalUrl ?? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']));
    $robotsContent = e($robots ?? 'index,follow');
    $ogType = e($ogType ?? 'website');
    $ogImageValue = $ogImage ?? getSiteSetting('og_image', 'assets/images/og-default.png');
    $ogImageUrl = e(getProductImage($ogImageValue));
    $ogImageAlt = e($ogImageAlt ?? $pageTitleTag);
    $twitterCard = e($twitterCard ?? 'summary_large_image');
    $twitterSite = e($twitterSite ?? getSiteSetting('twitter_handle', '@' . preg_replace('/[^a-z0-9_]/i', '', strtolower($siteName))));
    $twitterCreator = e($twitterCreator ?? $twitterSite);
    $googleVerification = e(getSiteSetting('google_site_verification', ''));
    $bingVerification = e(getSiteSetting('bing_site_verification', ''));
    $publishedTime = e($publishedTime ?? '');
    $modifiedTime = e($modifiedTime ?? '');
    $jsonLdData = $jsonLd ?? getDefaultSeoJsonLd();
    $geoRegion = e(getSiteSetting('geo_region', 'NZ-AUK'));
    $geoPlacename = e(getSiteSetting('geo_placename', 'Auckland, New Zealand'));
    ?>
    <title><?php
        if ($resolvedTitle === '' || strcasecmp($resolvedTitle, $siteNameRaw) === 0) {
            echo e($homeSeo['title'] !== '' ? $homeSeo['title'] : $siteNameRaw);
        } elseif (stripos($resolvedTitle, $siteNameRaw) !== false) {
            echo $pageTitleTag;
        } else {
            echo $pageTitleTag . ' | ' . $siteName;
        }
    ?></title>
    <meta name="description" content="<?php echo $pageDescription; ?>">
    <meta name="keywords" content="<?php echo $pageKeywords; ?>">
    <meta name="author" content="<?php echo $pageAuthor; ?>">
    <meta name="robots" content="<?php echo $robotsContent; ?>">
    <meta name="googlebot" content="<?php echo $robotsContent; ?>">
    <meta name="bingbot" content="<?php echo $robotsContent; ?>">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <meta name="geo.region" content="<?php echo $geoRegion; ?>">
    <meta name="geo.placename" content="<?php echo $geoPlacename; ?>">
    <?php if (!empty($googleVerification)): ?>
    <meta name="google-site-verification" content="<?php echo $googleVerification; ?>">
    <?php endif; ?>
    <?php if (!empty($bingVerification)): ?>
    <meta name="msvalidate.01" content="<?php echo $bingVerification; ?>">
    <?php endif; ?>
    <link rel="canonical" href="<?php echo $canonicalUrl; ?>">
    <link rel="sitemap" type="application/xml" title="Sitemap" href="<?php echo e(rtrim(BASE_URL, '/') . '/sitemap.php'); ?>">
    <meta property="og:locale" content="<?php echo e($ogLocale); ?>">
    <meta property="og:site_name" content="<?php echo $siteName; ?>">
    <meta property="og:type" content="<?php echo $ogType; ?>">
    <meta property="og:title" content="<?php echo $pageTitleTag; ?>">
    <meta property="og:description" content="<?php echo $pageDescription; ?>">
    <meta property="og:url" content="<?php echo $canonicalUrl; ?>">
    <meta property="og:image" content="<?php echo $ogImageUrl; ?>">
    <meta property="og:image:alt" content="<?php echo $ogImageAlt; ?>">
    <?php if (!empty($publishedTime)): ?>
    <meta property="article:published_time" content="<?php echo $publishedTime; ?>">
    <?php endif; ?>
    <?php if (!empty($modifiedTime)): ?>
    <meta property="article:modified_time" content="<?php echo $modifiedTime; ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="<?php echo $twitterCard; ?>">
    <meta name="twitter:title" content="<?php echo $pageTitleTag; ?>">
    <meta name="twitter:description" content="<?php echo $pageDescription; ?>">
    <meta name="twitter:image" content="<?php echo $ogImageUrl; ?>">
    <meta name="twitter:site" content="<?php echo $twitterSite; ?>">
    <meta name="twitter:creator" content="<?php echo $twitterCreator; ?>">
    <script type="application/ld+json"><?php echo json_encode($jsonLdData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#062a62">
    <?php
    $siteFavicon = getSiteFavicon();
    if (!empty($siteFavicon['url'])):
    ?>
    <link rel="icon" href="<?php echo e($siteFavicon['url']); ?>" type="<?php echo e($siteFavicon['type']); ?>">
    <link rel="shortcut icon" href="<?php echo e($siteFavicon['url']); ?>" type="<?php echo e($siteFavicon['type']); ?>">
    <link rel="apple-touch-icon" href="<?php echo e($siteFavicon['url']); ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css">
    <link rel="stylesheet" href="<?php echo ASSET_URL; ?>css/style.css?v=20260721u">
    <style>
    /* Dropdown menu stays open when interacting inside */
    .mega-menu {
        min-width: 260px;
        max-height: 70vh;
        overflow-y: auto;
    }
    .category-group {
        border-bottom: 1px solid #f1f1f1;
        padding-bottom: 0.3rem;
    }
    .category-group:last-child {
        border-bottom: none;
    }
    .category-toggle {
        cursor: pointer;
        padding: 0.5rem 0.25rem;
        border-radius: 6px;
        transition: background 0.2s;
    }
    .category-toggle:hover {
        background: #f8f9fa;
    }
    .category-toggle .caret-icon {
        transition: transform 0.3s ease;
        font-size: 0.8rem;
        color: #6c757d;
    }
    .category-toggle.active .caret-icon {
        transform: rotate(180deg);
    }
    .subcategory-wrap .dropdown-item {
        padding: 0.25rem 0.75rem;
        font-size: 0.9rem;
        border-radius: 4px;
    }
    .subcategory-wrap .dropdown-item:hover {
        background: #e9ecef;
    }
    /* Header search & language layout improvements */
    :root {
        --header-control-height: 44px;
        --flag-size-desktop: 20px;
        --flag-size-mobile: 18px;
    }
    .header-controls {
        gap: 0.75rem;
        flex: 0 0 auto;
        justify-content: flex-end;
    }
    .header-search-language {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex: 0 0 auto;
        min-width: 0;
        max-width: 320px;
    }
    .search-form {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex: 1 1 auto;
        min-width: 0;
        width: auto;
    }
    .search-form .form-control {
        height: var(--header-control-height);
        border-radius: 6px 0 0 6px;
        flex: 1 1 auto;
        min-width: 0;
        max-width: 240px;
    }
    .header-controls > .d-flex {
        flex: 0 0 auto;
    }
    .search-form .btn {
        height: var(--header-control-height);
        border-radius: 0 6px 6px 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 0.75rem;
    }
    .input-group .form-control { min-width: 0; }
    .language-switcher {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        height: var(--header-control-height);
    }
    .language-dropdown-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.35rem 0.5rem;
        height: var(--header-control-height);
        border-radius: 6px;
    }
    .language-switcher .dropdown-menu {
        min-width: 180px;
        padding: 0.35rem 0;
    }
    .language-switcher .dropdown-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.75rem;
    }
    .language-switcher .dropdown-item.active,
    .language-switcher .dropdown-item:hover {
        background: #f8f9fa;
    }
    .lang-flag {
        width: var(--flag-size-desktop);
        height: auto;
        display: block;
    }
    @media (max-width: 767px) {
        :root { --header-control-height: 44px; }
        .lang-flag { width: var(--flag-size-mobile); }
        .header-controls { flex-wrap: wrap; gap: 0.5rem; }
        .header-search-language { flex-basis: 100%; }
        .language-switcher { flex-basis: 100%; justify-content: flex-start; }
        .search-form .form-control { border-radius: 6px; }
        .search-form .btn { border-radius: 6px; }
        .language-dropdown-toggle { width: auto; }
    }
    /* Mobile small screens: keep search+submit on one row, then flags row, then icons+login row */
    @media (max-width: 575px) {
        .header-controls { flex-direction: column; align-items: stretch; gap: 0.5rem; }
        /* Search row: input + button stay inline */
        .header-search-language { order: 1; width: 100%; }
        .search-form { flex-direction: row; width: 100%; gap: 0.5rem; flex-wrap: nowrap; align-items: center; }
        .search-form .form-control { flex: 1 1 auto; min-width: 0; box-sizing: border-box; }
        .search-form .btn { flex: 0 0 48px; width: 48px; height: var(--header-control-height); padding: 0; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.5rem; }
        .search-form .form-control, .search-form .btn { white-space: nowrap; }

        /* Second row: language flags centered */
        .language-switcher { order: 2; display: flex; justify-content: center; gap: 0.35rem; width: 100%; padding: 0.25rem 0; }
        .language-switcher .language-dropdown-toggle { padding: 0.35rem 0.75rem; }
        .lang-flag { width: var(--flag-size-mobile); }

        /* Third row: icons (compare, wishlist, cart) and login button arranged in one row */
        .header-controls > .d-flex { order: 3; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; width: 100%; }
        .header-controls > .d-flex .icon-pill { display: inline-flex; margin: 0 0.25rem; }
        .header-controls > .d-flex .btn.btn-dark.btn-sm { flex: 0 0 auto; }
    }
</style>
</head>
<body<?php echo !empty($showWaterSplash) ? ' class="has-water-splash"' : ''; ?>>
<?php if (!empty($showWaterSplash)) { ?>
<div class="foam-intro" id="foamIntro" aria-hidden="true">
    <div class="foam-intro-bg"></div>
    <div class="foam-burst" aria-hidden="true">
        <span></span><span></span><span></span><span></span><span></span>
        <span></span><span></span><span></span><span></span><span></span>
    </div>
    <div class="foam-hand-wrap">
        <svg class="foam-hand" viewBox="0 0 240 220" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Cleaning glove">
            <defs>
                <linearGradient id="gloveGrad" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="#fff7e0"/>
                    <stop offset="55%" stop-color="#ffd54a"/>
                    <stop offset="100%" stop-color="#f0b429"/>
                </linearGradient>
                <linearGradient id="foamGrad" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#ffffff"/>
                    <stop offset="100%" stop-color="#d9ecff"/>
                </linearGradient>
            </defs>
            <!-- foam cloud behind glove -->
            <g class="foam-cloud">
                <ellipse cx="118" cy="78" rx="54" ry="34" fill="url(#foamGrad)" opacity=".95"/>
                <ellipse cx="86" cy="92" rx="30" ry="22" fill="#fff" opacity=".92"/>
                <ellipse cx="152" cy="90" rx="28" ry="20" fill="#fff" opacity=".9"/>
                <circle cx="104" cy="60" r="16" fill="#fff"/>
                <circle cx="136" cy="58" r="14" fill="#eef7ff"/>
                <circle cx="160" cy="72" r="12" fill="#fff"/>
            </g>
            <!-- yellow cleaning glove -->
            <g class="glove-shape">
                <path fill="url(#gloveGrad)" d="M98 188c-10 0-18-8-18-18V118c0-7 5-12 12-12s12 5 12 12v28h6V78c0-8 5-14 13-14s13 6 13 14v52h6V68c0-8 5-14 13-14s13 6 13 14v66h6V84c0-8 6-14 14-14s14 6 14 14v86c0 24-18 42-42 42H98z"/>
                <path fill="rgba(255,255,255,.35)" d="M112 96c0-5 3-8 7-8s7 3 7 8v46h-6V108c0-3-2-5-4-5s-4 2-4 5v34h-6V96z"/>
                <ellipse cx="146" cy="188" rx="34" ry="10" fill="rgba(0,0,0,.12)"/>
            </g>
            <!-- foam burst blobs on glove -->
            <g class="foam-on-glove">
                <circle cx="128" cy="108" r="10" fill="#fff"/>
                <circle cx="146" cy="118" r="8" fill="#eef7ff"/>
                <circle cx="118" cy="124" r="7" fill="#fff"/>
                <circle cx="156" cy="102" r="6" fill="#fff"/>
            </g>
        </svg>
        <div class="foam-intro-brand"><?php echo $siteName; ?></div>
    </div>
</div>
<?php } else { ?>
<div class="page-loader" id="pageLoader">
    <div class="spinner"></div>
</div>
<?php } ?>
<div class="topbar">
    <div class="container topbar-inner small">
        <div class="topbar-contact">
            <span class="topbar-contact-item"><i class="fa fa-phone"></i><?php echo e(getSiteSetting('contact_phone', '+977 9869224134')); ?></span>
            <span class="topbar-contact-item"><i class="fa fa-envelope"></i><?php echo e(getSiteSetting('contact_email', 'contact@sastikatrading.com.np')); ?></span>
        </div>
        <div class="topbar-social social-links">
            <?php foreach (getSocialLinks() as $social) { ?>
                <a href="<?php echo e($social['url']); ?>" target="_blank" rel="noreferrer" class="social-link" aria-label="<?php echo e($social['name']); ?>">
                    <i class="<?php echo e($social['icon']); ?>"></i>
                </a>
            <?php } ?>
        </div>
    </div>
</div>
<header class="site-header">
    <nav class="navbar navbar-expand-lg navbar-light container py-3">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
            <img src="<?php echo getProductImage(getSiteSetting('logo', 'logo.jpg')); ?>" alt="<?php echo t('brand_logo'); ?>">
            <span><?php echo $siteName; ?></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="<?php echo t('toggle_navigation'); ?>">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>"><?php echo t('home'); ?></a></li>
                <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="categoryDropdown" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
        <?php echo t('categories'); ?>
    </a>
    <ul class="dropdown-menu mega-menu p-3" aria-labelledby="categoryDropdown">
        <?php foreach (getTopCategories() as $top) { ?>
            <li class="category-group mb-2">
                <div class="category-toggle d-flex justify-content-between align-items-center" data-target="#collapse-<?php echo $top['tcat_id']; ?>">
                    <span class="fw-bold text-dark"><?php echo e($top['tcat_name']); ?></span>
                    <i class="fa fa-chevron-down caret-icon"></i>
                </div>
                <div class="subcategory-wrap" id="collapse-<?php echo $top['tcat_id']; ?>" style="display:none;">
                    <ul class="list-unstyled ms-3 mt-1">
                        <?php foreach (getMidCategories($top['tcat_id']) as $mid) { ?>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>category.php?id=<?php echo (int)$mid['mcat_id']; ?>"><?php echo e($mid['mcat_name']); ?></a></li>
                        <?php } ?>
                    </ul>
                </div>
            </li>
        <?php } ?>
    </ul>
</li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>about.php"><?php echo t('about'); ?></a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>gallery.php"><?php echo t('gallery'); ?></a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>blog.php"><?php echo t('blog'); ?></a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>contact.php"><?php echo t('contact'); ?></a></li>
            </ul>
            <div class="header-controls d-flex align-items-center gap-2 flex-nowrap ms-auto">
            <form class="header-search-language mb-2 position-relative search-shell" role="search" action="<?php echo BASE_URL; ?>search.php" method="get">
                <div class="search-form input-group">
                    <input class="form-control header-search-input" id="headerSearchInput" type="search" name="q" placeholder="<?php echo t('search_products'); ?>" aria-label="<?php echo t('search'); ?>" autocomplete="off">
                    <button class="btn btn-dark header-search-btn" type="submit"><i class="fa fa-search"></i></button>
                </div>
                <div id="searchResults" class="position-absolute top-100 start-0 w-100 bg-white rounded-4 shadow mt-2 p-2" style="z-index:1000; display:none;"></div>
            </form>
            <div class="d-flex align-items-center gap-2">
                <?php
                $currentLang = getCurrentLang();
                $langFlags = [
                    'en' => ['src' => ASSET_URL . 'images/flags/gb.svg', 'label' => 'English'],
                    'ne' => ['src' => ASSET_URL . 'images/flags/np.svg', 'label' => 'Māori'],
                    'hi' => ['src' => ASSET_URL . 'images/flags/in.svg', 'label' => 'हिन्दी'],
                ];
                $currentLangFlag = $langFlags[$currentLang] ?? $langFlags['en'];
                ?>
                <div class="dropdown language-switcher">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle language-dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?php echo $currentLangFlag['src']; ?>" alt="<?php echo e($currentLangFlag['label']); ?>" class="lang-flag">
                    </button>
                    <ul class="dropdown-menu">
                        <?php foreach ($langFlags as $code => $data) { ?>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2<?php echo $currentLang === $code ? ' active' : ''; ?>" href="<?php echo e(langSwitchUrl($code)); ?>">
                                    <img src="<?php echo $data['src']; ?>" alt="<?php echo e($data['label']); ?>" class="lang-flag">
                                    <span><?php echo e($data['label']); ?></span>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
                <a href="<?php echo BASE_URL; ?>wishlist.php" class="icon-pill position-relative" title="<?php echo t('wishlist'); ?>">
                    <i class="fa fa-bookmark"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?php echo wishCount(); ?></span>
                </a>
                <a href="<?php echo BASE_URL; ?>cart.php" class="icon-pill position-relative" title="<?php echo t('cart'); ?>" id="bookingCartIcon">
                    <i class="fa fa-calendar-check"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" data-cart-count><?php echo cartCount(); ?></span>
                </a>
                <?php if (isLoggedIn()) { ?>
                    <a href="<?php echo BASE_URL; ?>account/profile.php" class="btn btn-dark btn-sm"><?php echo t('account'); ?></a>
                <?php } else { ?>
                    <a href="<?php echo BASE_URL; ?>account/login.php" class="btn btn-dark btn-sm"><?php echo t('login'); ?></a>
                <?php } ?>
            </div>
            </div>
        </div>
    </nav>
</header>
<main class="site-main<?php echo !empty($fullWidth) ? ' is-fullwidth' : ''; ?>">
    <?php if (empty($fullWidth)) { ?>
    <div class="container page-wrap py-4">
        <?php echo renderFlash(); ?>
    <?php } else { ?>
        <?php
        $flashHtml = renderFlash();
        if (trim(strip_tags((string)$flashHtml)) !== '') {
            echo '<div class="container pt-3">' . $flashHtml . '</div>';
        }
        ?>
    <?php } ?>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    $('.category-toggle').on('click', function(e) {
        e.stopPropagation();
        var target = $(this).data('target');
        var $subWrap = $(target);
        var $toggle = $(this);
        $subWrap.slideToggle(300, function() {
            $toggle.toggleClass('active');
        });
    });
});
</script>