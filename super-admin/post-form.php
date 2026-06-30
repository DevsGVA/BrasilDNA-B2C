<?php
require_once __DIR__ . '/../includes/conexao.php';
require_once __DIR__ . '/includes/auth.php';

exigirLogin();

$id   = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : null;
$post = null;
$erro = '';

if ($id !== null) {
    $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $post = $stmt->fetch();
    if (!$post) { header('Location: index.php'); exit; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarCSRF($_POST['csrf_token'] ?? '')) {
        die('Requisição inválida. Recarregue a página e tente novamente.');
    }

    $titulo          = trim($_POST['titulo']          ?? '');
    $resumo          = trim($_POST['resumo']          ?? '');
    $conteudo        = strip_tags($_POST['conteudo'] ?? '', '<p><br><strong><em><b><i><ul><ol><li><h2><h3><blockquote><a><img><span>');
    $regiao          = trim($_POST['regiao']          ?? '');
    $status          = in_array($_POST['status'] ?? '', ['rascunho', 'publicado'])
                         ? $_POST['status'] : 'rascunho';
    $data_publicacao = !empty($_POST['data_publicacao']) ? $_POST['data_publicacao'] : null;
    $imagem = $post['imagem'] ?? null;

    if (!empty($_FILES['imagem_file']['tmp_name'])) {
        $uploadDir    = __DIR__ . '/../uploads/';
        $ext          = strtolower(pathinfo($_FILES['imagem_file']['name'], PATHINFO_EXTENSION));
        $allowedExts  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo        = finfo_open(FILEINFO_MIME_TYPE);
        $mime         = finfo_file($finfo, $_FILES['imagem_file']['tmp_name']);
        finfo_close($finfo);
        if (!in_array($ext, $allowedExts) || !in_array($mime, $allowedMimes)) {
            $erro = 'Formato inválido. Use JPG, PNG, GIF ou WebP.';
        } elseif ($_FILES['imagem_file']['size'] > 5 * 1024 * 1024) {
            $erro = 'Imagem muito grande. Máximo 5 MB.';
        } else {
            $filename = uniqid('img_') . '.' . $ext;
            if (move_uploaded_file($_FILES['imagem_file']['tmp_name'], $uploadDir . $filename)) {
                $imagem = 'uploads/' . $filename;
            } else {
                $erro = 'Falha ao salvar a imagem no servidor.';
            }
        }
    }

    if ($titulo === '') {
        $erro = 'O título é obrigatório.';
    }

    if (empty($erro)) {
        try {
            if ($id !== null) {
                $stmt = $pdo->prepare(
                    'UPDATE posts SET titulo=:titulo, resumo=:resumo, conteudo=:conteudo,
                     regiao=:regiao, status=:status, data_publicacao=:dp,
                     imagem=:img WHERE id=:id'
                );
                $stmt->execute([
                    ':titulo' => $titulo, ':resumo' => $resumo,
                    ':conteudo' => $conteudo, ':regiao' => $regiao,
                    ':status' => $status, ':dp' => $data_publicacao,
                    ':img' => $imagem, ':id' => $id,
                ]);
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO posts (titulo, resumo, conteudo, regiao, status, data_publicacao, imagem)
                     VALUES (:titulo, :resumo, :conteudo, :regiao, :status, :dp, :img)'
                );
                $stmt->execute([
                    ':titulo' => $titulo, ':resumo' => $resumo,
                    ':conteudo' => $conteudo, ':regiao' => $regiao,
                    ':status' => $status, ':dp' => $data_publicacao,
                    ':img' => $imagem,
                ]);
            }
        } catch (\PDOException $e) {
            error_log('[BrasilDNA] super post-form: ' . $e->getMessage());
            $erro = 'Erro ao salvar. Tente novamente.';
        }
        if (empty($erro)) {
            header('Location: index.php');
            exit;
        }
    }
}

$vTitulo   = $_POST['titulo']          ?? ($post['titulo']          ?? '');
$vResumo   = $_POST['resumo']          ?? ($post['resumo']          ?? '');
$vConteudo = $_POST['conteudo']        ?? ($post['conteudo']        ?? '');
$vRegiao   = $_POST['regiao']          ?? ($post['regiao']          ?? '');
$vStatus   = $_POST['status']          ?? ($post['status']          ?? 'rascunho');
$vData     = $_POST['data_publicacao'] ?? ($post['data_publicacao'] ?? date('Y-m-d'));
$vImagem   = $post['imagem'] ?? '';

$pageTitle   = $id !== null ? 'Editar post' : 'Criar post';
$paginaAtiva = 'posts';
require_once __DIR__ . '/includes/sidebar.php';
?>

<a href="index.php" class="post-back-link">
  <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
    <path d="M19 12H5M5 12l7-7M5 12l7 7"/>
  </svg>
  Voltar para Posts
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

