<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/conexao.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../backstage/includes/auth.php';
exigirLogin();

function clienteVideoUrl(string $entrada): string {
    $entrada = trim($entrada);
    if ($entrada === '') return '';
    // Compatibilidade com iframes antigos: nunca renderizar o HTML armazenado.
    if (str_starts_with($entrada, '<')) {
        if (!preg_match('~<iframe\b[^>]*\ssrc\s*=\s*([\x22\x27])(.*?)\1~is', $entrada, $m)) {
            throw new InvalidArgumentException('Informe um link ou iframe válido do YouTube ou Vimeo.');
        }
        $entrada = html_entity_decode($m[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    $u = parse_url($entrada);
    if (!is_array($u) || strtolower($u['scheme'] ?? '') !== 'https' || isset($u['user']) || isset($u['pass']) || isset($u['port'])) {
        throw new InvalidArgumentException('Use um endereço HTTPS do YouTube ou Vimeo.');
    }
    $host = strtolower($u['host'] ?? '');
    $path = $u['path'] ?? '';
    parse_str($u['query'] ?? '', $q);
    $id = '';
    if ($host === 'youtu.be') {
        $id = trim($path, '/');
    } elseif (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com'], true)) {
        if ($path === '/watch' && is_string($q['v'] ?? null)) {
            $id = $q['v'];
        } elseif (preg_match('~^/(?:embed|shorts|live)/([A-Za-z0-9_-]{11})/?$~D', $path, $m)) {
            $id = $m[1];
        }
    }
    if ($id !== '' && preg_match('~^[A-Za-z0-9_-]{11}$~D', $id)) {
        $youtubeHost = in_array($host, ['youtube-nocookie.com', 'www.youtube-nocookie.com'], true) ? 'www.youtube-nocookie.com' : 'www.youtube.com';
        return 'https://' . $youtubeHost . '/embed/' . $id;
    }
    $hash = '';
    if (in_array($host, ['vimeo.com', 'www.vimeo.com'], true) && preg_match('~^/([0-9]+)(?:/([A-Za-z0-9]+))?/?$~D', $path, $m)) {
        $id = $m[1];
        $hash = $m[2] ?? '';
    } elseif ($host === 'player.vimeo.com' && preg_match('~^/video/([0-9]+)/?$~D', $path, $m)) {
        $id = $m[1];
    } else {
        $id = '';
    }
    if ($id !== '') {
        if (array_key_exists('h', $q)) {
            if (!is_string($q['h']) || !preg_match('~^[A-Za-z0-9]+$~D', $q['h'])) {
                throw new InvalidArgumentException('O link privado do Vimeo é inválido.');
            }
            $hash = $q['h'];
        }
        return 'https://player.vimeo.com/video/' . $id . ($hash !== '' ? '?h=' . rawurlencode($hash) : '');
    }
    throw new InvalidArgumentException('Vídeo não reconhecido. Use um link de vídeo do YouTube ou Vimeo.');
}

$id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : null;
$cliente = null;
$erro = '';
if ($id !== null) {
    $stmt = $pdo->prepare('SELECT * FROM clientes WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $cliente = $stmt->fetch();
    if (!$cliente) { header('Location: index.php'); exit; }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarCSRF($_POST['csrf_token'] ?? '')) {
        die('Requisição inválida. Recarregue a página e tente novamente.');
    }
    $titulo = trim($_POST['titulo'] ?? '');
    $tipo = in_array($_POST['tipo'] ?? '', ['destino', 'parceiro']) ? $_POST['tipo'] : 'destino';
    $regiao = trim($_POST['regiao'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $iframe = '';
    try {
        $iframe = clienteVideoUrl((string) ($_POST['iframe'] ?? ''));
    } catch (InvalidArgumentException $e) {
        $erro = $e->getMessage();
    }
    $facebook = trim($_POST['facebook'] ?? '');
    $instagram = trim($_POST['instagram'] ?? '');
    $linkedin = trim($_POST['linkedin'] ?? '');
    $site = trim($_POST['site'] ?? '');
    $youtube = trim($_POST['youtube'] ?? '');
    $link_guia = trim($_POST['link_guia'] ?? '');
    $logo = $cliente['logo'] ?? null;
    $imagem_fundo = $cliente['imagem_fundo'] ?? null;
    if ($titulo === '') $erro = 'O título do cliente é obrigatório.';

    if (empty($erro) && !empty($_FILES['logo_file']['tmp_name'])) {
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        $ext = strtolower(pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['logo_file']['tmp_name']);
        finfo_close($finfo);
        if (!in_array($ext, $allowedExts) || !in_array($mime, $allowedMimes)) {
            $erro = 'Logo: formato inválido. Use JPG, PNG ou WebP.';
        } elseif ($_FILES['logo_file']['size'] > 2 * 1024 * 1024) {
            $erro = 'Logo muito grande. Máximo 2 MB.';
        } else {
            $filename = uniqid('cliente_') . '.' . $ext;
            $uploadDir = __DIR__ . '/../uploads/clientes/';
            if (move_uploaded_file($_FILES['logo_file']['tmp_name'], $uploadDir . $filename)) {
                $logo = 'uploads/clientes/' . $filename;
            } else { $erro = 'Falha ao salvar o logo.'; }
        }
    }
    if (empty($erro) && !empty($_FILES['imagem_fundo_file']['tmp_name'])) {
        $allowedExtsF = ['jpg', 'jpeg', 'png', 'webp'];
        $allowedMimesF = ['image/jpeg', 'image/png', 'image/webp'];
        $extF = strtolower(pathinfo($_FILES['imagem_fundo_file']['name'], PATHINFO_EXTENSION));
        $finfoF = finfo_open(FILEINFO_MIME_TYPE);
        $mimeF = finfo_file($finfoF, $_FILES['imagem_fundo_file']['tmp_name']);
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
            } else { $erro = 'Falha ao salvar a imagem de fundo.'; }
        }
    }
    if (empty($erro)) {
        try {
            $dados = [
                ':titulo' => $titulo, ':tipo' => $tipo, ':regiao' => $regiao,
                ':logo' => $logo, ':descricao' => $descricao, ':iframe' => $iframe,
                ':imagem_fundo' => $imagem_fundo, ':facebook' => $facebook,
                ':instagram' => $instagram, ':linkedin' => $linkedin, ':site' => $site,
                ':youtube' => $youtube, ':link_guia' => $link_guia,
            ];
            if ($id !== null) {
                $stmt = $pdo->prepare('UPDATE clientes SET titulo=:titulo, tipo=:tipo, regiao=:regiao, logo=:logo, descricao=:descricao, iframe=:iframe, imagem_fundo=:imagem_fundo, facebook=:facebook, instagram=:instagram, linkedin=:linkedin, site=:site, youtube=:youtube, link_guia=:link_guia WHERE id=:id');
                $dados[':id'] = $id;
            } else {
                $stmt = $pdo->prepare('INSERT INTO clientes (titulo, tipo, regiao, logo, descricao, iframe, imagem_fundo, facebook, instagram, linkedin, site, youtube, link_guia) VALUES (:titulo, :tipo, :regiao, :logo, :descricao, :iframe, :imagem_fundo, :facebook, :instagram, :linkedin, :site, :youtube, :link_guia)');
            }
            $stmt->execute($dados);
        } catch (\PDOException $e) {
            error_log('[BrasilDNA] clientes/form: ' . $e->getMessage());
            $erro = 'Erro ao salvar. Tente novamente.';
        }
        if (empty($erro)) { header('Location: index.php'); exit; }
    }
}
$vTitulo = $_POST['titulo'] ?? ($cliente['titulo'] ?? '');
$vTipo = $_POST['tipo'] ?? ($cliente['tipo'] ?? 'destino');
$vRegiao = $_POST['regiao'] ?? ($cliente['regiao'] ?? '');
$vDescricao = $_POST['descricao'] ?? ($cliente['descricao'] ?? '');
$vIframe = $_POST['iframe'] ?? ($cliente['iframe'] ?? '');
try { $vIframe = clienteVideoUrl((string) $vIframe); } catch (InvalidArgumentException $e) {
    if ($erro === '') $erro = 'Revise o vídeo cadastrado: ' . $e->getMessage();
}
$vFacebook = $_POST['facebook'] ?? ($cliente['facebook'] ?? '');
$vInstagram = $_POST['instagram'] ?? ($cliente['instagram'] ?? '');
$vLinkedin = $_POST['linkedin'] ?? ($cliente['linkedin'] ?? '');
$vSite = $_POST['site'] ?? ($cliente['site'] ?? '');
$vYoutube = $_POST['youtube'] ?? ($cliente['youtube'] ?? '');
$vLinkGuia = $_POST['link_guia'] ?? ($cliente['link_guia'] ?? '');
$vLogo = $cliente['logo'] ?? '';
$vImagemFundo = $cliente['imagem_fundo'] ?? '';
$pageTitle = $id !== null ? 'Editar Cliente' : 'Novo Cliente';
$paginaAtiva = 'clientes';
$adminBase = '../backstage/';
require_once __DIR__ . '/../backstage/includes/sidebar.php';
?>

<a href="index.php" class="post-back-link">
  <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 12H5M5 12l7-7M5 12l7 7"/></svg>
  Voltar para Clientes
</a>
<div class="adm-page-head"><h1 class="adm-page-title"><?= htmlspecialchars($pageTitle) ?></h1></div>
<?php if ($erro): ?>
<div class="adm-alert adm-alert-err" style="margin-bottom:20px;"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>
<form id="cliente-form" method="POST" enctype="multipart/form-data">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(gerarCSRF()) ?>">
  <input type="hidden" name="iframe" id="video-url-field" value="">
  <div class="post-form-layout">
    <div class="post-main-card">
      <div class="adm-form__group">
        <label class="adm-form__label" for="titulo">Título <span style="color:var(--action)">*</span></label>
        <input class="adm-form__input" type="text" id="titulo" name="titulo" placeholder="Ex: Bahia ou Compass Brazil" value="<?= htmlspecialchars($vTitulo) ?>" required>
      </div>
      <div class="adm-form__group">
        <label class="adm-form__label">Tipo <span style="color:var(--action)">*</span></label>
        <div style="display:flex;gap:16px;margin-top:4px;">
          <label style="display:flex;align-items:center;gap:6px;cursor:pointer;"><input type="radio" name="tipo" value="destino" <?= $vTipo === 'destino' ? 'checked' : '' ?>> Destino</label>
          <label style="display:flex;align-items:center;gap:6px;cursor:pointer;"><input type="radio" name="tipo" value="parceiro" <?= $vTipo === 'parceiro' ? 'checked' : '' ?>> Parceiro</label>
        </div>
        <p style="font-size:.75rem;color:var(--text-sec);margin-top:6px;">Destinos aparecem na seção de destinos turísticos. Parceiros aparecem na seção de parceiros.</p>
      </div>
      <div class="adm-form__group" id="campo-regiao" style="<?= $vTipo === 'parceiro' ? 'display:none;' : '' ?>">
        <label class="adm-form__label" for="regiao">Região (apenas Destinos)</label>
        <input class="adm-form__input" type="text" id="regiao" name="regiao" placeholder="Ex: Nordeste, Centro-Oeste, Sul..." value="<?= htmlspecialchars($vRegiao) ?>">
      </div>
      <div class="adm-form__group">
        <label class="adm-form__label" for="descricao">Descrição</label>
        <textarea class="adm-form__input" id="descricao" name="descricao" rows="5" placeholder="Descreva o cliente..."><?= htmlspecialchars($vDescricao, ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>
      <div class="adm-form__group" id="campo-imagem-fundo">
        <label class="adm-form__label">Imagem de fundo do card</label>
        <?php if ($vImagemFundo): ?>
        <img id="fundo-preview" src="<?= htmlspecialchars('../' . $vImagemFundo, ENT_QUOTES, 'UTF-8') ?>" alt="Imagem de fundo atual" style="width:100%;max-height:140px;border-radius:8px;margin-bottom:10px;object-fit:cover;">
        <?php else: ?>
        <img id="fundo-preview" src="" alt="" style="display:none;width:100%;max-height:140px;border-radius:8px;margin-bottom:10px;object-fit:cover;">
        <?php endif; ?>
        <label class="post-img-upload" for="fundo-upload" style="padding:20px 16px;">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
          <span id="fundo-label">Enviar imagem de fundo</span>
        </label>
        <input type="file" id="fundo-upload" name="imagem_fundo_file" accept="image/jpeg,image/png,image/webp" style="display:none;">
        <p style="font-size:.75rem;color:var(--text-sec);margin-top:6px;">JPG, PNG ou WebP. Máximo 5 MB.</p>
      </div>
      <div class="adm-form__group" id="campo-iframe" style="<?= $vTipo === 'parceiro' ? 'display:none;' : '' ?>">
        <label class="adm-form__label" for="iframe">Vídeo do pop-up — YouTube ou Vimeo</label>
        <textarea class="adm-form__input" id="iframe" rows="3" aria-describedby="video-help" placeholder="Cole o link do YouTube, Vimeo ou o código de incorporação"><?= htmlspecialchars($vIframe, ENT_QUOTES, 'UTF-8') ?></textarea>
        <p id="video-help" style="font-size:.75rem;color:var(--text-sec);margin-top:6px;">Aceita links de vídeos e códigos iframe do YouTube ou Vimeo. Somente a URL será enviada e salva. Para vídeos não listados do Vimeo, use o link completo.</p>
        <noscript><p>Ative o JavaScript para editar e salvar o vídeo com segurança.</p></noscript>
      </div>
    </div>
    <div class="post-side-card">
      <div class="post-side-section">
        <div class="post-side-label">Logo do cliente</div>
        <?php if ($vLogo): ?>
        <img id="logo-preview" src="<?= htmlspecialchars('../' . $vLogo, ENT_QUOTES, 'UTF-8') ?>" alt="Logo atual" style="width:100%;border-radius:8px;margin-bottom:10px;object-fit:contain;max-height:80px;background:#012a15;padding:8px;">
        <?php else: ?>
        <img id="logo-preview" src="" alt="" style="display:none;width:100%;border-radius:8px;margin-bottom:10px;object-fit:contain;max-height:80px;background:#012a15;padding:8px;">
        <?php endif; ?>
        <label class="post-img-upload" for="logo-upload">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
          <span id="logo-label">Enviar logo</span>
        </label>
        <input type="file" id="logo-upload" name="logo_file" accept="image/*" style="display:none;">
        <p style="font-size:.75rem;color:var(--text-sec);margin-top:6px;">JPG, PNG ou WebP. Máximo 2 MB.</p>
      </div>
      <div class="post-side-section">
        <label class="post-side-label" for="link_guia">Link Guia (BM)</label>
        <input class="adm-form__input" type="url" id="link_guia" name="link_guia" placeholder="https://..." value="<?= htmlspecialchars($vLinkGuia) ?>">
      </div>
      <div class="post-side-section">
        <div class="post-side-label">Redes Sociais</div>
        <div class="adm-form__group" style="margin-bottom:10px;"><label class="adm-form__label" for="facebook" style="font-size:.8rem;">Facebook</label><input class="adm-form__input" type="url" id="facebook" name="facebook" placeholder="https://facebook.com/..." value="<?= htmlspecialchars($vFacebook) ?>"></div>
        <div class="adm-form__group" style="margin-bottom:10px;"><label class="adm-form__label" for="instagram" style="font-size:.8rem;">Instagram</label><input class="adm-form__input" type="url" id="instagram" name="instagram" placeholder="https://instagram.com/..." value="<?= htmlspecialchars($vInstagram) ?>"></div>
        <div class="adm-form__group" style="margin-bottom:10px;"><label class="adm-form__label" for="linkedin" style="font-size:.8rem;">LinkedIn</label><input class="adm-form__input" type="url" id="linkedin" name="linkedin" placeholder="https://linkedin.com/..." value="<?= htmlspecialchars($vLinkedin) ?>"></div>
        <div class="adm-form__group" style="margin-bottom:10px;"><label class="adm-form__label" for="site" style="font-size:.8rem;">Site</label><input class="adm-form__input" type="url" id="site" name="site" placeholder="https://..." value="<?= htmlspecialchars($vSite) ?>"></div>
        <div class="adm-form__group" style="margin-bottom:10px;"><label class="adm-form__label" for="youtube" style="font-size:.8rem;">YouTube</label><input class="adm-form__input" type="url" id="youtube" name="youtube" placeholder="https://youtube.com/..." value="<?= htmlspecialchars($vYoutube) ?>"></div>
      </div>
      <div class="post-side-actions"><button type="submit" id="cliente-save" class="btn btn-primary btn-full" disabled>Salvar Cliente</button><a href="index.php" class="btn btn-ghost btn-full">Cancelar</a></div>
    </div>
  </div>
</form>
<script>
(function () {
  var form = document.getElementById('cliente-form');
  var video = document.getElementById('iframe');
  var hidden = document.getElementById('video-url-field');
  var radios = document.querySelectorAll('input[name="tipo"]');
  function extractVideoUrl() {
    var value = video.value.trim();
    if (value.startsWith('<')) {
      var template = document.createElement('template');
      template.innerHTML = value;
      var frame = template.content.querySelector('iframe[src]');
      if (!frame) throw new Error('O código colado não contém um iframe com endereço de vídeo.');
      value = (frame.getAttribute('src') || '').trim();
      if (!value) throw new Error('O iframe não contém um endereço de vídeo.');
    }
    if (value) {
      var url;
      try { url = new URL(value); } catch (e) { throw new Error('Informe um link HTTPS do YouTube ou Vimeo.'); }
      var hosts = ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be', 'youtube-nocookie.com', 'www.youtube-nocookie.com', 'vimeo.com', 'www.vimeo.com', 'player.vimeo.com'];
      if (url.protocol !== 'https:' || url.username || url.password || url.port || !hosts.includes(url.hostname)) {
        throw new Error('Use um link HTTPS de vídeo do YouTube ou Vimeo.');
      }
      value = url.href;
    }
    return value;
  }
  video.addEventListener('input', function () { video.setCustomValidity(''); });
  form.addEventListener('submit', function (event) {
    try {
      hidden.value = extractVideoUrl();
      video.setCustomValidity('');
    } catch (e) {
      event.preventDefault();
      hidden.value = '';
      document.getElementById('campo-iframe').style.display = '';
      video.setCustomValidity(e.message);
      video.reportValidity();
    }
  });
  function toggleTipo() {
    var parceiro = document.querySelector('input[name="tipo"]:checked').value === 'parceiro';
    document.getElementById('campo-regiao').style.display = parceiro ? 'none' : '';
    document.getElementById('campo-iframe').style.display = parceiro ? 'none' : '';
  }
  radios.forEach(function (radio) { radio.addEventListener('change', toggleTipo); });
  function preview(inputId, imageId, labelId) {
    document.getElementById(inputId).addEventListener('change', function () {
      var file = this.files[0];
      if (!file) return;
      var reader = new FileReader();
      reader.onload = function (e) {
        var image = document.getElementById(imageId);
        image.src = e.target.result;
        image.style.display = 'block';
        document.getElementById(labelId).textContent = file.name;
      };
      reader.readAsDataURL(file);
    });
  }
  preview('logo-upload', 'logo-preview', 'logo-label');
  preview('fundo-upload', 'fundo-preview', 'fundo-label');
  document.getElementById('cliente-save').disabled = false;
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
  images_upload_url: '/admin/upload_image.php',
  automatic_uploads: true,
  paste_data_images: true,
  relative_urls: false,
  remove_script_host: false,
  convert_urls: true,
});
</script>
<?php require_once __DIR__ . '/../backstage/includes/layout-footer.php'; ?>
