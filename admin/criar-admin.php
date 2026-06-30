<?php
/**
 * Script temporário para criar o primeiro admin.
 * DELETE este arquivo após usar!
 */
require_once __DIR__ . '/../includes/conexao.php';

// ── Defina os dados do admin aqui ──────────────────────────
$nome  = 'Admin';
$email = 'admin@brasildna.com';
$senha = 'brasildna123';
// ───────────────────────────────────────────────────────────

$mensagem = '';
$tipo     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome']  ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $tipoAdmin = in_array($_POST['tipo_admin'] ?? '', ['admin', 'super_admin']) ? $_POST['tipo_admin'] : 'admin';

    if (!$nome || !$email || !$senha) {
        $mensagem = 'Preencha todos os campos.';
        $tipo = 'err';
    } else {
        $check = $pdo->prepare('SELECT id FROM admins WHERE email = :email');
        $check->execute([':email' => $email]);
        if ($check->fetch()) {
            $mensagem = "Já existe um admin com o email '{$email}'.";
            $tipo = 'warn';
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO admins (nome, email, senha, tipo) VALUES (:nome, :email, :senha, :tipo)');
            $stmt->execute([':nome' => $nome, ':email' => $email, ':senha' => $hash, ':tipo' => $tipoAdmin]);
            $mensagem = "Admin ({$tipoAdmin}) criado com sucesso! Email: {$email} — Senha: {$_POST['senha']}. Agora apague este arquivo.";
            $tipo = 'ok';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Criar Admin — Brasil DNA</title>
  <link rel="stylesheet" href="assets/admin.css">
  <style>
    .warn { background:#fff8e6; color:#8a6100; border:1px solid #f9b000; padding:12px 16px; border-radius:8px; font-size:14px; font-weight:500; margin-bottom:20px; }
    .setup-note { background:#fdecea; color:#a00c25; border:1px solid #f5c6cb; border-radius:8px; padding:12px 16px; font-size:13px; margin-bottom:24px; font-weight:600; }
  </style>
</head>
<body>
<div class="adm-auth">
  <div class="adm-auth__card">

    <div class="adm-auth__logo">Brasil DNA</div>
    <div class="adm-auth__sub">Setup — Criar primeiro admin</div>

    <div class="setup-note">⚠️ Apague este arquivo após criar o admin!</div>

    <?php if ($mensagem): ?>
      <div class="<?= $tipo === 'ok' ? 'adm-alert adm-alert-ok' : ($tipo === 'warn' ? 'warn' : 'adm-alert adm-alert-err') ?>" style="margin-bottom:20px;">
        <?= htmlspecialchars($mensagem) ?>
      </div>
    <?php endif; ?>

    <?php if ($tipo !== 'ok'): ?>
    <form method="POST" class="adm-form">
      <div class="adm-form__group">
        <label class="adm-form__label">Nome</label>
        <input class="adm-form__input" type="text" name="nome"
               value="<?= htmlspecialchars($_POST['nome'] ?? 'Admin') ?>" required>
      </div>
      <div class="adm-form__group">
        <label class="adm-form__label">Email</label>
        <input class="adm-form__input" type="email" name="email"
               value="<?= htmlspecialchars($_POST['email'] ?? 'admin@brasildna.com') ?>" required>
      </div>
      <div class="adm-form__group">
        <label class="adm-form__label">Senha</label>
        <input class="adm-form__input" type="text" name="senha"
               value="<?= htmlspecialchars($_POST['senha'] ?? 'brasildna123') ?>" required>
      </div>
      <div class="adm-form__group">
        <label class="adm-form__label">Tipo</label>
        <select class="adm-form__input" name="tipo_admin">
          <option value="admin" <?= ($_POST['tipo_admin'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
          <option value="super_admin" <?= ($_POST['tipo_admin'] ?? '') === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary" style="margin-top:4px;">Criar Admin</button>
    </form>
    <?php else: ?>
      <a href="login.php" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px;">Ir para o Login</a>
    <?php endif; ?>

  </div>
</div>
</body>
</html>

