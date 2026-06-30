<?php
require_once __DIR__ . '/../includes/conexao.php';
require_once __DIR__ . '/includes/auth.php';

exigirLogin();

if (isset($_GET['aprovar']) && ctype_digit($_GET['aprovar'])) {
    $pdo->prepare('UPDATE parceiros SET status = :s WHERE id = :id')
        ->execute([':s' => 'aprovado', ':id' => (int) $_GET['aprovar']]);
    header('Location: parceiros.php');
    exit;
}

if (isset($_GET['rejeitar']) && ctype_digit($_GET['rejeitar'])) {
    $pdo->prepare('UPDATE parceiros SET status = :s WHERE id = :id')
        ->execute([':s' => 'rejeitado', ':id' => (int) $_GET['rejeitar']]);
    header('Location: parceiros.php');
    exit;
}

if (isset($_GET['excluir']) && ctype_digit($_GET['excluir'])) {
    $pdo->prepare('DELETE FROM parceiros WHERE id = :id')
        ->execute([':id' => (int) $_GET['excluir']]);
    header('Location: parceiros.php');
    exit;
}

$stmt = $pdo->query(
    'SELECT p.*, COUNT(b.id) AS total_banners
     FROM parceiros p
     LEFT JOIN banners b ON b.parceiro_id = p.id
     GROUP BY p.id
     ORDER BY
       CASE p.status WHEN \'pendente\' THEN 0 WHEN \'aprovado\' THEN 1 ELSE 2 END,
       p.criado_em DESC'
);
$parceiros = $stmt->fetchAll();

$pageTitle   = 'Parceiros';
$paginaAtiva = 'parceiros';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="adm-page-head">
  <h1 class="adm-page-title">Parceiros</h1>
</div>

<?php if (empty($parceiros)): ?>
  <div class="adm-card adm-empty">
    <p>Nenhum parceiro cadastrado ainda.</p>
  </div>
<?php else: ?>
  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead>
        <tr>
          <th>Empresa</th>
          <th>E-mail</th>
          <th>Status</th>
          <th>Banners</th>
          <th>Cadastro</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($parceiros as $p): ?>
          <?php
            $pid    = (int) $p['id'];
            $status = $p['status'];
            $nome   = htmlspecialchars($p['nome_empresa']);
            $email  = htmlspecialchars($p['email']);
            $data   = date('d/m/Y', strtotime($p['criado_em']));
          ?>
          <tr>
            <td><div class="adm-table__title"><?= $nome ?></div></td>
            <td><span class="adm-table__meta"><?= $email ?></span></td>
            <td>
              <?php if ($status === 'pendente'): ?>
                <span class="badge badge-draft">Pendente</span>
              <?php elseif ($status === 'aprovado'): ?>
                <span class="badge badge-pub">Aprovado</span>
              <?php else: ?>
                <span class="badge badge-err">Rejeitado</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ((int)$p['total_banners'] > 0): ?>
                <a href="banners.php?parceiro=<?= $pid ?>" style="color:var(--link);font-size:13px;">
                  <?= (int)$p['total_banners'] ?> banner(s)
                </a>
              <?php else: ?>
                <span class="adm-table__meta">—</span>
              <?php endif; ?>
            </td>
            <td><span class="adm-table__meta"><?= $data ?></span></td>
            <td>
              <div class="adm-table__actions">
                <?php if ($status === 'pendente'): ?>
                  <a href="parceiros.php?aprovar=<?= $pid ?>" class="a-edit">Aprovar</a>
                  <a href="parceiros.php?rejeitar=<?= $pid ?>" class="a-del">Rejeitar</a>
                <?php elseif ($status === 'aprovado'): ?>
                  <a href="banners.php?parceiro=<?= $pid ?>" class="a-edit">Ver Banners</a>
                  <a href="parceiros.php?rejeitar=<?= $pid ?>" class="a-del">Rejeitar</a>
                <?php else: ?>
                  <a href="parceiros.php?aprovar=<?= $pid ?>" class="a-edit">Aprovar</a>
                <?php endif; ?>
                <a href="parceiros.php?excluir=<?= $pid ?>"
                   class="a-del"
                   onclick="return confirm('Excluir parceiro <?= addslashes($p['nome_empresa']) ?>? Os banners associados ficarão sem dono.')">
                  Excluir
                </a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/layout-footer.php'; ?>
