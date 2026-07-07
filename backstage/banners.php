<?php
require_once __DIR__ . '/../includes/conexao.php';
require_once __DIR__ . '/includes/auth.php';
exigirLogin();

// Excluir banner — apenas super_admin
if (isset($_GET['excluir']) && ctype_digit($_GET['excluir'])) {
    exigirPermissao('excluir_banner');
    $stmt = $pdo->prepare('DELETE FROM banners WHERE id = :id');
    $stmt->execute([':id' => (int) $_GET['excluir']]);
    header('Location: banners.php');
    exit;
}

$filtroParceiro = null;
$nomeFiltro     = '';
if (!empty($_GET['parceiro']) && ctype_digit($_GET['parceiro'])) {
    $filtroParceiro = (int) $_GET['parceiro'];
    $stmtNome = $pdo->prepare('SELECT nome_empresa FROM parceiros WHERE id = :id');
    $stmtNome->execute([':id' => $filtroParceiro]);
    $rowNome    = $stmtNome->fetch();
    $nomeFiltro = $rowNome ? $rowNome['nome_empresa'] : '';
    $stmt = $pdo->prepare('SELECT * FROM banners WHERE parceiro_id = :pid ORDER BY ordem ASC, criado_em DESC');
    $stmt->execute([':pid' => $filtroParceiro]);
} else {
    $stmt = $pdo->query('SELECT * FROM banners ORDER BY ordem ASC, criado_em DESC');
}
$banners = $stmt->fetchAll();

$pageTitle   = 'Banners de Parceiros';
$paginaAtiva = 'banners';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="adm-page-head">
  <div>
    <?php if ($filtroParceiro): ?>
      <a href="banners.php" class="post-back-link" style="margin-bottom:6px;">← Todos os banners</a>
    <?php endif; ?>
    <h1 class="adm-page-title">
      Banners<?= $nomeFiltro ? ' — ' . htmlspecialchars($nomeFiltro) : ' de Parceiros' ?>
    </h1>
  </div>
  <?php if (canFazer('criar_banner')): ?>
  <a href="banner-form.php" class="btn btn-primary">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
      <path d="M12 4v16m8-8H4"/>
    </svg>
    Novo Banner
  </a>
  <?php endif; ?>
</div>

<?php if (count($banners) === 0): ?>
  <div class="adm-card adm-empty">
    <p>Nenhum banner cadastrado ainda.</p>
    <?php if (canFazer('criar_banner')): ?>
      <a href="banner-form.php" class="btn btn-primary">Criar primeiro banner</a>
    <?php endif; ?>
  </div>
<?php else: ?>
  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead>
        <tr>
          <th>Parceiro</th>
          <th>Desktop</th>
          <th>Mobile</th>
          <th>Status</th>
          <th>Ordem</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($banners as $b): ?>
          <tr>
            <td><div class="adm-table__title"><?= htmlspecialchars($b['nome_parceiro']) ?></div></td>
            <td>
              <?php if (!empty($b['imagem_url'])): ?>
                <img src="<?= htmlspecialchars($b['imagem_url'], ENT_QUOTES, 'UTF-8') ?>"
                     alt="desktop" loading="lazy"
                     style="height:36px;border-radius:4px;object-fit:cover;max-width:90px;">
              <?php else: ?>
                <span class="adm-table__meta" style="color:var(--text-muted)">—</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if (!empty($b['imagem_vertical_url'])): ?>
                <img src="<?= htmlspecialchars($b['imagem_vertical_url'], ENT_QUOTES, 'UTF-8') ?>"
                     alt="mobile" loading="lazy"
                     style="height:36px;border-radius:4px;object-fit:cover;max-width:40px;">
              <?php else: ?>
                <span class="adm-table__meta" style="color:var(--text-muted)">—</span>
              <?php endif; ?>
            </td>
            <td>
              <span class="badge <?= $b['ativo'] ? 'badge-pub' : 'badge-draft' ?>">
                <?= $b['ativo'] ? 'Ativo' : 'Inativo' ?>
              </span>
            </td>
            <td><span class="adm-table__meta"><?= (int) $b['ordem'] ?></span></td>
            <td>
              <div class="adm-table__actions">
                <?php if (canFazer('editar_banner')): ?>
                  <a href="banner-form.php?id=<?= (int) $b['id'] ?>" class="a-edit">Editar</a>
                <?php endif; ?>
                <?php if (canFazer('excluir_banner')): ?>
                  <a href="banners.php?excluir=<?= (int) $b['id'] ?>"
                     class="a-del"
                     onclick="return confirm('Excluir este banner?')">Excluir</a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/layout-footer.php'; ?>
