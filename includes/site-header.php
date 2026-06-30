<?php
$pageTitle   = $pageTitle   ?? 'Brasil DNA — Experience the Essence of Brazil';
$currentPage = $currentPage ?? 'home';
if (!function_exists('esc')) {
    function esc($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
}
require_once __DIR__ . '/config.php';
$v_css = file_exists(__DIR__ . '/../assets/style.css') ? filemtime(__DIR__ . '/../assets/style.css') : 1;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($pageTitle) ?></title>
<meta name="description" content="Brasil DNA: Where Nature, Culture, and Warmth Create Unforgettable Journeys.">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bungee&family=Playfair+Display:ital,wght@0,700;0,900;1,700;1,900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/style.css?v=<?= $v_css ?>">
</head>
<body>

<header class="site-header" id="siteHeader">
  <div class="container header-inner">
    <div class="logo-group">
      <a href="<?= BASE_URL ?>index.php" aria-label="Brasil DNA — Home">
        <img src="<?= BASE_URL ?>assets/images/logo_brasilDNA_preto.webp" alt="Brasil DNA" class="logo-img" height="50" loading="eager">
      </a>
      <div class="logo-divider"></div>
      <img src="<?= BASE_URL ?>assets/images/Logotipo_Brasil.png" alt="Brasil" class="logo-brasil" height="44" loading="eager">
    </div>

    <button class="nav-toggle" id="navToggle" aria-label="Abrir menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>

<!-- Nav fora do header — evita stacking context do backdrop-filter -->
<nav class="main-nav" id="mainNav" aria-label="Navegação principal">
  <a href="<?= BASE_URL ?>pages/about-us.php" <?= $currentPage === 'about' ? 'class="is-active"' : '' ?>>About Us</a>
  <a href="<?= BASE_URL ?>pages/news.php" <?= in_array($currentPage, ['news', 'post']) ? 'class="is-active"' : '' ?>>News</a>
  <a href="<?= BASE_URL ?>index.php#clients" class="nav-cta">Explore Brazil</a>
</nav>
