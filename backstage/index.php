<?php
require_once __DIR__ . '/../includes/conexao.php';
require_once __DIR__ . '/includes/auth.php';
exigirLogin();

// Excluir post — apenas super_admin
if (isset($_GET['excluir']) && ctype_digit($_GET['excluir'])) {
    exigirPermissao('excluir_post');
    $stmt = $pdo->prepare('DELETE FROM posts WHERE id = :id');
    $stmt->execute([':id' => (int) $_GET['excluir']]);
    header('Location: index.php');
    exit;
}

$filtroTitulo = trim($_GET['titulo'] ?? '');
$filtroMes    = isset($_GET['mes']) && ctype_digit($_GET['mes']) && $_GET['mes'] >= 1 && $_GET['mes'] <= 12 ? (int) $_GET['mes'] : 0;
$filtroAno    = isset($_GET['ano']) && ctype_digit($_GET['ano']) ? (int) $_GET['ano'] : 0;

$where  = [];
$params = [];

if ($filtroTitulo !== '') {
    $where[]           = 'p.titulo LIKE :titulo';
    $params[':titulo']  = '%' . $filtroTitulo . '%';
}
if ($filtroMes > 0) {
    $where[]         = 'MONTH(COALESCE(p.data_publicacao, p.criado_em)) = :mes';
    $params[':mes']  = $filtroMes;
}
if ($filtroAno > 0) {
    $where[]         = 'YEAR(COALESCE(p.data_publicacao, p.criado_em)) = :ano';
    $params[':ano']  = $filtroAno;
}

$sql = 'SELECT p.*, COALESCE(SUM(s.visualizacoes), 0) AS views
        FROM posts p
        LEFT JOIN stats_diario s ON s.tipo = "post" AND s.referencia_id = p.id';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' GROUP BY p.id ORDER BY COALESCE(p.data_publicacao, p.criado_em) DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();

$meses = [
    1=>'Janeiro', 2=>'Fevereiro', 3=>'Março', 4=>'Abril', 5=>'Maio', 6=>'Junho',
    7=>'Julho', 8=>'Agosto', 9=>'Setembro', 10=>'Outubro', 11=>'Novembro', 12=>'Dezembro',
];
$anoAtual = (int) date('Y');
$anos     = range($anoAtual, $anoAtual - 5);

$pageTitle   = 'Posts';
$paginaAtiva = 'posts';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="adm-page-head">
  <h1 class="adm-page-title">Gerenciar Posts</h1>
  <?php if (canFazer('criar_post')): ?>
  <a href="post-form.php" class="btn btn-primary">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
      <path d="M12 4v16m8-8H4"/>
    </svg>
    Novo Post
  </a>
  <?php endif; ?>
</div>

<form method="get" class="adm-filters">
  <input type="text" name="titulo" placeholder="Buscar por título…" value="<?= htmlspecialchars($filtroTitulo) ?>"
         class="adm-form__input adm-filters__search">
  <select name="mes" class="adm-form__select adm-filters__select" data-custom-select>
    <option value="">Mês</option>
    <?php foreach ($meses as $num => $nome): ?>
      <option value="<?= $num ?>" <?= $filtroMes === $num ? 'selected' : '' ?>><?= $nome ?></option>
    <?php endforeach; ?>
  </select>
  <select name="ano" class="adm-form__select adm-filters__select" data-custom-select>
    <option value="">Ano</option>
    <?php foreach ($anos as $ano): ?>
      <option value="<?= $ano ?>" <?= $filtroAno === $ano ? 'selected' : '' ?>><?= $ano ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="btn btn-primary">Filtrar</button>
  <?php if ($filtroTitulo !== '' || $filtroMes > 0 || $filtroAno > 0): ?>
    <a href="index.php" class="btn btn-ghost">Limpar</a>
  <?php endif; ?>
</form>

<?php if (count($posts) === 0): ?>
  <div class="adm-card adm-empty">
    <p>Nenhum post encontrado.</p>
    <?php if (canFazer('criar_post')): ?>
      <a href="post-form.php" class="btn btn-primary">Criar primeiro post</a>
    <?php endif; ?>
  </div>

<?php else: ?>
  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead>
        <tr>
          <th>Título</th>
          <th>Status</th>
          <th>Data de publicação</th>
          <th>Views</th>
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
            $dataRef = $post['data_publicacao'] ?? $post['criado_em'] ?? '';
            $data    = $dataRef ? date('d/m/Y', strtotime($dataRef)) : '—';
          ?>
          <tr>
            <td>
              <div class="adm-table__title"><?= htmlspecialchars($post['titulo']) ?></div>
            </td>
            <td><span class="badge <?= $badgeClass ?>"><?= $badgeLabel ?></span></td>
            <td><span class="adm-table__meta"><?= $data ?></span></td>
            <td><span class="adm-table__meta"><?= number_format((int)$post['views'], 0, ',', '.') ?></span></td>
            <td>
              <div class="adm-table__actions">
                <?php if (canFazer('editar_post')): ?>
                  <a href="post-form.php?id=<?= (int) $post['id'] ?>" class="a-edit">Editar</a>
                <?php endif; ?>
                <?php if (canFazer('excluir_post')): ?>
                  <a href="index.php?excluir=<?= (int) $post['id'] ?>"
                     class="a-del"
                     onclick="return confirm('Excluir este post?')">Excluir</a>
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
