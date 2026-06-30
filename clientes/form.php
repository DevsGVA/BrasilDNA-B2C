<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/conexao.php';

require_once __DIR__ . '/../admin/includes/auth.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: ../admin/login.php');
    exit;
}

$id      = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : null;
$cliente = null;
$erro    = '';

if ($id !== null) {
    $stmt    = $pdo->prepare('SELECT * FROM clientes WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $cliente = $stmt->fetch();
    if (!$cliente) { header('Location: index.php'); exit; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarCSRF($_POST['csrf_token'] ?? '')) {
        die('Requisição inválida. Recarregue a página e tente novamente.');
    }

    $titulo    = trim($_POST['titulo']     ?? '');
    $tipo      = in_array($_POST['tipo'] ?? '', ['destino', 'parceiro']) ? $_POST['tipo'] : 'destino';
    $regiao    = trim($_POST['regiao']     ?? '');
    $descricao = trim($_POST['descricao']  ?? '');
    $iframe    = trim($_POST['iframe']     ?? '');
    // Aceita URL do YouTube (youtu.be ou youtube.com) e converte para embed URL
    if ($iframe !== '' && !str_starts_with($iframe, '<')) {
        if (preg_match('#(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([a-zA-Z0-9_-]{11})#', $iframe, $_m)) {
            $iframe = 'https://www.youtube.com/embed/' . $_m[1];
        }
    }
    $facebook  = trim($_POST['facebook']   ?? '');
    $instagram = trim($_POST['instagram']  ?? '');
    $linkedin  = trim($_POST['linkedin']   ?? '');
    $site      = trim($_POST['site']       ?? '');
    $youtube   = trim($_POST['youtube']    ?? '');
    $link_guia = trim($_POST['link_guia']  ?? '');
    $logo         = $cliente['logo']         ?? null;
    $imagem_fundo = $cliente['imagem_fundo'] ?? null;

    if ($titulo === '') {
        $erro = 'O título do cliente é obrigatório.';
    }

    if (empty($erro) && !empty($_FILES['logo_file']['tmp_name'])) {
        $allowedExts  = ['jpg', 'jpeg', 'png', 'webp'];
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        $ext          = strtolower(pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION));
        $finfo        = finfo_open(FILEINFO_MIME_TYPE);
        $mime         = finfo_file($finfo, $_FILES['logo_file']['tmp_name']);
        finfo_close($finfo);
        $allowed = $allowedExts; // mantém compatibilidade abaixo
        if (!in_array($ext, $allowedExts) || !in_array($mime, $allowedMimes)) {
            $erro = 'Logo: formato inválido. Use JPG, PNG ou WebP.';
        } elseif ($_FILES['logo_file']['size'] > 2 * 1024 * 1024) {
            $erro = 'Logo muito grande. Máximo 2 MB.';
        } else {
            $filename  = uniqid('cliente_') . '.' . $ext;
            $uploadDir = __DIR__ . '/../uploads/clientes/';
            if (move_uploaded_file($_FILES['logo_file']['tmp_name'], $uploadDir . $filename)) {
                $logo = 'uploads/clientes/' . $filename;
            } else {
                $erro = 'Falha ao salvar o logo.';
            }
        }
    }

    if (empty($erro) && !empty($_FILES['imagem_fundo_file']['tmp_name'])) {
        $allowedExtsF  = ['jpg', 'jpeg', 'png', 'webp'];
        $allowedMimesF = ['image/jpeg', 'image/png', 'image/webp'];
        $extF = strtolower(pathinfo($_FILES['imagem_fundo_file']['name'], PATHINFO_EXTENSION));
        $finfoF = finfo_open(FILEINFO_MIME_TYPE);
        $mimeF  = finfo_file($finfoF, $_FILES['imagem_fundo_file']['tmp_name']);
        finfo_close($finfoF);
        if (!in_array($extF, $allowedExtsF) || !in_array($mimeF, $allowedMimesF)) {
            $erro = 'Imagem de fundo: formato inválido. Use JPG, PNG ou WebP.';
        } elseif ($_FILES['imagem_fundo_file']['size'] > 5 * 1024 * 1024) {
            $erro = 'Imagem de fundo muito grande. Máximo 5 MB.';
        } else {
            $fFilename = uniqid('fundo_') . '.' . $extF;
            $uploadDir = __DIR__ . '/../uploads/clientes/';
            if (move_uploaded_file($_FILES['imagem_fundo_file']['tmp_name'], $uploadDir . $fFilename)) {
                if ($imagem_fundo && file_exists(__DIR__ . '/../' . $imagem_fundo)) {
                    @unlink(__DIR__ . '/../' . $imagem_fundo);
                }
                $imagem_fundo = 'uploads/clientes/' . $fFilename;
            } else {
                $erro = 'Falha ao salvar a imagem de fundo.';
            }
        }
    }

    if (empty($erro)) {
        try {
            if ($id !== null) {
                $stmt = $pdo->prepare(
                    'UPDATE clientes SET titulo=:titulo, tipo=:tipo, regiao=:regiao, logo=:logo, descricao=:descricao,
                     iframe=:iframe, imagem_fundo=:imagem_fundo, facebook=:facebook, instagram=:instagram,
                     linkedin=:linkedin, site=:site, youtube=:youtube, link_guia=:link_guia
                     WHERE id=:id'
                );
                $stmt->execute([
                    ':titulo' => $titulo, ':tipo' => $tipo, ':regiao' => $regiao,
                    ':logo' => $logo, ':descricao' => $descricao,
                    ':iframe' => $iframe, ':imagem_fundo' => $imagem_fundo,
                    ':facebook' => $facebook, ':instagram' => $instagram,
                    ':linkedin' => $linkedin, ':site' => $site, ':youtube' => $youtube,
                    ':link_guia' => $link_guia, ':id' => $id,
                ]);
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO clientes (titulo, tipo, regiao, logo, descricao, iframe, imagem_fundo, facebook, instagram,
                     linkedin, site, youtube, link_guia)
                     VALUES (:titulo, :tipo, :regiao, :logo, :descricao, :iframe, :imagem_fundo, :facebook, :instagram,
                     :linkedin, :site, :youtube, :link_guia)'
                );
                $stmt->execute([
                    ':titulo' => $titulo, ':tipo' => $tipo, ':regiao' => $regiao,
                    ':logo' => $logo, ':descricao' => $descricao,
                    ':iframe' => $iframe, ':imagem_fundo' => $imagem_fundo,
                    ':facebook' => $facebook, ':instagram' => $instagram,
                    ':linkedin' => $linkedin, ':site' => $site, ':youtube' => $youtube,
                    ':link_guia' => $link_guia,
                ]);
            }
        } catch (\PDOException $e) {
            error_log('[BrasilDNA] clientes/form: ' . $e->getMessage());
            $erro = 'Erro ao salvar. Tente novamente.';
        }
        if (empty($erro)) {
            header('Location: index.php');
            exit;
        }
    }
}

