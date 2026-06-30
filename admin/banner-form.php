<?php
require_once __DIR__ . '/../includes/conexao.php';
require_once __DIR__ . '/includes/auth.php';

exigirLogin();

/* Garante coluna imagem_vertical_url existe */
try {
    $pdo->exec("ALTER TABLE banners ADD COLUMN imagem_vertical_url VARCHAR(500) NULL AFTER imagem_url");
} catch (\PDOException $e) { /* já existe — ignora */ }

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
    $logo     = $banner['logo_url']            ?? null;
    $imagem   = $banner['imagem_url']          ?? null;
    $imgVert  = $banner['imagem_vertical_url'] ?? null;

    $uploadDir    = __DIR__ . '/../uploads/banners/';
    $allowedExts  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    /* Helper upload */
    $doUpload = function(string $field, int $maxMb, string $prefix) use (&$erro, $uploadDir, $allowedExts, $allowedMimes): ?string {
        if (empty($_FILES[$field]['tmp_name'])) return null;
        $ext   = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $_FILES[$field]['tmp_name']);
        finfo_close($finfo);
        if (!in_array($ext, $allowedExts) || !in_array($mime, $allowedMimes)) {
            $erro = ucfirst($field) . ': formato inválido. Use JPG, PNG ou WebP.'; return null;
        }
        if ($_FILES[$field]['size'] > $maxMb * 1024 * 1024) {
            $erro = ucfirst($field) . ': máximo ' . $maxMb . ' MB.'; return null;
        }
        $filename = uniqid($prefix) . '.' . $ext;
        if (!move_uploaded_file($_FILES[$field]['tmp_name'], $uploadDir . $filename)) {
            $erro = 'Falha ao salvar arquivo.'; return null;
        }
        return 'uploads/banners/' . $filename;
    };

    if ($res = $doUpload('logo_file', 2, 'logo_'))                    $logo    = $res;
    if (empty($erro) && $res = $doUpload('bg_file',   5, 'bg_'))      $imagem  = $res;
    if (empty($erro) && $res = $doUpload('vert_file', 5, 'vert_'))    $imgVert = $res;

    // URL como alternativa ao upload
    if (empty($erro) && empty($_FILES['bg_file']['tmp_name'])) {
        $bgUrl = trim($_POST['bg_url'] ?? '');
        if ($bgUrl !== '' && filter_var($bgUrl, FILTER_VALIDATE_URL)) $imagem = $bgUrl;
    }
    if (empty($erro) && empty($_FILES['vert_file']['tmp_name'])) {
        $vertUrl = trim($_POST['vert_url'] ?? '');
        if ($vertUrl !== '' && filter_var($vertUrl, FILTER_VALIDATE_URL)) $imgVert = $vertUrl;
    }

    if (empty($erro)) {
        try {
            if ($id !== null) {
                $stmt = $pdo->prepare(
                    'UPDATE banners SET nome_parceiro=:nome, logo_url=:logo, imagem_url=:imagem,
                     imagem_vertical_url=:imgvert,
                     titulo=:titulo, subtexto=:subtexto, botao_texto=:botao,
                     link_url=:link, ativo=:ativo, ordem=:ordem WHERE id=:id'
                );
                $stmt->execute([
                    ':nome' => $nome, ':logo' => $logo, ':imagem' => $imagem,
                    ':imgvert' => $imgVert,
                    ':titulo' => $titulo, ':subtexto' => $subtexto, ':botao' => $botao,
                    ':link' => $link, ':ativo' => $ativo, ':ordem' => $ordem, ':id' => $id,
                ]);
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO banners (nome_parceiro, logo_url, imagem_url, imagem_vertical_url,
                     titulo, subtexto, botao_texto, link_url, ativo, ordem)
                     VALUES (:nome, :logo, :imagem, :imgvert, :titulo, :subtexto, :botao, :link, :ativo, :ordem)'
                );
                $stmt->execute([
                    ':nome' => $nome, ':logo' => $logo, ':imagem' => $imagem,
                    ':imgvert' => $imgVert,
                    ':titulo' => $titulo, ':subtexto' => $subtexto, ':botao' => $botao,
                    ':link' => $link, ':ativo' => $ativo, ':ordem' => $ordem,
                ]);
            }
        } catch (\PDOException $e) {
            error_log('[BrasilDNA] banner-form: ' . $e->getMessage());
            $erro = 'Erro ao salvar. Tente novamente.';
        }
        if (empty($erro)) {
            header('Location: banners.php');
            exit;
        }
    }
}

