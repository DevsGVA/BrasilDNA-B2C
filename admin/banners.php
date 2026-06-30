
<?php
require_once __DIR__ . '/../includes/conexao.php';
require_once __DIR__ . '/includes/auth.php';

exigirLogin();


$filtroParceiro = null;
$nomeFiltro     = '';
if (!empty($_GET['parceiro']) && ctype_digit($_GET['parceiro'])) {
    $filtroParceiro = (int) $_GET['parceiro'];
    $stmtNome = $pdo->prepare('SELECT nome_empresa FROM parceiros WHERE id = :id');
    $stmtNome->execute([':id' => $filtroParceiro]);
    $rowNome  = $stmtNome->fetch();
    $nomeFiltro = $rowNome ? $rowNome['nome_empresa'] : '';
    $stmt = $pdo->prepare('SELECT * FROM banners WHERE parceiro_id = :pid ORDER BY ordem ASC, criado_em DESC');
    $stmt->execute([':pid' => $filtroParceiro]);
} else {
    $stmt = $pdo->query('SELECT * FROM banners ORDER BY ordem ASC, criado_em DESC');
}
$banners = $stmt->fetchAll();

// Analytics
$_periodo  = in_array($_GET['periodo'] ?? '', ['24h','7d','30d','90d','12m']) ? $_GET['periodo'] : '30d';
$_diasMap  = ['24h'=>1,'7d'=>7,'30d'=>30,'90d'=>90,'12m'=>365];
$_dias     = $_diasMap[$_periodo];
$_hoje     = date('Y-m-d');
$_inicio   = date('Y-m-d', strtotime("-{$_dias} days"));

try {
    $_kpiStmt = $pdo->prepare("SELECT COALESCE(SUM(visualizacoes),0) AS views, COALESCE(SUM(cliques),0) AS clicks
        FROM stats_diario WHERE tipo='banner' AND data BETWEEN :i AND :h");
    $_kpiStmt->execute([':i'=>$_inicio, ':h'=>$_hoje]);
    $_kpi = $_kpiStmt->fetch();

    $_rankStmt = $pdo->prepare("SELECT b.nome_parceiro AS nome,
        COALESCE(SUM(s.visualizacoes),0) AS views,
        COALESCE(SUM(s.cliques),0) AS clicks
        FROM banners b
        LEFT JOIN stats_diario s ON s.tipo='banner' AND s.referencia_id=b.id
            AND s.data BETWEEN :i AND :h
        GROUP BY b.id ORDER BY views DESC");
    $_rankStmt->execute([':i'=>$_inicio, ':h'=>$_hoje]);
    $_ranking = $_rankStmt->fetchAll();
} catch (\Throwable $e) {
    $_kpi = ['views'=>0,'clicks'=>0];
    $_ranking = [];
}
$_ctr = $_kpi['views'] > 0 ? round($_kpi['clicks'] / $_kpi['views'] * 100, 1) : 0;

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
  <a href="banner-form.php" class="btn btn-primary">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
      <path d="M12 4v16m8-8H4"/>
    </svg>
    Novo Banner
  </a>
</div>

<!-- Analytics -->
<div class="painel-periodo-bar" style="margin-bottom:20px;">
  <?php foreach (['24h'=>'Últimas 24h','7d'=>'Últimos 7 dias','30d'=>'Últimos 30 dias','90d'=>'Últimos 90 dias','12m'=>'Últimos 12 meses'] as $_pk=>$_pl): ?>
    <a href="banners.php?periodo=<?= $_pk ?><?= $filtroParceiro ? '&parceiro='.$filtroParceiro : '' ?>"
       class="painel-periodo-btn <?= $_periodo===$_pk ? 'is-active' : '' ?>"><?= $_pl ?></a>
  <?php endforeach; ?>
</div>

<div class="painel-kpi-grid" style="margin-bottom:24px;">
  <div class="painel-kpi-card">
    <div class="painel-kpi-label">Visualizações</div>
    <div class="painel-kpi-value"><?= number_format((int)$_kpi['views'], 0, ',', '.') ?></div>
  </div>
  <div class="painel-kpi-card">
    <div class="painel-kpi-label">Cliques</div>
    <div class="painel-kpi-value"><?= number_format((int)$_kpi['clicks'], 0, ',', '.') ?></div>
  </div>
  <div class="painel-kpi-card">
    <div class="painel-kpi-label">Taxa de cliques (CTR)</div>
    <div class="painel-kpi-value"><?= $_ctr ?>%</div>
  </div>
</div>

<?php if (!empty($_ranking)): ?>
<div class="adm-card painel-chart-card" style="margin-bottom:28px;">
  <div class="painel-chart-head">
    <span class="painel-chart-title">Ranking de banners</span>
  </div>
  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead>
        <tr>
          <th>Banner</th>
          <th>Views</th>
          <th>Cliques</th>
          <th>CTR</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($_ranking as $_r): ?>
          <tr>
            <td><div class="adm-table__title"><?= htmlspecialchars($_r['nome']) ?></div></td>
            <td><?= number_format((int)$_r['views'], 0, ',', '.') ?></td>
            <td><?= number_format((int)$_r['clicks'], 0, ',', '.') ?></td>
            <td><?= $_r['views'] > 0 ? round($_r['clicks'] / $_r['views'] * 100, 1) : 0 ?>%</td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php if (count($banners) === 0): ?>
  <div class="adm-card adm-empty">
    <p>Nenhum banner cadastrado ainda.</p>
    <a href="banner-form.php" class="btn btn-primary">Criar primeiro banner</a>
  </div>
<?php else: ?>
  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead>
        <tr>
          <th>Parceiro</th>
          <th class="col-hide-mob">Título</th>
          <th>Status</th>
          <th class="col-hide-mob">Ordem</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($banners as $b): ?>
          <tr>
            <td>
              <div class="adm-table__title"><?= htmlspecialchars($b['nome_parceiro']) ?></div>
            </td>
            <td class="col-hide-mob"><?= htmlspecialchars($b['titulo'] ?? '—') ?></td>
            <td>
              <span class="badge <?= $b['ativo'] ? 'badge-pub' : 'badge-draft' ?>">
                <?= $b['ativo'] ? 'Ativo' : 'Inativo' ?>
              </span>
            </td>
            <td class="col-hide-mob"><span class="adm-table__meta"><?= (int) $b['ordem'] ?></span></td>
            <td>
              <div class="adm-table__actions">
                <a href="banner-form.php?id=<?= (int) $b['id'] ?>" class="a-edit">Editar</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/layout-footer.php'; ?>

