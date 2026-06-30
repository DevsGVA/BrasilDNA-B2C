<?php
session_start();
require_once __DIR__ . '/../includes/conexao.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: ../admin/login.php');
    exit;
}

if ($_SESSION['admin_tipo'] === 'super_admin'
    && !empty($_GET['excluir'])
    && ctype_digit($_GET['excluir'])
) {
    $del = (int) $_GET['excluir'];
    try {
        $stmt = $pdo->prepare('SELECT logo FROM clientes WHERE id = :id');
        $stmt->execute([':id' => $del]);
        $row = $stmt->fetch();
        if ($row && $row['logo']) {
            $path = __DIR__ . '/../' . $row['logo'];
            if (file_exists($path)) unlink($path);
        }
        $pdo->prepare('DELETE FROM clientes WHERE id = :id')->execute([':id' => $del]);
    } catch (\PDOException $e) {}
    header('Location: index.php');
    exit;
}

$clientes = [];
try {
    $clientes = $pdo->query('SELECT * FROM clientes ORDER BY criado_em DESC')->fetchAll();
} catch (\PDOException $e) {
    $clientes = [];
}

// Analytics
$_periodo = in_array($_GET['periodo'] ?? '', ['24h','7d','30d','90d','12m']) ? $_GET['periodo'] : '30d';
$_diasMap = ['24h'=>1,'7d'=>7,'30d'=>30,'90d'=>90,'12m'=>365];
$_dias    = $_diasMap[$_periodo];
$_hoje    = date('Y-m-d');
$_inicio  = date('Y-m-d', strtotime("-{$_dias} days"));