$vTitulo    = $_POST['titulo']     ?? ($cliente['titulo']     ?? '');
$vTipo      = $_POST['tipo']       ?? ($cliente['tipo']       ?? 'destino');
$vRegiao    = $_POST['regiao']     ?? ($cliente['regiao']     ?? '');
$vDescricao = $_POST['descricao']  ?? ($cliente['descricao']  ?? '');
$vIframe    = $_POST['iframe']     ?? ($cliente['iframe']     ?? '');
$vFacebook  = $_POST['facebook']   ?? ($cliente['facebook']   ?? '');
$vInstagram = $_POST['instagram']  ?? ($cliente['instagram']  ?? '');
$vLinkedin  = $_POST['linkedin']   ?? ($cliente['linkedin']   ?? '');
$vSite      = $_POST['site']       ?? ($cliente['site']       ?? '');
$vYoutube   = $_POST['youtube']    ?? ($cliente['youtube']    ?? '');
$vLinkGuia  = $_POST['link_guia']  ?? ($cliente['link_guia']  ?? '');
$vLogo        = $cliente['logo']         ?? '';
$vImagemFundo = $cliente['imagem_fundo'] ?? '';

$pageTitle   = $id !== null ? 'Editar Cliente' : 'Novo Cliente';
$paginaAtiva = 'clientes';

$adminBase = '../admin/';
require_once __DIR__ . '/../admin/includes/sidebar.php';
?>

<a href="index.php" class="post-back-link">
  <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
    <path d="M19 12H5M5 12l7-7M5 12l7 7"/>
  </svg>
  Voltar para Clientes
</a>