<form id="post-form" method="POST" enctype="multipart/form-data">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(gerarCSRF()) ?>">
  <input type="hidden" name="status"   id="status-field"   value="<?= htmlspecialchars($vStatus) ?>">
  <input type="hidden" name="conteudo" id="conteudo-field" value="">

  <div class="post-form-layout">

    <div class="post-main-card">

      <div class="adm-form__group">
        <label class="adm-form__label" for="titulo">Título do post</label>
        <input
          class="adm-form__input"
          type="text"
          id="titulo"
          name="titulo"
          placeholder="Digite o título do post"
          value="<?= htmlspecialchars($vTitulo) ?>"
          required
        >
      </div>

      <div class="adm-form__group">
        <label class="adm-form__label" for="resumo">
          Resumo
          <span class="post-label-hint">(aparece nos cards da home)</span>
        </label>
        <textarea
          class="adm-form__textarea"
          id="resumo"
          name="resumo"
          placeholder="Breve descrição que aparecerá nos cards da página inicial..."
          style="min-height:80px;resize:none;"
        ><?= htmlspecialchars($vResumo) ?></textarea>
      </div>

      <div class="adm-form__group">
        <label class="adm-form__label">Corpo do post</label>
        <div class="post-editor-wrap">
          <div class="post-editor-toolbar">
            <button type="button" class="post-editor-btn" onclick="fmt('bold')"           title="Negrito"><b>B</b></button>
            <button type="button" class="post-editor-btn" onclick="fmt('italic')"          title="Itálico"><i>I</i></button>
            <button type="button" class="post-editor-btn" onclick="fmt('formatBlock','h2')" title="Título H2" style="font-size:12px;font-weight:700;">H2</button>
            <button type="button" class="post-editor-btn" onclick="fmt('formatBlock','h3')" title="Título H3" style="font-size:12px;font-weight:700;">H3</button>
            <button type="button" class="post-editor-btn" onclick="insertLink()" title="Inserir link">
              <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/>
              </svg>
            </button>
            <button type="button" class="post-editor-btn" onclick="insertImg()" title="Inserir imagem">
              <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21 15 16 10 5 21"/>
              </svg>
            </button>
          </div>
          <div
            class="post-editor-body"
            contenteditable="true"
            id="editor"
            data-placeholder="Escreva o conteúdo do post aqui..."
          ><?= $vConteudo ?></div>
        </div>
      </div>

    </div>

    <div class="post-side-card">

      <div class="post-side-section">
        <div class="post-side-label">Status</div>
        <div class="post-status-toggle">
          <button type="button" class="post-status-btn <?= $vStatus === 'rascunho'  ? 'is-active' : '' ?>" data-status="rascunho"  onclick="setStatus('rascunho')">Rascunho</button>
          <button type="button" class="post-status-btn <?= $vStatus === 'publicado' ? 'is-active' : '' ?>" data-status="publicado" onclick="setStatus('publicado')">Publicado</button>
        </div>
      </div>

      <div class="post-side-section">
        <div class="post-side-label">Imagem destacada</div>
        <?php if ($vImagem): ?>
          <img id="img-preview" src="<?= htmlspecialchars($vImagem, ENT_QUOTES, 'UTF-8') ?>"
               alt="Imagem atual" style="width:100%;border-radius:8px;margin-bottom:10px;object-fit:cover;max-height:160px;">
        <?php else: ?>
          <img id="img-preview" src="" alt="" style="display:none;width:100%;border-radius:8px;margin-bottom:10px;object-fit:cover;max-height:160px;">
        <?php endif; ?>
        <label class="post-img-upload" for="imagem-upload">
          <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
          </svg>
          <span id="img-label">Enviar imagem</span>
        </label>
        <input type="file" id="imagem-upload" name="imagem_file" accept="image/*" style="display:none;">
      </div>

      <div class="post-side-section">
        <label class="post-side-label" for="data_publicacao">Data de publicação</label>
        <input
          class="adm-form__input"
          type="date"
          id="data_publicacao"
          name="data_publicacao"
          value="<?= htmlspecialchars($vData) ?>"
        >
      </div>

      <div class="post-side-actions">
        <button
          type="submit"
          class="btn btn-primary btn-full"
          onclick="setStatus('publicado'); syncEditor();"
        >Publicar</button>
        <button
          type="submit"
          class="btn btn-ghost btn-full"
          onclick="setStatus('rascunho'); syncEditor();"
        >Salvar rascunho</button>
      </div>

    </div>
  </div>
</form>

<script>
function fmt(cmd, val) {
  document.getElementById('editor').focus();
  document.execCommand(cmd, false, val || null);
}

function insertLink() {
  var url = prompt('URL do link:');
  if (url) { document.getElementById('editor').focus(); document.execCommand('createLink', false, url); }
}

function insertImg() {
  var url = prompt('URL da imagem:');
  if (url) { document.getElementById('editor').focus(); document.execCommand('insertImage', false, url); }
}

function setStatus(val) {
  document.getElementById('status-field').value = val;
  document.querySelectorAll('.post-status-btn').forEach(function(b) {
    b.classList.toggle('is-active', b.dataset.status === val);
  });
}

function syncEditor() {
  document.getElementById('conteudo-field').value = document.getElementById('editor').innerHTML;
}

document.getElementById('post-form').addEventListener('submit', syncEditor);

document.getElementById('imagem-upload').addEventListener('change', function() {
  var file = this.files[0];
  if (!file) return;
  var preview = document.getElementById('img-preview');
  var label   = document.getElementById('img-label');
  var reader  = new FileReader();
  reader.onload = function(e) {
    preview.src = e.target.result;
    preview.style.display = 'block';
    label.textContent = file.name;
  };
  reader.readAsDataURL(file);
});
</script>

<?php require_once __DIR__ . '/includes/layout-footer.php'; ?>