try {
    $_kpiStmt = $pdo->prepare("SELECT COALESCE(SUM(visualizacoes),0) AS views, COALESCE(SUM(cliques),0) AS clicks
        FROM stats_diario WHERE tipo='cliente' AND data BETWEEN :i AND :h");
    $_kpiStmt->execute([':i'=>$_inicio, ':h'=>$_hoje]);
    $_kpi = $_kpiStmt->fetch();

    $_rankStmt = $pdo->prepare("SELECT c.titulo AS nome, c.tipo AS subtipo,
        COALESCE(SUM(s.visualizacoes),0) AS views,
        COALESCE(SUM(s.cliques),0) AS clicks
        FROM clientes c
        LEFT JOIN stats_diario s ON s.tipo='cliente' AND s.referencia_id=c.id
            AND s.data BETWEEN :i AND :h
        GROUP BY c.id ORDER BY views DESC");
    $_rankStmt->execute([':i'=>$_inicio, ':h'=>$_hoje]);
    $_ranking = $_rankStmt->fetchAll();
} catch (\Throwable $e) {
    $_kpi = ['views'=>0,'clicks'=>0];
    $_ranking = [];
}
$_ctr = $_kpi['views'] > 0 ? round($_kpi['clicks'] / $_kpi['views'] * 100, 1) : 0;

$pageTitle   = 'Clientes';
$paginaAtiva = 'clientes';

$adminBase = '../admin/';
require_once __DIR__ . '/../admin/includes/sidebar.php';
?>

<div class="adm-page-head">
  <h1 class="adm-page-title">Clientes</h1>
  <a href="form.php" class="btn btn-primary">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
      <path d="M12 4v16m8-8H4"/>
    </svg>
    Novo Cliente
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
    <span class="painel-chart-title">Ranking de clientes</span>
  </div>
  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead>
        <tr>
          <th>Cliente</th>
          <th>Tipo</th>
          <th>Views</th>
          <th>Cliques</th>
          <th>CTR</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($_ranking as $_r): ?>
          <tr>
            <td><div class="adm-table__title"><?= htmlspecialchars($_r['nome']) ?></div></td>
            <td>
              <span style="font-size:.75rem;font-weight:600;padding:2px 7px;border-radius:4px;
                background:<?= $_r['subtipo']==='parceiro' ? 'rgba(0,80,180,.15)' : 'rgba(0,140,60,.15)' ?>;
                color:<?= $_r['subtipo']==='parceiro' ? '#0050b4' : '#00803a' ?>;">
                <?= $_r['subtipo']==='parceiro' ? 'Parceiro' : 'Destino' ?>
              </span>
            </td>
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

<?php if (count($clientes) === 0): ?>
  <div class="adm-card adm-empty">
    <p>Nenhum cliente cadastrado ainda.</p>
    <a href="form.php" class="btn btn-primary">Criar primeiro cliente</a>
  </div>
<?php else: ?>
  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead>
        <tr>
          <th>Logo</th>
          <th>Título</th>
          <th>Tipo</th>
          <th class="col-hide-mob">Redes Sociais</th>
          <th class="col-hide-mob">Data</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($clientes as $c): ?>
          <tr>
            <td>
              <?php if ($c['logo']): ?>
                <div style="width:72px;height:52px;background:#012a15;border-radius:8px;display:flex;align-items:center;justify-content:center;padding:6px;">
                  <img src="<?= htmlspecialchars('../' . $c['logo'], ENT_QUOTES, 'UTF-8') ?>"
                       alt="Logo" style="max-height:40px;max-width:60px;width:auto;object-fit:contain;">
                </div>
              <?php else: ?>
                <span class="adm-table__meta">—</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="adm-table__title"><?= htmlspecialchars($c['titulo']) ?></div>
            </td>
            <td>
              <?php $badge = ($c['tipo'] ?? 'destino') === 'parceiro' ? 'Parceiro' : 'Destino'; ?>
              <span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:.75rem;font-weight:600;
                background:<?= $badge === 'Destino' ? 'rgba(0,140,60,.15)' : 'rgba(0,80,180,.15)' ?>;
                color:<?= $badge === 'Destino' ? '#00803a' : '#0050b4' ?>;">
                <?= $badge ?>
              </span>
            </td>
            <td class="col-hide-mob">
              <div style="display:flex;gap:6px;flex-wrap:wrap;">
                <?php if ($c['facebook']): ?>
                  <a href="<?= htmlspecialchars($c['facebook'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="adm-table__meta" title="Facebook">FB</a>
                <?php endif; ?>
                <?php if ($c['instagram']): ?>
                  <a href="<?= htmlspecialchars($c['instagram'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="adm-table__meta" title="Instagram">IG</a>
                <?php endif; ?>
                <?php if ($c['linkedin']): ?>
                  <a href="<?= htmlspecialchars($c['linkedin'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="adm-table__meta" title="LinkedIn">LI</a>
                <?php endif; ?>
                <?php if ($c['site']): ?>
                  <a href="<?= htmlspecialchars($c['site'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="adm-table__meta" title="Site">Site</a>
                <?php endif; ?>
                <?php if ($c['youtube']): ?>
                  <a href="<?= htmlspecialchars($c['youtube'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="adm-table__meta" title="YouTube">YT</a>
                <?php endif; ?>
                <?php if (!$c['facebook'] && !$c['instagram'] && !$c['linkedin'] && !$c['site'] && !$c['youtube']): ?>
                  <span class="adm-table__meta">—</span>
                <?php endif; ?>
              </div>
            </td>
            <td class="col-hide-mob">
              <span class="adm-table__meta">
                <?= date('d/m/Y', strtotime($c['criado_em'])) ?>
              </span>
            </td>
            <td>
              <div class="adm-table__actions">
                <a href="form.php?id=<?= (int) $c['id'] ?>" class="a-edit">Editar</a>
                <?php if ($_SESSION['admin_tipo'] === 'super_admin'): ?>
                  <a href="index.php?excluir=<?= (int) $c['id'] ?>" class="a-del"
                     onclick="return confirm('Excluir este cliente?')">Excluir</a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php
if ($_SESSION['admin_tipo'] === 'super_admin') {
    require_once __DIR__ . '/../super-admin/includes/layout-footer.php';
} else {
    require_once __DIR__ . '/../admin/includes/layout-footer.php';
}
?>