<div class="adm-page-head">
  <h1 class="adm-page-title"><?= htmlspecialchars($pageTitle) ?></h1>
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
        <label class="adm-form__label" for="titulo">
          Título <span style="color:var(--action)">*</span>
        </label>
        <input class="adm-form__input" type="text" id="titulo" name="titulo"
               placeholder="Ex: Bahia ou Compass Brazil"
               value="<?= htmlspecialchars($vTitulo) ?>" required>
      </div>

      <div class="adm-form__group">
        <label class="adm-form__label">Tipo <span style="color:var(--action)">*</span></label>
        <div style="display:flex;gap:16px;margin-top:4px;">
          <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
            <input type="radio" name="tipo" value="destino" <?= $vTipo === 'destino' ? 'checked' : '' ?>>
            Destino
          </label>
          <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
            <input type="radio" name="tipo" value="parceiro" <?= $vTipo === 'parceiro' ? 'checked' : '' ?>>
            Parceiro
          </label>
        </div>
        <p style="font-size:.75rem;color:var(--text-sec);margin-top:6px;">Destinos aparecem na seção de destinos turísticos. Parceiros aparecem na seção de parceiros.</p>
      </div>

      <div class="adm-form__group" id="campo-regiao" style="<?= $vTipo === 'parceiro' ? 'display:none;' : '' ?>">
        <label class="adm-form__label" for="regiao">Região (apenas Destinos)</label>
        <input class="adm-form__input" type="text" id="regiao" name="regiao"
               placeholder="Ex: Nordeste, Centro-Oeste, Sul..."
               value="<?= htmlspecialchars($vRegiao) ?>">
      </div>

      <div class="adm-form__group">
        <label class="adm-form__label" for="descricao">Descrição</label>
        <textarea class="adm-form__input" id="descricao" name="descricao"
                  rows="5" placeholder="Descreva o cliente..."><?= $vDescricao ?></textarea>
      </div>

      <!-- Imagem de fundo do card -->
      <div class="adm-form__group" id="campo-imagem-fundo">
        <label class="adm-form__label">Imagem de fundo do card</label>
        <?php if ($vImagemFundo): ?>
          <img id="fundo-preview" src="<?= htmlspecialchars('../' . $vImagemFundo, ENT_QUOTES, 'UTF-8') ?>"
               alt="Imagem de fundo atual"
               style="width:100%;max-height:140px;border-radius:8px;margin-bottom:10px;object-fit:cover;">
        <?php else: ?>
          <img id="fundo-preview" src="" alt=""
               style="display:none;width:100%;max-height:140px;border-radius:8px;margin-bottom:10px;object-fit:cover;">
        <?php endif; ?>
        <label class="post-img-upload" for="fundo-upload" style="padding:20px 16px;">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
          </svg>
          <span id="fundo-label">Enviar imagem de fundo</span>
        </label>
        <input type="file" id="fundo-upload" name="imagem_fundo_file" accept="image/jpeg,image/png,image/webp" style="display:none;">
        <p style="font-size:.75rem;color:var(--text-sec);margin-top:6px;">JPG, PNG ou WebP. Máximo 5 MB.</p>
      </div>

      <!-- Vídeo do pop-up -->
      <div class="adm-form__group" id="campo-iframe" style="<?= $vTipo === 'parceiro' ? 'display:none;' : '' ?>">
        <label class="adm-form__label" for="iframe">Vídeo do pop-up (iframe)</label>
        <textarea class="adm-form__input" id="iframe" name="iframe"
                  rows="3" placeholder='https://youtu.be/... ou https://www.youtube.com/watch?v=... ou código <iframe>'><?= htmlspecialchars($vIframe) ?></textarea>
        <p style="font-size:.75rem;color:var(--text-sec);margin-top:6px;">Cole o link do YouTube, Vimeo ou código &lt;iframe&gt;. Aparece no pop-up ao clicar no card.</p>
      </div>

    </div>

    <!-- Painel lateral -->
    <div class="post-side-card">

      <!-- Logo -->
      <div class="post-side-section">
        <div class="post-side-label">Logo do cliente</div>
        <?php if ($vLogo): ?>
          <img id="logo-preview" src="<?= htmlspecialchars('../' . $vLogo, ENT_QUOTES, 'UTF-8') ?>"
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
        <p style="font-size:.75rem;color:var(--text-sec);margin-top:6px;">JPG, PNG ou WebP. Máximo 2 MB.</p>
      </div>

      <!-- Link Guia -->
      <div class="post-side-section">
        <label class="post-side-label" for="link_guia">Link Guia (BM)</label>
        <input class="adm-form__input" type="url" id="link_guia" name="link_guia"
               placeholder="https://..."
               value="<?= htmlspecialchars($vLinkGuia) ?>">
      </div>

      <!-- Redes Sociais -->
      <div class="post-side-section">
        <div class="post-side-label">Redes Sociais</div>

        <div class="adm-form__group" style="margin-bottom:10px;">
          <label class="adm-form__label" for="facebook" style="font-size:.8rem;">Facebook</label>
          <input class="adm-form__input" type="url" id="facebook" name="facebook"
                 placeholder="https://facebook.com/..."
                 value="<?= htmlspecialchars($vFacebook) ?>">
        </div>

        <div class="adm-form__group" style="margin-bottom:10px;">
          <label class="adm-form__label" for="instagram" style="font-size:.8rem;">Instagram</label>
          <input class="adm-form__input" type="url" id="instagram" name="instagram"
                 placeholder="https://instagram.com/..."
                 value="<?= htmlspecialchars($vInstagram) ?>">
        </div>

        <div class="adm-form__group" style="margin-bottom:10px;">
          <label class="adm-form__label" for="linkedin" style="font-size:.8rem;">LinkedIn</label>
          <input class="adm-form__input" type="url" id="linkedin" name="linkedin"
                 placeholder="https://linkedin.com/..."
                 value="<?= htmlspecialchars($vLinkedin) ?>">
        </div>

        <div class="adm-form__group" style="margin-bottom:10px;">
          <label class="adm-form__label" for="site" style="font-size:.8rem;">Site</label>
          <input class="adm-form__input" type="url" id="site" name="site"
                 placeholder="https://..."
                 value="<?= htmlspecialchars($vSite) ?>">
        </div>

        <div class="adm-form__group" style="margin-bottom:0;">
          <label class="adm-form__label" for="youtube" style="font-size:.8rem;">YouTube</label>
          <input class="adm-form__input" type="url" id="youtube" name="youtube"
                 placeholder="https://youtube.com/..."
                 value="<?= htmlspecialchars($vYoutube) ?>">
        </div>
      </div>

      <div class="post-side-actions">
        <button type="submit" class="btn btn-primary btn-full">Salvar Cliente</button>
        <a href="index.php" class="btn btn-ghost btn-full">Cancelar</a>
      </div>

    </div>
  </div>
