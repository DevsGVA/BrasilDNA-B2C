<?php
require_once __DIR__ . '/../includes/conexao.php';
require_once __DIR__ . '/includes/auth.php';

exigirLogin();

if (isset($_GET['excluir']) && ctype_digit($_GET['excluir'])) {
    $stmt = $pdo->prepare('DELETE FROM posts WHERE id = :id');
    $stmt->execute([':id' => (int) $_GET['excluir']]);
    header('Location: index.php');
    exit;
}

$stmt  = $pdo->query('SELECT * FROM posts ORDER BY criado_em DESC');
$posts = $stmt->fetchAll();

$pageTitle   = 'Posts';
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
          <th>Data</th>
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
            <td>
              <span class="adm-table__meta"><?= $data ?></span>
            </td>
            <td>
              <div class="adm-table__actions">
                <a href="post-form.php?id=<?= (int) $post['id'] ?>" class="a-edit">Editar</a>
                <a href="index.php?excluir=<?= (int) $post['id'] ?>"
                   class="a-del"
                   onclick="return confirm('Excluir este post?')">Excluir</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/layout-footer.php'; ?>
