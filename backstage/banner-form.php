<?php
require_once __DIR__ . '/../includes/conexao.php';
require_once __DIR__ . '/includes/auth.php';

exigirLogin();

$id     = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : null;
$banner = null;
$erro   = '';

if ($id !== null) {
    $stmt   = $pdo->prepare('SELECT * FROM banners WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $banner = $stmt->fetch();
    if (!$banner) { header('Location: banners.php'); exit; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarCSRF($_POST['csrf_token'] ?? '')) {
        die('Requisição inválida. Recarregue a página e tente novamente.');
    }

    $nome     = trim($_POST['nome_parceiro'] ?? '');
    $titulo   = '';
    $subtexto = '';
    $botao    = '';
    $link     = trim($_POST['link_url']      ?? '');
    $ativo    = isset($_POST['ativo']) ? 1 : 0;
    $ordem    = 0;
    $logo     = $banner['logo_url']   ?? null;
    $imagem   = $banner['imagem_url'] ?? null;

    $uploadDir    = __DIR__ . '/../uploads/';
    $allowedExts  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    if (!empty($_FILES['logo_file']['tmp_name'])) {
        $ext   = strtolower(pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $_FILES['logo_file']['tmp_name']);
        finfo_close($finfo);
        if (!in_array($ext, $allowedExts) || !in_array($mime, $allowedMimes)) {
            $erro = 'Logo: formato inválido. Use JPG, PNG ou WebP.';
        } elseif ($_FILES['logo_file']['size'] > 2 * 1024 * 1024) {
            $erro = 'Logo muito grande. Máximo 2 MB.';
        } else {
            $filename = uniqid('logo_') . '.' . $ext;
            if (move_uploaded_file($_FILES['logo_file']['tmp_name'], $uploadDir . $filename)) {
                $logo = 'uploads/' . $filename;
            } else {
                $erro = 'Falha ao salvar o logo.';
            }
        }
    }

    if (empty($erro) && !empty($_FILES['bg_file']['tmp_name'])) {
        $ext   = strtolower(pathinfo($_FILES['bg_file']['name'], PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $_FILES['bg_file']['tmp_name']);
        finfo_close($finfo);
        if (!in_array($ext, $allowedExts) || !in_array($mime, $allowedMimes)) {
            $erro = 'Background: formato inválido. Use JPG, PNG ou WebP.';
        } elseif ($_FILES['bg_file']['size'] > 5 * 1024 * 1024) {
            $erro = 'Imagem de fundo muito grande. Máximo 5 MB.';
        } else {
            $filename = uniqid('bg_') . '.' . $ext;
            if (move_uploaded_file($_FILES['bg_file']['tmp_name'], $uploadDir . $filename)) {
                $imagem = 'uploads/' . $filename;
            } else {
                $erro = 'Falha ao salvar a imagem de fundo.';
            }
        }
    }

    if (empty($erro)) {
        try {
            if ($id !== null) {
                $stmt = $pdo->prepare(
                    'UPDATE banners SET nome_parceiro=:nome, logo_url=:logo, imagem_url=:imagem,
                     titulo=:titulo, subtexto=:subtexto, botao_texto=:botao,
                     link_url=:link, ativo=:ativo, ordem=:ordem WHERE id=:id'
                );
                $stmt->execute([
                    ':nome' => $nome, ':logo' => $logo, ':imagem' => $imagem,
                    ':titulo' => $titulo, ':subtexto' => $subtexto, ':botao' => $botao,
                    ':link' => $link, ':ativo' => $ativo, ':ordem' => $ordem, ':id' => $id,
                ]);
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO banners (nome_parceiro, logo_url, imagem_url, titulo, subtexto,
                     botao_texto, link_url, ativo, ordem)
                     VALUES (:nome, :logo, :imagem, :titulo, :subtexto, :botao, :link, :ativo, :ordem)'
                );
                $stmt->execute([
                    ':nome' => $nome, ':logo' => $logo, ':imagem' => $imagem,
                    ':titulo' => $titulo, ':subtexto' => $subtexto, ':botao' => $botao,
                    ':link' => $link, ':ativo' => $ativo, ':ordem' => $ordem,
                ]);
            }
        } catch (\PDOException $e) {
            error_log('[BrasilDNA] super banner-form: ' . $e->getMessage());
            $erro = 'Erro ao salvar. Tente novamente.';
        }
        if (empty($erro)) {
            header('Location: banners.php');
            exit;
        }
    }
}

$vNome     = $_POST['nome_parceiro'] ?? ($banner['nome_parceiro'] ?? '');
$vLink     = $_POST['link_url']      ?? ($banner['link_url']      ?? '');
$vAtivo    = isset($_POST['ativo'])  ? (int) $_POST['ativo'] : (int) ($banner['ativo'] ?? 1);
$vLogo     = $banner['logo_url']     ?? '';
$vImagem   = $banner['imagem_url']   ?? '';

$pageTitle   = $id !== null ? 'Editar Banner' : 'Novo Banner';
$paginaAtiva = 'banners';
require_once __DIR__ . '/includes/sidebar.php';
?>

<a href="banners.php" class="post-back-link">
  <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
    <path d="M19 12H5M5 12l7-7M5 12l7 7"/>
  </svg>
  Voltar para Banners
</a>

<div class="adm-page-head">
  <h1 class="adm-page-title"><?= htmlspecialchars($pageTitle) ?></h1>
  <a href="../index.php" target="_blank" class="btn btn-ghost btn-ver-site">
    Ver site
    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6M15 3h6v6M10 14L21 3"/>
    </svg>
  </a>
</div>

<?php if ($erro): ?>
  <div class="adm-alert adm-alert-err" style="margin-bottom:20px;"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(gerarCSRF()) ?>">

  <div class="post-form-layout">

    <div class="post-main-card">

      <div class="adm-form__group">
        <label class="adm-form__label" for="nome_parceiro">Nome do parceiro <span style="color:var(--action)">*</span></label>
        <input class="adm-form__input" type="text" id="nome_parceiro" name="nome_parceiro"
               placeholder="Ex: Compass Brazil"
               value="<?= htmlspecialchars($vNome) ?>" required>
      </div>

      <div class="adm-form__group">
        <label class="adm-form__label" for="link_url">URL de destino <span style="color:var(--action)">*</span></label>
        <input class="adm-form__input" type="url" id="link_url" name="link_url"
               placeholder="https://..."
               value="<?= htmlspecialchars($vLink) ?>" required>
      </div>

    </div>

    <div class="post-side-card">

      <div class="post-side-section">
        <div class="post-side-label">Logo do parceiro</div>
        <?php if ($vLogo): ?>
          <img id="logo-preview" src="<?= htmlspecialchars($vLogo, ENT_QUOTES, 'UTF-8') ?>"
               alt="Logo atual" style="width:100%;border-radius:8px;margin-bottom:10px;object-fit:contain;max-height:80px;background:#012a15;padding:8px;">
        <?php else: ?>
          <img id="logo-preview" src="" alt="" style="display:none;width:100%;border-radius:8px;margin-bottom:10px;object-fit:contain;max-height:80px;background:#012a15;padding:8px;">
        <?php endif; ?>
        <label class="post-img-upload" for="logo-upload">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
          </svg>
          <span id="logo-label">Enviar logo</span>
        </label>
        <input type="file" id="logo-upload" name="logo_file" accept="image/*" style="display:none;">
      </div>

      <div class="post-side-section">
        <div class="post-side-label">Imagem de fundo</div>
        <?php if ($vImagem): ?>
          <img id="bg-preview" src="<?= htmlspecialchars($vImagem, ENT_QUOTES, 'UTF-8') ?>"
               alt="Fundo atual" style="width:100%;border-radius:8px;margin-bottom:10px;object-fit:cover;max-height:100px;">
        <?php else: ?>
          <img id="bg-preview" src="" alt="" style="display:none;width:100%;border-radius:8px;margin-bottom:10px;object-fit:cover;max-height:100px;">
        <?php endif; ?>
        <label class="post-img-upload" for="bg-upload">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
          </svg>
          <span id="bg-label">Enviar imagem de fundo</span>
        </label>
        <input type="file" id="bg-upload" name="bg_file" accept="image/*" style="display:none;">
      </div>

      <div class="post-side-section">
        <div class="post-side-label">Status</div>
        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:.9rem;color:var(--text);">
          <input type="checkbox" name="ativo" value="1" <?= $vAtivo ? 'checked' : '' ?>
                 style="width:18px;height:18px;accent-color:var(--action);cursor:pointer;">
          Exibir no site
        </label>
      </div>

      <div class="post-side-actions">
        <button type="submit" class="btn btn-primary btn-full">Salvar Banner</button>
        <a href="banners.php" class="btn btn-ghost btn-full">Cancelar</a>
      </div>

    </div>
  </div>
</form>

<script>
function bindUploadPreview(inputId, previewId, labelId) {
  document.getElementById(inputId).addEventListener('change', function() {
    var file = this.files[0];
    if (!file) return;
    var preview = document.getElementById(previewId);
    var label   = document.getElementById(labelId);
    var reader  = new FileReader();
    reader.onload = function(e) {
      preview.src = e.target.result;
      preview.style.display = 'block';
      label.textContent = file.name;
    };
    reader.readAsDataURL(file);
  });
}
bindUploadPreview('logo-upload', 'logo-preview', 'logo-label');
bindUploadPreview('bg-upload',   'bg-preview',   'bg-label');
</script>

<?php require_once __DIR__ . '/includes/layout-footer.php'; ?>
