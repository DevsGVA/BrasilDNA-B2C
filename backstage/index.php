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

$stmt  = $pdo->query(
    'SELECT p.*, COALESCE(SUM(s.visualizacoes), 0) AS views
     FROM posts p
     LEFT JOIN stats_diario s ON s.tipo = "post" AND s.referencia_id = p.id
     GROUP BY p.id
     ORDER BY p.criado_em DESC'
);
$posts = $stmt->fetchAll();

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

<?php if (count($posts) === 0): ?>
  <div class="adm-card adm-empty">
    <p>Nenhum post cadastrado ainda.</p>
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
          <th>Data</th>
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
            $data = !empty($post['criado_em'])
              ? date('d/m/Y', strtotime($post['criado_em']))
              : '—';
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
