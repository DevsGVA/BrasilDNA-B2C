<?php
require_once __DIR__ . '/../includes/conexao.php';
require_once __DIR__ . '/includes/auth.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/../vendor/autoload.php';

// Se já estiver logado, vai direto ao painel
if (estaLogado()) {
    header('Location: painel.php');
    exit;
}

$mensagem = '';
$tipo     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarCSRF($_POST['csrf_token'] ?? '')) {
        $mensagem = 'Requisição inválida. Tente novamente.';
        $tipo     = 'erro';
    } else {
        $email = trim(strtolower($_POST['email'] ?? ''));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensagem = 'Informe um e-mail válido.';
            $tipo     = 'erro';
        } else {
            $stmt = $pdo->prepare('SELECT id FROM admins WHERE email = :email');
            $stmt->execute([':email' => $email]);
            $admin = $stmt->fetch();

            if ($admin) {
                $token   = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $pdo->prepare(
                    'UPDATE admins SET reset_token = :token, reset_token_expires_at = :exp WHERE id = :id'
                )->execute([':token' => $token, ':exp' => $expires, ':id' => $admin['id']]);

                $baseUrl  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
                          . '://' . $_SERVER['HTTP_HOST'];
                $resetUrl = $baseUrl . '/backstage/reset-senha.php?token=' . $token;

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = defined('SMTP_HOST')     ? SMTP_HOST     : 'mail.brasildna.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = defined('SMTP_USER')     ? SMTP_USER     : 'contact@brasildna.com';
                    $mail->Password   = defined('SMTP_PASS')     ? SMTP_PASS     : 'BrasilDNA@2025';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port       = defined('SMTP_PORT')     ? SMTP_PORT     : 465;
                    $mail->CharSet    = 'UTF-8';

                    $mail->setFrom('contact@brasildna.com', 'Brasil DNA');
                    $mail->addAddress($email);
                    $mail->Subject = 'Recuperação de senha — Brasil DNA';
                    $mail->isHTML(true);
                    $mail->Body    = "
<p>Olá,</p>
<p>Clique no link abaixo para redefinir sua senha. O link expira em <strong>1 hora</strong>.</p>
<p><a href=\"{$resetUrl}\">{$resetUrl}</a></p>
<p>Se você não solicitou a recuperação, ignore este e-mail.</p>
";
                    $mail->AltBody = "Acesse o link para redefinir sua senha: {$resetUrl}\nExpira em 1 hora.";
                    $mail->send();
                } catch (Exception $e) {
                    // Falha silenciosa — não revela erro ao usuário
                }
            }

            // Mensagem genérica para não revelar se o e-mail existe
            $mensagem = 'Se esse e-mail estiver cadastrado, você receberá as instruções em breve.';
            $tipo     = 'ok';
        }
    }
}

$csrf = gerarCSRF();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Esqueci minha senha — Brasil DNA</title>
  <link rel="stylesheet" href="../admin/assets/admin.css">
</head>
<body>

<div class="adm-auth">
  <div class="adm-auth__card">

    <div class="adm-auth__logo"><?php include __DIR__ . '/../includes/brasildna-logo.php'; ?></div>
    <div class="adm-auth__sub">Painel de Acesso</div>

    <h1 class="adm-auth__title">Recuperar senha</h1>
    <p style="margin-bottom:20px;color:var(--adm-text-muted,#6b7280);font-size:.9rem;">
      Informe seu e-mail e enviaremos um link para redefinir sua senha.
    </p>

    <?php if ($mensagem): ?>
      <div class="adm-alert <?= $tipo === 'ok' ? 'adm-alert-ok' : 'adm-alert-err' ?>" style="margin-bottom:20px;">
        <?= htmlspecialchars($mensagem) ?>
      </div>
    <?php endif; ?>

    <?php if ($tipo !== 'ok'): ?>
    <form method="POST" action="esqueci-senha.php" class="adm-form">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

      <div class="adm-form__group">
        <label class="adm-form__label" for="email">E-mail</label>
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

      <button type="submit" class="btn btn-primary" style="margin-top:4px;">Enviar link de recuperação</button>
    </form>
    <?php endif; ?>

    <div class="adm-auth__footer">
      <a href="login.php">← Voltar para o login</a>
    </div>

  </div>
</div>

</body>
</html>
