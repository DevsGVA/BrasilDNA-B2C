<?php
require_once __DIR__ . '/../includes/conexao.php';
require_once __DIR__ . '/includes/auth.php';

exigirLogin();


$stmt  = $pdo->query('SELECT * FROM posts ORDER BY criado_em DESC');
$posts = $stmt->fetchAll();

// Analytics
$_periodo = in_array($_GET['periodo'] ?? '', ['24h','7d','30d','90d','12m']) ? $_GET['periodo'] : '30d';
$_diasMap = ['24h'=>1,'7d'=>7,'30d'=>30,'90d'=>90,'12m'=>365];
$_dias    = $_diasMap[$_periodo];
$_hoje    = date('Y-m-d');
$_inicio  = date('Y-m-d', strtotime("-{$_dias} days"));

try {
    $_kpiStmt = $pdo->prepare("SELECT COALESCE(SUM(visualizacoes),0) AS views, COALESCE(SUM(cliques),0) AS clicks
        FROM stats_diario WHERE tipo='post' AND data BETWEEN :i AND :h");
    $_kpiStmt->execute([':i'=>$_inicio, ':h'=>$_hoje]);
    $_kpi = $_kpiStmt->fetch();

    $_rankStmt = $pdo->prepare("SELECT p.titulo AS nome,
        COALESCE(SUM(s.visualizacoes),0) AS views,
        COALESCE(SUM(s.cliques),0) AS clicks
        FROM posts p
        LEFT JOIN stats_diario s ON s.tipo='post' AND s.referencia_id=p.id
            AND s.data BETWEEN :i AND :h
        WHERE p.status='publicado'
        GROUP BY p.id ORDER BY views DESC");
    $_rankStmt->execute([':i'=>$_inicio, ':h'=>$_hoje]);
    $_ranking = $_rankStmt->fetchAll();
} catch (\Throwable $e) {
    $_kpi = ['views'=>0,'clicks'=>0];
    $_ranking = [];
}
$_ctr = $_kpi['views'] > 0 ? round($_kpi['clicks'] / $_kpi['views'] * 100, 1) : 0;

$pageTitle  = 'Posts';
$paginaAtiva = 'posts';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="adm-page-head">
  <h1 class="adm-page-title">Gerenciar Posts</h1>
  <a href="post-form.php" class="btn btn-primary">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
      <path d="M12 4v16m8-8H4"/>
    </svg>
    Novo Post
  </a>
</div>

<!-- Analytics -->
<div class="painel-periodo-bar" style="margin-bottom:20px;">
  <?php foreach (['24h'=>'Últimas 24h','7d'=>'Últimos 7 dias','30d'=>'Últimos 30 dias','90d'=>'Últimos 90 dias','12m'=>'Últimos 12 meses'] as $_pk=>$_pl): ?>
    <a href="index.php?periodo=<?= $_pk ?>" class="painel-periodo-btn <?= $_periodo===$_pk ? 'is-active' : '' ?>"><?= $_pl ?></a>
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
    <span class="painel-chart-title">Ranking de posts</span>
  </div>
  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead>
        <tr>
          <th>Post</th>
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

<?php if (count($posts) === 0): ?>
  <div class="adm-card adm-empty">
    <p>Nenhum post cadastrado ainda.</p>
    <a href="post-form.php" class="btn btn-primary">Criar primeiro post</a>
  </div>

<?php else: ?>
  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead>
        <tr>
          <th>Título</th>
          <th>Status</th>
          <th class="col-hide-mob">Data</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($posts as $post): ?>
          <?php
            $badgeClass = match($post['status']) {
              'publicado' => 'badge-pub',
              'rascunho'  => 'badge-draft',
              default     => 'badge-err',
            };
            $badgeLabel = match($post['status']) {
              'publicado' => 'Publicado',
              'rascunho'  => 'Rascunho',
              default     => htmlspecialchars($post['status']),
            };
            $data = !empty($post['criado_em'])
              ? date('d/m/Y', strtotime($post['criado_em']))
              : '—';
          ?>
          <tr>
            <td>
              <div class="adm-table__title"><?= htmlspecialchars($post['titulo']) ?></div>
            </td>
            <td>
              <span class="badge <?= $badgeClass ?>"><?= $badgeLabel ?></span>
            </td>
            <td class="col-hide-mob">
              <span class="adm-table__meta"><?= $data ?></span>
            </td>
            <td>
              <div class="adm-table__actions">
                <a href="post-form.php?id=<?= (int) $post['id'] ?>" class="a-edit">Editar</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/layout-footer.php'; ?>

