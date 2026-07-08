<?php
$paginaAtiva = $paginaAtiva ?? '';
$adminBase   = $adminBase   ?? './';
$userName    = $_SESSION['admin_nome']  ?? 'Admin';
$userEmail   = $_SESSION['admin_email'] ?? '';
$isSuperAdmin = ehSuperAdmin();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Backstage') ?> — Brasil DNA</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
  <link rel="stylesheet" href="<?= $adminBase ?>assets/admin.css?v=<?= filemtime(__DIR__ . '/../assets/admin.css') ?>">
</head>
<body>

<button class="adm-mob-toggle" id="admMobToggle" aria-label="Abrir menu">&#9776;</button>
<div class="adm-mob-overlay" id="admMobOverlay"></div>

<div class="adm-shell">

  <!-- Sidebar -->
  <aside class="adm-sidebar">
    <div class="adm-sidebar__logo">
      <img src="<?= $adminBase ?>../assets/images/logo_brasilDNA_branco.png" alt="Brasil DNA" class="adm-sidebar__logo-img" height="50">
      <div class="adm-sidebar__logo-tag">
        <?= $isSuperAdmin ? 'Super Admin' : 'Painel administrativo' ?>
      </div>
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

      <a href="<?= $adminBase ?>../clientes/" class="adm-sidebar__link <?= $paginaAtiva === 'clientes' ? 'is-active' : '' ?>">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
        </svg>
        Clientes
      </a>

      <?php if ($isSuperAdmin): ?>
      <div class="adm-sidebar__label" style="margin-top:16px;">Sistema</div>

      <a href="<?= $adminBase ?>admins.php" class="adm-sidebar__link <?= $paginaAtiva === 'admins' ? 'is-active' : '' ?>">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        Admins
      </a>
      <?php endif; ?>
    </nav>

    <div class="adm-sidebar__bottom">
      <a href="https://brasildna.com" target="_blank" rel="noopener" class="adm-sidebar__ver-site">
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