</form>

<script>
(function () {
  var radios          = document.querySelectorAll('input[name="tipo"]');
  var campoRegiao     = document.getElementById('campo-regiao');
  var campoFundo      = document.getElementById('campo-imagem-fundo');
  var campoIframe     = document.getElementById('campo-iframe');
  function toggleTipo() {
    var isParceiro = document.querySelector('input[name="tipo"]:checked').value === 'parceiro';
    campoRegiao.style.display  = isParceiro ? 'none' : '';
    campoIframe.style.display  = isParceiro ? 'none' : '';
  }
  radios.forEach(function(r) { r.addEventListener('change', toggleTipo); });
}());
</script>

<script>
(function () {
  var logoInput   = document.getElementById('logo-upload');
  var logoPreview = document.getElementById('logo-preview');
  var logoLabel   = document.getElementById('logo-label');
  logoInput.addEventListener('change', function () {
    var file = this.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function (e) {
      logoPreview.src = e.target.result;
      logoPreview.style.display = 'block';
      logoLabel.textContent = file.name;
    };
    reader.readAsDataURL(file);
  });

  var fundoInput   = document.getElementById('fundo-upload');
  var fundoPreview = document.getElementById('fundo-preview');
  var fundoLabel   = document.getElementById('fundo-label');
  fundoInput.addEventListener('change', function () {
    var file = this.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function (e) {
      fundoPreview.src = e.target.result;
      fundoPreview.style.display = 'block';
      fundoLabel.textContent = file.name;
    };
    reader.readAsDataURL(file);
  });
}());

tinymce.init({
  license_key: 'gpl',
  selector: '#descricao',
  height: 300,
  menubar: true,
  plugins: 'link lists image table code autolink preview searchreplace wordcount emoticons',
  toolbar: 'undo redo | styleselect | bold italic underline | ' +
           'alignleft aligncenter alignright alignjustify | ' +
           'bullist numlist outdent indent | link image table | code | ' +
           'searchreplace preview emoticons',
  content_style: 'body { font-family: Inter, sans-serif; font-size: 14px; }',
  images_upload_url: '/BrasilDNA-Website/admin/upload_image.php',
  automatic_uploads: true,
  paste_data_images: true,
  relative_urls: false,
  remove_script_host: false,
  convert_urls: true,
});
</script>

<?php
require_once __DIR__ . '/../admin/includes/layout-footer.php';
?>
