<?php
// ============================================================
// Kamadhenu Goushala — Shared <head> Include
// $page_title   (string) — page-specific title
// $page_desc    (string) — meta description
// $extra_css    (string) — additional <link> tags
// ============================================================
require_once __DIR__ . '/../includes/functions.php';

$site_name = getSetting('site_name', SITE_NAME);
$full_title = isset($page_title) ? $page_title . ' | ' . $site_name : $site_name . ' | ' . getSetting('site_tagline', SITE_TAGLINE);
$meta_desc  = $page_desc ?? getSetting('site_tagline', SITE_TAGLINE);
$base       = BASE_URL;

// Preload banner image immediately so browser loads it with highest priority
if (!isset($banner)) {
    $currPageKey = basename($_SERVER['SCRIPT_NAME'] ?? '', '.php');
    $currKey = ($currPageKey === 'index' || empty($currPageKey)) ? 'home' : $currPageKey;
    $banner = getPageBanner($currKey);
}
$preloadBannerImage = !empty($banner['banner_image']) ? getImageUrl($banner['banner_image']) : '';
?>
<!DOCTYPE html>
<html lang="<?= getCurrentLang() ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- Instant Theme Initializer (Prevents Flash of Light/Dark Theme) -->
  <script>
    (function() {
      try {
        const savedTheme = localStorage.getItem('kamadhenu_theme') || 'light';
        if (savedTheme === 'dark') {
          document.documentElement.setAttribute('data-theme', 'dark');
          document.documentElement.classList.add('dark-theme');
        } else {
          document.documentElement.setAttribute('data-theme', 'light');
          document.documentElement.classList.remove('dark-theme');
        }
      } catch(e) {}
    })();
  </script>

  <title><?= e($full_title) ?></title>
  <meta name="description" content="<?= e($meta_desc) ?>">
  <link rel="canonical" href="<?= $base . $_SERVER['PHP_SELF'] ?>">
  <?php 
  $siteFavicon = getSetting('site_favicon', '');
  if (empty($siteFavicon)) {
      $siteFavicon = getSetting('site_logo', '');
  }
  ?>
  <?php if (!empty($siteFavicon)): ?>
    <link rel="icon" href="<?= e(getImageUrl($siteFavicon)) ?>">
  <?php endif; ?>

  <!-- High-Priority Instant Image Preload -->
  <?php if (!empty($preloadBannerImage)): ?>
    <link rel="preload" as="image" href="<?= e($preloadBannerImage) ?>" fetchpriority="high">
    <?php if (str_starts_with($preloadBannerImage, 'http')): ?>
      <link rel="preconnect" href="<?= e(parse_url($preloadBannerImage, PHP_URL_SCHEME) . '://' . parse_url($preloadBannerImage, PHP_URL_HOST)) ?>">
      <link rel="dns-prefetch" href="<?= e(parse_url($preloadBannerImage, PHP_URL_SCHEME) . '://' . parse_url($preloadBannerImage, PHP_URL_HOST)) ?>">
    <?php endif; ?>
  <?php endif; ?>

  <!-- Open Graph & WhatsApp Link Preview Meta Tags -->
  <?php 
  $siteLogo = getSetting('site_logo', '');
  $defaultOgImage = !empty($siteLogo) ? getImageUrl($siteLogo) : (!empty($preloadBannerImage) ? $preloadBannerImage : 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=1200&q=80');
  $ogImage = !empty($page_og_image) ? getImageUrl($page_og_image) : $defaultOgImage;
  $ogUrl   = $page_og_url ?? ($base . ($_SERVER['REQUEST_URI'] ?? $_SERVER['PHP_SELF']));
  ?>
  <meta property="og:site_name"   content="<?= e($site_name) ?>">
  <meta property="og:title"       content="<?= e($full_title) ?>">
  <meta property="og:description" content="<?= e($meta_desc) ?>">
  <meta property="og:type"        content="website">
  <meta property="og:url"         content="<?= e($ogUrl) ?>">
  <meta property="og:image"       content="<?= e($ogImage) ?>">
  <meta property="og:image:secure_url" content="<?= e($ogImage) ?>">
  <meta property="og:image:alt"   content="<?= e($full_title) ?>">
  <meta property="og:image:width"  content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:locale"      content="<?= getCurrentLang() === 'kn' ? 'kn_IN' : 'en_US' ?>">
  <meta name="twitter:card"       content="summary_large_image">
  <meta name="twitter:title"      content="<?= e($full_title) ?>">
  <meta name="twitter:description" content="<?= e($meta_desc) ?>">
  <meta name="twitter:image"      content="<?= e($ogImage) ?>">

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Kannada:wght@400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="<?= $base ?>/assets/css/variables.css?v=<?= filemtime(__DIR__ . '/../assets/css/variables.css') ?>">
  <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">

  <?= $extra_css ?? '' ?>
</head>
