<?php
require_once __DIR__ . '/../includes/conexao.php';
require_once __DIR__ . '/includes/auth.php';

if (estaLogado()) {
    header('Location: index.php');
    exit;
}

$token = trim($_GET['token'] ?? '');
$erro  = '';
$ok    = false;

if ($token === '') {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM admins WHERE reset_token = :token AND reset_token_expires_at > NOW()');
$stmt->execute([':token' => $token]);
$admin = $stmt->fetch();

if (!$admin) {
    $erro = 'Link inválido ou expirado. Solicite um novo.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $admin) {
    if (!validarCSRF($_POST['csrf_token'] ?? '')) {
        die('Requisição inválida.');
    }

    $nova   = $_POST['senha']    ?? '';
    $repete = $_POST['confirma'] ?? '';

    if (strlen($nova) < 8) {
        $erro = 'Senha precisa ter pelo menos 8 caracteres.';
    } elseif ($nova !== $repete) {
        $erro = 'As senhas não coincidem.';
    } else {
        $hash = password_hash($nova, PASSWORD_DEFAULT);
        $pdo->prepare('UPDATE admins SET senha = :senha, reset_token = NULL, reset_token_expires_at = NULL WHERE id = :id')
            ->execute([':senha' => $hash, ':id' => $admin['id']]);
        $ok = true;
    }
}

$csrf = gerarCSRF();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Redefinir senha — Brasil DNA Admin</title>
  <link rel="stylesheet" href="assets/admin.css">
</head>
<body>

<div class="adm-auth">
  <div class="adm-auth__card">

    <div class="adm-auth__logo"><?php include __DIR__ . '/../includes/brasildna-logo.php'; ?></div>
    <div class="adm-auth__sub">Painel Administrativo</div>

    <h1 class="adm-auth__title">Nova senha</h1>

    <?php if ($ok): ?>
      <div class="adm-alert adm-alert-ok" style="margin-bottom:20px;">
        Senha alterada com sucesso!
      </div>
      <div class="adm-auth__footer">
        <a href="login.php">Ir para o login</a>
      </div>

    <?php elseif ($erro && !$admin): ?>
      <div class="adm-alert adm-alert-err" style="margin-bottom:20px;">
        <?= htmlspecialchars($erro) ?>
      </div>
      <div class="adm-auth__footer">
        <a href="esqueci-senha.php">Solicitar novo link</a>
      </div>

    <?php else: ?>
      <?php if ($erro): ?>
        <div class="adm-alert adm-alert-err" style="margin-bottom:20px;">
          <?= htmlspecialchars($erro) ?>
        </div>
      <?php endif; ?>

      <form method="POST" class="adm-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <div class="adm-form__group">
          <label class="adm-form__label" for="senha">Nova senha</label>
          <input class="adm-form__input" type="password" id="senha" name="senha" minlength="8" required>
        </div>
        <div class="adm-form__group">
          <label class="adm-form__label" for="confirma">Confirmar senha</label>
          <input class="adm-form__input" type="password" id="confirma" name="confirma" minlength="8" required>
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:4px;">Salvar nova senha</button>
      </form>

      <div class="adm-auth__footer">
        <a href="login.php">Voltar para o login</a>
      </div>
    <?php endif; ?>

  </div>
</div>

</body>
</html>
