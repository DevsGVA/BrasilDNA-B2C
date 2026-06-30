<?php
require_once __DIR__ . '/../includes/conexao.php';
require_once __DIR__ . '/includes/auth.php';

exigirLogin();

$msg     = '';
$msgTipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarCSRF($_POST['csrf_token'] ?? '')) {
        $msg     = 'Requisição inválida. Tente novamente.';
        $msgTipo = 'err';
    } else {
        $nome     = trim($_POST['nome']  ?? '');
        $email    = trim($_POST['email'] ?? '');
        $senha    = $_POST['senha'] ?? '';
        $tipoNovo = in_array($_POST['tipo'] ?? '', ['admin', 'super_admin']) ? $_POST['tipo'] : 'admin';

        if (!$nome || !$email || !$senha) {
            $msg     = 'Preencha todos os campos.';
            $msgTipo = 'err';
        } else {
            $check = $pdo->prepare('SELECT id FROM admins WHERE email = :e');
            $check->execute([':e' => $email]);
            if ($check->fetch()) {
                $msg     = 'Já existe um admin com esse email.';
                $msgTipo = 'err';
            } else {
                $hash = password_hash($senha, PASSWORD_DEFAULT);
                $pdo->prepare('INSERT INTO admins (nome, email, senha, tipo) VALUES (:n, :e, :s, :t)')
                    ->execute([':n' => $nome, ':e' => $email, ':s' => $hash, ':t' => $tipoNovo]);
                $msg     = 'Admin criado com sucesso.';
                $msgTipo = 'ok';
            }
        }
    }
}

if (isset($_GET['excluir']) && ctype_digit($_GET['excluir'])) {
    $idExcluir = (int) $_GET['excluir'];
    if ($idExcluir !== (int) $_SESSION['admin_id']) {
        $pdo->prepare('DELETE FROM admins WHERE id = :id')->execute([':id' => $idExcluir]);
    }
    header('Location: admins.php');
    exit;
}

$admins = $pdo->query('SELECT id, nome, email, tipo FROM admins ORDER BY tipo DESC, nome ASC')->fetchAll();

$pageTitle   = 'Admins';
$paginaAtiva = 'admins';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="adm-page-head">
  <h1 class="adm-page-title">Gerenciar Admins</h1>
</div>

<?php if ($msg): ?>
  <div class="adm-alert <?= $msgTipo === 'ok' ? 'adm-alert-ok' : 'adm-alert-err' ?>" style="margin-bottom:20px;">
    <?= htmlspecialchars($msg) ?>
  </div>
<?php endif; ?>

<div class="adm-table-wrap" style="margin-bottom:32px;">
  <table class="adm-table">
    <thead>
      <tr>
        <th>Nome</th>
        <th>Email</th>
        <th>Tipo</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($admins as $a): ?>
        <?php $isSelf = (int)$a['id'] === (int)$_SESSION['admin_id']; ?>
        <tr>
          <td><div class="adm-table__title"><?= htmlspecialchars($a['nome']) ?></div></td>
          <td><span class="adm-table__meta"><?= htmlspecialchars($a['email']) ?></span></td>
          <td>
            <span class="badge <?= $a['tipo'] === 'super_admin' ? 'badge-pub' : 'badge-draft' ?>">
              <?= $a['tipo'] === 'super_admin' ? 'Super Admin' : 'Admin' ?>
            </span>
          </td>
          <td>
            <div class="adm-table__actions">
              <?php if (!$isSelf): ?>
                <a href="admins.php?excluir=<?= (int)$a['id'] ?>"
                   class="a-del"
                   onclick="return confirm('Excluir admin <?= htmlspecialchars(addslashes($a['nome'])) ?>?')">Excluir</a>
              <?php else: ?>
                <span class="adm-table__meta">Você</span>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="adm-card" style="max-width:480px;">
  <h2 style="font-size:16px;font-weight:600;margin-bottom:20px;">Novo Admin</h2>
  <form method="POST" class="adm-form">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(gerarCSRF()) ?>">
    <div class="adm-form__group">
      <label class="adm-form__label">Nome</label>
      <input class="adm-form__input" type="text" name="nome"
             value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required>
    </div>
    <div class="adm-form__group">
      <label class="adm-form__label">Email</label>
      <input class="adm-form__input" type="email" name="email"
             value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
    </div>
    <div class="adm-form__group">
      <label class="adm-form__label">Senha</label>
      <input class="adm-form__input" type="password" name="senha" required>
    </div>
    <div class="adm-form__group">
      <label class="adm-form__label">Tipo</label>
      <select class="adm-form__input" name="tipo">
        <option value="admin">Admin</option>
        <option value="super_admin">Super Admin</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary" style="margin-top:4px;">Criar Admin</button>
  </form>
</div>

<?php require_once __DIR__ . '/includes/layout-footer.php'; ?>