$vNome    = $_POST['nome_parceiro'] ?? ($banner['nome_parceiro'] ?? '');
$vLink    = $_POST['link_url']      ?? ($banner['link_url']      ?? '');
$vAtivo   = isset($_POST['ativo'])  ? (int) $_POST['ativo'] : (int) ($banner['ativo'] ?? 1);
$fixPath  = fn($p) => $p && !str_starts_with($p, 'http') && !str_starts_with($p, '/') ? '../' . $p : $p;
$vLogo    = $fixPath($banner['logo_url']            ?? '');
$vImagem  = $fixPath($banner['imagem_url']          ?? '');
$vImgVert = $fixPath($banner['imagem_vertical_url'] ?? '');

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

    <!-- Coluna principal -->
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

    <!-- Painel lateral -->
    <div class="post-side-card">

      <!-- Logo -->
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

      <!-- Banner Horizontal (desktop) -->
      <div class="post-side-section">
        <div class="post-side-label">
          Banner Horizontal
          <span class="banner-size-hint">🖥 Desktop &amp; tablet — <strong>1400 × 148 px</strong> (formato paisagem)</span>
        </div>
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
          <span id="bg-label">Enviar banner horizontal</span>
        </label>
        <input type="file" id="bg-upload" name="bg_file" accept="image/*" style="display:none;">
        <div style="margin-top:8px;">
          <input class="adm-form__input" type="url" name="bg_url" id="bg-url"
                 placeholder="Ou cole a URL do banner (https://...)"
                 value="<?= htmlspecialchars(filter_var($vImagem, FILTER_VALIDATE_URL) ? $vImagem : '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
      </div>

      <!-- Banner Vertical (mobile) -->
      <div class="post-side-section">
        <div class="post-side-label">
          Banner Mobile (quadrada)
          <span class="banner-size-hint">📱 Mobile — <strong>600 × 600 px</strong> (quadrada, proporção 1:1)</span>
        </div>
        <?php if ($vImgVert): ?>
          <img id="vert-preview" src="<?= htmlspecialchars($vImgVert, ENT_QUOTES, 'UTF-8') ?>"
               alt="Banner vertical atual" style="width:100%;border-radius:8px;margin-bottom:10px;object-fit:cover;max-height:120px;">
        <?php else: ?>
          <img id="vert-preview" src="" alt="" style="display:none;width:100%;border-radius:8px;margin-bottom:10px;object-fit:cover;max-height:120px;">
        <?php endif; ?>
        <label class="post-img-upload" for="vert-upload">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
          </svg>
          <span id="vert-label">Enviar banner vertical</span>
        </label>
        <input type="file" id="vert-upload" name="vert_file" accept="image/*" style="display:none;">
        <div style="margin-top:8px;">
          <input class="adm-form__input" type="url" name="vert_url" id="vert-url"
                 placeholder="Ou cole a URL do banner mobile (https://...)"
                 value="<?= htmlspecialchars(filter_var($vImgVert, FILTER_VALIDATE_URL) ? $vImgVert : '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <p style="font-size:11px;color:var(--text-sec);margin-top:6px;">
          Se não enviado, o banner horizontal será usado no mobile também.
        </p>
      </div>

      <!-- Status -->
      <div class="post-side-section">
        <div class="post-side-label">Status</div>
        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:.9rem;color:var(--text);">
          <input type="checkbox" name="ativo" value="1" <?= $vAtivo ? 'checked' : '' ?>
                 style="width:18px;height:18px;accent-color:var(--action);cursor:pointer;">
          Exibir no site
        </label>
      </div>

      <!-- Ações -->
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
bindUploadPreview('vert-upload', 'vert-preview', 'vert-label');

document.getElementById('bg-url').addEventListener('input', function() {
  var url = this.value.trim();
  var preview = document.getElementById('bg-preview');
  if (url) { preview.src = url; preview.style.display = 'block'; }
});
document.getElementById('vert-url').addEventListener('input', function() {
  var url = this.value.trim();
  var preview = document.getElementById('vert-preview');
  if (url) { preview.src = url; preview.style.display = 'block'; }
});
</script>

<?php require_once __DIR__ . '/includes/layout-footer.php'; ?>
