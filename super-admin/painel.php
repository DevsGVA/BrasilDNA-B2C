<?php
require_once __DIR__ . '/../includes/conexao.php';
require_once __DIR__ . '/includes/auth.php';
exigirLogin();

$hoje    = date('Y-m-d');
$inicio  = date('Y-m-d', strtotime('-29 days'));
$inicioP = date('Y-m-d', strtotime('-59 days'));
$fimP    = date('Y-m-d', strtotime('-30 days'));

$totalViews = $totalClicks = 0;
$varViews = $varClicks = $varCtr = 0.0;
$ctr = 0.0;
$labels = [];
$serieViewsBanner = $serieViewsCliente = $serieViewsPost = $serieClicksPost = [];

for ($i = 29; $i >= 0; $i--) {
    $labels[]            = date('d/m', strtotime("-{$i} days"));
    $serieViewsBanner[]  = 0;
    $serieViewsCliente[] = 0;
    $serieViewsPost[]    = 0;
    $serieClicksPost[]   = 0;
}

$kpiBanner  = ['views' => 0, 'clicks' => 0];
$kpiCliente = ['views' => 0, 'clicks' => 0];
$kpiPost    = ['views' => 0, 'clicks' => 0];

try {
    $calcVar = function(float $atual, float $anterior): float {
        if ($anterior == 0.0) return $atual > 0 ? 100.0 : 0.0;
        return round((($atual - $anterior) / $anterior) * 100, 1);
    };

    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(visualizacoes),0) AS views,
               COALESCE(SUM(cliques),0)       AS clicks
        FROM stats_diario WHERE data BETWEEN :i AND :f
    ");
    $stmt->execute([':i' => $inicio, ':f' => $hoje]);
    $kpi = $stmt->fetch();

    $stmtP = $pdo->prepare("
        SELECT COALESCE(SUM(visualizacoes),0) AS views,
               COALESCE(SUM(cliques),0)       AS clicks
        FROM stats_diario WHERE data BETWEEN :i AND :f
    ");
    $stmtP->execute([':i' => $inicioP, ':f' => $fimP]);
    $kpiP = $stmtP->fetch();

    $stmtT = $pdo->prepare("
        SELECT tipo,
               COALESCE(SUM(visualizacoes),0) AS views,
               COALESCE(SUM(cliques),0)       AS clicks
        FROM stats_diario WHERE data BETWEEN :i AND :f GROUP BY tipo
    ");
    $stmtT->execute([':i' => $inicio, ':f' => $hoje]);
    foreach ($stmtT->fetchAll() as $row) {
        if ($row['tipo'] === 'banner')  $kpiBanner  = $row;
        if ($row['tipo'] === 'cliente') $kpiCliente = $row;
        if ($row['tipo'] === 'post')    $kpiPost    = $row;
    }

    $stmtS = $pdo->prepare("
        SELECT tipo, data,
               SUM(visualizacoes) AS views,
               SUM(cliques)       AS clicks
        FROM stats_diario WHERE data BETWEEN :i AND :f
        GROUP BY tipo, data
    ");
    $stmtS->execute([':i' => $inicio, ':f' => $hoje]);
    $serieRaw = $stmtS->fetchAll();

    $serieMap = [];
    foreach ($serieRaw as $row) {
        $serieMap[$row['tipo']][$row['data']] = $row;
    }

    $labels = $serieViewsBanner = $serieViewsCliente = $serieViewsPost = $serieClicksPost = [];
    for ($i = 29; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-{$i} days"));
        $labels[]            = date('d/m', strtotime($d));
        $serieViewsBanner[]  = (int)($serieMap['banner'][$d]['views']   ?? 0);
        $serieViewsCliente[] = (int)($serieMap['cliente'][$d]['views']  ?? 0);
        $serieViewsPost[]    = (int)($serieMap['post'][$d]['views']     ?? 0);
        $serieClicksPost[]   = (int)($serieMap['post'][$d]['clicks']    ?? 0);
    }

    $totalViews  = (int)$kpi['views'];
    $totalClicks = (int)$kpi['clicks'];
    $ctr         = $totalViews > 0 ? round($totalClicks / $totalViews * 100, 1) : 0.0;
    $ctrP        = (int)$kpiP['views'] > 0
                    ? round((int)$kpiP['clicks'] / (int)$kpiP['views'] * 100, 1)
                    : 0.0;

    $varViews  = $calcVar((float)$totalViews,  (float)$kpiP['views']);
    $varClicks = $calcVar((float)$totalClicks, (float)$kpiP['clicks']);
    $varCtr    = round($ctr - $ctrP, 1);
} catch (\Throwable $e) {}

$bannerAtivo = null;
try {
    $bannerAtivo = $pdo->query("SELECT * FROM banners WHERE ativo = 1 ORDER BY ordem ASC LIMIT 1")->fetch();
} catch (\Throwable $e) {}

$totalAdmins   = 0;
$totalClientes = 0;
try {
    $totalAdmins   = (int)$pdo->query("SELECT COUNT(*) FROM admins WHERE tipo = 'admin'")->fetchColumn();
    $totalClientes = (int)$pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
} catch (\Throwable $e) {}

$pageTitle   = 'Painel Geral';
$paginaAtiva = 'painel';
$adminBase   = '../super-admin/';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="adm-page-head">
  <h1 class="adm-page-title">Painel Geral</h1>
  <span class="painel-periodo">Últimos 30 dias</span>
</div>

<!-- KPI Cards -->
<div class="painel-kpi-grid">
  <div class="painel-kpi-card">
    <div class="painel-kpi-label">Visualizações (30 dias)</div>
    <div class="painel-kpi-value">
      <?= number_format($totalViews, 0, ',', '.') ?>
      <span class="painel-kpi-delta <?= $varViews >= 0 ? 'delta-up' : 'delta-down' ?>">
        <?= $varViews >= 0 ? '↑' : '↓' ?>&nbsp;<?= abs($varViews) ?>%
      </span>
    </div>
  </div>

  <div class="painel-kpi-card">
    <div class="painel-kpi-label">Cliques (30 dias)</div>
    <div class="painel-kpi-value">
      <?= number_format($totalClicks, 0, ',', '.') ?>
      <span class="painel-kpi-delta <?= $varClicks >= 0 ? 'delta-up' : 'delta-down' ?>">
        <?= $varClicks >= 0 ? '↑' : '↓' ?>&nbsp;<?= abs($varClicks) ?>%
      </span>
    </div>
  </div>

  <div class="painel-kpi-card">
    <div class="painel-kpi-label">Taxa de cliques (CTR)</div>
    <div class="painel-kpi-value">
      <?= $ctr ?>%
      <span class="painel-kpi-delta <?= $varCtr >= 0 ? 'delta-up' : 'delta-down' ?>">
        <?= $varCtr >= 0 ? '↑' : '↓' ?>&nbsp;<?= abs($varCtr) ?>%
      </span>
    </div>
  </div>

  <div class="painel-kpi-card">
    <div class="painel-kpi-label">Clientes</div>
    <div class="painel-kpi-value"><?= $totalClientes ?></div>
  </div>

  <div class="painel-kpi-card">
    <div class="painel-kpi-label">Admins</div>
    <div class="painel-kpi-value"><?= $totalAdmins ?></div>
  </div>
</div>

<!-- Gráfico -->
<div class="adm-card painel-chart-card">
  <div class="painel-chart-head">
    <span class="painel-chart-title">Visualizações e cliques — últimos 30 dias</span>
    <div class="painel-chart-legend">
      <span><span class="legend-dot" style="background:#036830"></span> Views banners</span>
      <span><span class="legend-dot" style="background:#f9b000"></span> Views clientes</span>
      <span><span class="legend-dot" style="background:#1a73e8"></span> Views posts</span>
      <span><span class="legend-dot" style="background:#c8102e"></span> Cliques posts</span>
    </div>
  </div>
  <canvas id="painelChart" height="90"></canvas>
</div>

<!-- Breakdown por seção -->
<div class="painel-breakdown-grid">
  <div class="painel-breakdown-card">
    <div class="painel-breakdown-icon" style="background:#e6f4ec">
      <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#036830" stroke-width="2">
        <rect x="2" y="7" width="20" height="10" rx="2"/><path d="M6 11h4M6 13h2"/>
      </svg>
    </div>
    <div>
      <div class="painel-breakdown-label">Banners</div>
      <div class="painel-breakdown-nums">
        <span><?= number_format((int)$kpiBanner['views'], 0, ',', '.') ?> views</span>
        <span class="painel-breakdown-sep">·</span>
        <span><?= number_format((int)$kpiBanner['clicks'], 0, ',', '.') ?> cliques</span>
      </div>
    </div>
    <a href="banners.php" class="painel-breakdown-link">Ver →</a>
  </div>

  <div class="painel-breakdown-card">
    <div class="painel-breakdown-icon" style="background:#fff8e6">
      <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#8a6100" stroke-width="2">
        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8z"/>
      </svg>
    </div>
    <div>
      <div class="painel-breakdown-label">Clientes <span class="painel-breakdown-total">(<?= $totalClientes ?> cadastrados)</span></div>
      <div class="painel-breakdown-nums">
        <span><?= number_format((int)$kpiCliente['views'], 0, ',', '.') ?> views</span>
      </div>
    </div>
    <a href="../clientes/" class="painel-breakdown-link">Ver →</a>
  </div>

  <div class="painel-breakdown-card">
    <div class="painel-breakdown-icon" style="background:#fdecea">
      <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#c8102e" stroke-width="2">
        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
      </svg>
    </div>
    <div>
      <div class="painel-breakdown-label">Admins</div>
      <div class="painel-breakdown-nums">
        <span><?= $totalAdmins ?> ativos</span>
      </div>
    </div>
    <a href="admins.php" class="painel-breakdown-link">Ver →</a>
  </div>

  <div class="painel-breakdown-card">
    <div class="painel-breakdown-icon" style="background:#e8f0fe">
      <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#1a73e8" stroke-width="2">
        <path d="M4 6h16M4 10h16M4 14h10"/>
      </svg>
    </div>
    <div>
      <div class="painel-breakdown-label">Posts</div>
      <div class="painel-breakdown-nums">
        <span><?= number_format((int)$kpiPost['views'], 0, ',', '.') ?> views</span>
        <span class="painel-breakdown-sep">·</span>
        <span><?= number_format((int)$kpiPost['clicks'], 0, ',', '.') ?> cliques</span>
      </div>
    </div>
    <a href="posts.php" class="painel-breakdown-link">Ver →</a>
  </div>
</div>

<!-- Banner ativo -->
<div class="painel-section-title" style="margin-top:28px">Banner ativo</div>
<div class="painel-banner-row">
  <div class="painel-banner-thumb">
    <?php if ($bannerAtivo && !empty($bannerAtivo['imagem_url'])): ?>
      <img src="../<?= htmlspecialchars($bannerAtivo['imagem_url']) ?>" alt="Banner ativo">
    <?php else: ?>
      <div class="painel-banner-thumb-empty"></div>
    <?php endif; ?>
  </div>
  <div class="painel-banner-row-actions">
    <a href="banners.php" class="btn btn-primary">Gerenciar banners</a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function () {
  var ctx = document.getElementById('painelChart').getContext('2d');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: <?= json_encode($labels) ?>,
      datasets: [
        {
          label: 'Views banners',
          data: <?= json_encode($serieViewsBanner) ?>,
          borderColor: '#036830',
          backgroundColor: 'rgba(3,104,48,0.07)',
          borderWidth: 2,
          pointRadius: 0,
          tension: 0.35,
          fill: true
        },
        {
          label: 'Views clientes',
          data: <?= json_encode($serieViewsCliente) ?>,
          borderColor: '#f9b000',
          backgroundColor: 'rgba(249,176,0,0.07)',
          borderWidth: 2,
          pointRadius: 0,
          tension: 0.35,
          fill: true
        },
        {
          label: 'Views posts',
          data: <?= json_encode($serieViewsPost) ?>,
          borderColor: '#1a73e8',
          backgroundColor: 'rgba(26,115,232,0.07)',
          borderWidth: 2,
          pointRadius: 0,
          tension: 0.35,
          fill: true
        },
        {
          label: 'Cliques posts',
          data: <?= json_encode($serieClicksPost) ?>,
          borderColor: '#c8102e',
          backgroundColor: 'rgba(200,16,46,0.05)',
          borderWidth: 2,
          pointRadius: 0,
          tension: 0.35,
          fill: false
        }
      ]
    },
    options: {
      responsive: true,
      interaction: { mode: 'index', intersect: false },
      plugins: { legend: { display: false } },
      scales: {
        x: {
          grid: { display: false },
          ticks: { font: { size: 11 }, color: '#5c6b62', maxTicksLimit: 10 }
        },
        y: {
          grid: { color: 'rgba(0,0,0,0.05)' },
          ticks: { font: { size: 11 }, color: '#5c6b62' },
          beginAtZero: true
        }
      }
    }
  });
})();
</script>

<?php require_once __DIR__ . '/includes/layout-footer.php'; ?>
