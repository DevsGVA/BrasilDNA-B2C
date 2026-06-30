<?php
require_once __DIR__ . '/../includes/conexao.php';
require_once __DIR__ . '/includes/auth.php';

if (estaLogado()) {
    header('Location: painel.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarCSRF($_POST['csrf_token'] ?? '')) {
        $erro = 'Requisição inválida. Tente novamente.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        if ($email === '' || $senha === '') {
            $erro = 'Preencha email e senha.';
        } else {
            $stmt = $pdo->prepare('SELECT * FROM admins WHERE email = :email');
            $stmt->execute([':email' => $email]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($senha, $admin['senha'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id']      = $admin['id'];
                $_SESSION['admin_nome']    = $admin['nome'];
                $_SESSION['admin_email']   = $admin['email'];
                $_SESSION['admin_tipo']    = $admin['tipo'];
                $_SESSION['last_activity'] = time();
                $destino = BASE_URL . 'admin/painel.php';
                header('Location: ' . $destino);
                exit;
            } else {
                $erro = 'Email ou senha incorretos. Tente novamente.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Entrar — Brasil DNA Admin</title>
  <link rel="stylesheet" href="assets/admin.css">
</head>
<body>

<div class="adm-auth">
  <div class="adm-auth__card">

    <div class="adm-auth__logo"><?php include __DIR__ . '/../includes/brasildna-logo.php'; ?></div>
    <div class="adm-auth__sub">Painel de Acesso</div>

    <h1 class="adm-auth__title">Entrar</h1>

    <?php if ($erro): ?>
      <div class="adm-alert adm-alert-err" style="margin-bottom:20px;">
        <?= htmlspecialchars($erro) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="login.php" class="adm-form">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(gerarCSRF()) ?>">
      <div class="adm-form__group">
        <label class="adm-form__label" for="email">Email</label>
        <input
          class="adm-form__input"
          type="email"
          id="email"
          name="email"
          placeholder="seu@email.com"
          value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
          required
          autocomplete="email"
        >
      </div>

      <div class="adm-form__group">
        <label class="adm-form__label" for="senha">Senha</label>
        <input
          class="adm-form__input"
          type="password"
          id="senha"
          name="senha"
          placeholder="••••••••"
          required
          autocomplete="current-password"
        >
      </div>

      <button type="submit" class="btn btn-primary" style="margin-top:4px;">Entrar</button>
    </form>

    <div class="adm-auth__footer">
      <a href="esqueci-senha.php">Esqueci minha senha</a>
    </div>

  </div>
</div>

</body>
</html>

