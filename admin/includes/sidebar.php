<?php
$paginaAtiva = $paginaAtiva ?? '';
$adminBase   = $adminBase   ?? '';
$userName    = $_SESSION['admin_nome']  ?? 'Admin';
$userEmail   = $_SESSION['admin_email'] ?? '';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> — Brasil DNA</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
  <link rel="stylesheet" href="<?= $adminBase ?>assets/admin.css?v=<?= filemtime(__DIR__ . '/../../admin/assets/admin.css') ?>">
</head>
<body>

<button class="adm-mob-toggle" id="admMobToggle" aria-label="Abrir menu">&#9776;</button>
<div class="adm-mob-overlay" id="admMobOverlay"></div>

<div class="adm-shell">

  <!-- Sidebar -->
  <aside class="adm-sidebar">
    <div class="adm-sidebar__logo">
      <svg class="adm-sidebar__logo-img" aria-label="Brasil DNA" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1847.11 800.51">
        <defs>
          <style>
            .sb-cls-1 { letter-spacing: 0em; }
            .sb-cls-2 { fill: #11723d; }
            .sb-cls-3 { letter-spacing: 0em; }
            .sb-cls-4 { fill: #c9423f; }
            .sb-cls-5 { fill: #f1ab18; font-family: Bungee, sans-serif; font-size: 270.55px; }
            .sb-cls-6 { stroke: #fff; stroke-miterlimit: 10; stroke-width: 6px; }
          </style>
        </defs>
        <g>
          <path class="sb-cls-4" d="M623.34,455.11c4.83-.02,7.58,3.94,7.99,7.29.45,3.64-1.94,8.79-6.52,9.01-24.36,1.17-47.42-7.47-65.8-23.26-10.39-8.92-19.37-18.4-27.22-29.56l-22.28-31.65c-17.19-24.42-37.33-53.2-67.36-60.51-.5-.23-1.01-.47-1.51-.7-19.52-5.48-41.57-3.91-59.95,6.29-20.39,11.32-34.49,28.89-47.7,47.78l-23.76,33.98c-9.36,13.39-19.85,25.23-32.35,35.67-18.61,15.55-41.14,22.77-65.36,22.24-45.91-1.01-72.57-29.99-97.19-65.05l-25.74-36.66-9.47-11.18c-1.04-1.33-2.07-2.66-3.11-3.99-17.3-19.76-38.9-32.22-65.92-32.11-4.68.02-7.92-3.35-8.33-7.39-.35-3.51,1.76-8.74,6.46-8.95,23.61-1.02,45.97,7.21,64.18,22.1,33.37,27.29,49.72,67.21,79.39,98.4,9.28,9.76,19.59,17.53,32.69,22.84,3.96,1.23,7.92,2.46,11.88,3.69,11.96,2.21,23.97,2.24,35.89-.02,3.99-1.21,7.98-2.42,11.97-3.62,14.75-6.19,26.33-15.62,36.1-26.6,1.54-1.85,3.07-3.7,4.61-5.55l3.09-4.54,5.05-5.04,24.08-34.59c24.4-35.05,50.21-64.81,95.81-67.24,46.35-2.47,76.6,23.29,101.86,59.42l25.3,36.18c20.55,29.38,45,53.52,83.25,53.33Z"/>
          <path class="sb-cls-4" d="M424.68,454.89c18.13-.9,34.37-8.36,48.36-20.21,12.28-10.78,21.74-23.23,31.88-37.08l10.09,14.38c-23.92,33.28-50.95,59.14-94.44,59.73-45.35.62-73.11-24.75-98.06-59.82l9.99-14.36,17.74,22.94c17.48,20.08,35.54,31.74,62.53,34.61,3.97-.07,7.94-.13,11.9-.2Z"/>
          <path class="sb-cls-4" d="M276.53,350.38c-28.02-29.2-69.26-36.84-104.62-16.02l-1.17,1.05-12.35,8.89c-11.3,10.89-20.75,22.51-30.22,35.93l-10.08-14.35c25.19-35.82,54.78-61.81,101.02-59.76,41.99,1.86,68.17,27.16,91.47,59.77l-10.1,14.4c-7.68-10.93-15.27-20.45-23.95-29.9Z"/>
          <path class="sb-cls-4" d="M569.91,342.77c3.11-2.41,6.23-4.83,9.34-7.24l-13.52,10.36c-11.07,10.15-19.68,21.97-28.84,34.37l-9.97-14.44c9.92-13.76,20.37-26.6,33.58-37.48,18.22-15.01,40.66-23.12,64.34-22,4.59.22,6.96,5.61,6.51,8.92-.66,4.84-4.35,6.97-9.34,7.47l-16.91,1.7c-3.96,1.24-7.92,2.47-11.88,3.71-4.3,2.2-8.6,4.41-12.89,6.61"/>
        </g>
        <path class="sb-cls-4" d="M61.5,433.84c-3.11,2.41-6.23,4.83-9.34,7.24l13.52-10.36c11.07-10.15,19.68-21.97,28.84-34.37l9.97,14.44c-9.92,13.76-20.37,26.6-33.58,37.48-18.22,15.01-40.66,23.12-64.34,22-4.59-.22-6.96-5.61-6.51-8.92.66-4.84,4.35-6.97,9.34-7.47l16.91-1.7c3.96-1.24,7.92-2.47,11.88-3.71,4.3-2.2,8.6-4.41,12.89-6.61"/>
        <path class="sb-cls-4" d="M88.89,477.83c-3.11,2.41-6.23,4.83-9.34,7.24l13.52-10.36c11.07-10.15,19.68-21.97,28.84-34.37l9.97,14.44c-9.92,13.76-20.37,26.6-33.58,37.48-18.22,15.01-40.66,23.12-64.34,22-4.59-.22-6.96-5.61-6.51-8.92.66-4.84,4.35-6.97,9.34-7.47l16.91-1.7c3.96-1.24,7.92-2.47,11.88-3.71,4.3-2.2,8.6-4.41,12.89-6.61"/>
        <path class="sb-cls-4" d="M543.94,483c3.11,2.41,6.23,4.83,9.34,7.24l-13.52-10.36c-11.07-10.15-19.68-21.97-28.84-34.37l-9.97,14.44c9.92,13.76,20.37,26.6,33.58,37.48,18.22,15.01,40.66,23.12,64.34,22,4.59-.22,6.96-5.61,6.51-8.92-.66-4.84-4.35-6.97-9.34-7.47l-16.91-1.7c-3.96-1.24-7.92-2.47-11.88-3.71-4.3-2.2-8.6-4.41-12.89-6.61"/>
        <ellipse class="sb-cls-4" cx="268.06" cy="268.05" rx="22.51" ry="23.2"/>
        <ellipse class="sb-cls-4" cx="369.68" cy="268.24" rx="22.51" ry="23.2"/>
        <path class="sb-cls-6" d="M148.28,387.03c10.83,26.44,36.15,44.11,64.37,44.96,29.62.89,57.03-16.91,68.57-44.64-14.65-28.43-44.57-45-74.72-41.73-38.7,4.2-56.93,38.84-58.22,41.4Z"/>
        <path class="sb-cls-6" d="M354.48,387.6c10.83,26.44,36.15,44.11,64.37,44.96,29.62.89,57.03-16.91,68.57-44.64-14.65-28.43-44.57-45-74.72-41.73-38.7,4.2-56.93,38.84-58.22,41.4Z"/>
        <path class="sb-cls-2" d="M45.5,128.67c13.2,2.92,26.4,5.85,39.6,8.77,22.31,4.1,43.08,12.05,62.35,23.74,15,9.1,26.15,21.6,32.43,38.03,7.36,15.82,10.22,32.63,9.52,50.18-.37,9.27.33,17.5,3.07,26.82-36.6,3.79-83.53-3.23-106.75-30.61-12.95-15.27-20.63-33.68-24.96-53.25-3.7-16.7-10.78-43.62-16.39-54.19-.64-1.21-4.58-8.4-3.25-9.41.15-.12.34-.13.45-.12l3.94.03Z"/>
        <path class="sb-cls-2" d="M587.5,129.95c-13.2,2.92-26.4,5.85-39.6,8.77-22.31,4.1-43.08,12.05-62.35,23.74-15,9.1-26.15,21.6-32.43,38.03-7.36,15.82-10.22,32.63-9.52,50.18.37,9.27-.33,17.5-3.07,26.82,36.6,3.79,83.53-3.23,106.75-30.61,12.95-15.27,20.63-33.68,24.96-53.25,3.7-16.7,10.78-43.62,16.39-54.19.64-1.21,4.58-8.4,3.25-9.41-.15-.12-.34-.13-.45-.12l-3.94.03Z"/>
        <path class="sb-cls-2" d="M316.59,3.05c7.27,11.4,14.53,22.8,21.8,34.2,12.88,18.67,21.94,38.98,27.3,60.87,4.17,17.04,3.22,33.77-3.96,49.82-5.98,16.39-15.85,30.3-28.75,42.22-6.81,6.29-12.14,12.6-16.79,21.14-28.56-23.2-56.78-61.35-53.84-97.13,1.64-19.96,9.23-38.41,20-55.3,9.2-14.43,23.22-38.46,26.72-49.91.4-1.31,2.7-9.17,4.35-8.95.19.03.33.15.41.24l2.76,2.81Z"/>
        <text class="sb-cls-5" transform="translate(712.59 274.13)"><tspan x="0" y="0">BRASI</tspan><tspan class="sb-cls-1" x="934.2" y="0">L</tspan></text>
        <text class="sb-cls-5" transform="translate(710.64 552.69)"><tspan x="0" y="0">dn</tspan><tspan class="sb-cls-3" x="405.55" y="0">a</tspan></text>
      </svg>
      <div class="adm-sidebar__logo-tag">Painel administrativo</div>
    </div>

    <div class="adm-sidebar__user">
      <div class="adm-sidebar__user-name"><?= htmlspecialchars($userName) ?></div>
      <?php if ($userEmail): ?>
        <div class="adm-sidebar__user-email"><?= htmlspecialchars($userEmail) ?></div>
      <?php endif; ?>
    </div>

    <nav class="adm-sidebar__nav">
      <div class="adm-sidebar__label">Visão geral</div>

      <a href="<?= $adminBase ?>painel.php" class="adm-sidebar__link <?= $paginaAtiva === 'painel' ? 'is-active' : '' ?>">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <rect x="2" y="2" width="9" height="9" rx="1.5"/><rect x="13" y="2" width="9" height="4" rx="1.5"/>
          <rect x="13" y="9" width="9" height="4" rx="1.5"/><rect x="2" y="13" width="9" height="9" rx="1.5"/>
          <rect x="13" y="15" width="9" height="7" rx="1.5"/>
        </svg>
        Meu Painel
      </a>

      <div class="adm-sidebar__label">Conteúdo</div>

      <a href="<?= $adminBase ?>index.php" class="adm-sidebar__link <?= $paginaAtiva === 'posts' ? 'is-active' : '' ?>">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Posts
      </a>

      <a href="<?= $adminBase ?>banners.php" class="adm-sidebar__link <?= $paginaAtiva === 'banners' ? 'is-active' : '' ?>">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <rect x="2" y="7" width="20" height="10" rx="2"/>
          <path d="M6 11h4M6 13h2"/>
        </svg>
        Banners
      </a>

      <a href="../clientes/" class="adm-sidebar__link <?= $paginaAtiva === 'clientes' ? 'is-active' : '' ?>">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
        </svg>
        Clientes
      </a>

      <?php if (($_SESSION['admin_tipo'] ?? '') === 'super_admin'): ?>
      <div class="adm-sidebar__label" style="margin-top:16px;">Sistema</div>

<a href="<?= $adminBase ?>admins.php" class="adm-sidebar__link <?= $paginaAtiva === 'admins' ? 'is-active' : '' ?>">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
        </svg>
        Admins
      </a>
      <?php endif; ?>
    </nav>


    <div class="adm-sidebar__bottom">
      <a href="<?= BASE_URL ?>index.php" target="_blank" rel="noopener" class="adm-sidebar__ver-site">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
        </svg>
        Ver site
      </a>
      <a href="<?= $adminBase ?>logout.php" class="adm-sidebar__sair">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
        </svg>
        Sair
      </a>
    </div>
  </aside>

  <!-- Área principal -->
  <div class="adm-main">
    <!-- Conteúdo da página -->
    <main class="adm-content">
