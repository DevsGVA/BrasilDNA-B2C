<?php
require_once __DIR__ . '/../includes/conexao.php';
require_once __DIR__ . '/includes/auth.php';

exigirLogin();

function gerarSlug(string $texto): string {
    $texto = trim($texto);
    if ($texto === '') {
        return '';
    }

    if (function_exists('iconv')) {
        $convertido = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        if ($convertido !== false) {
            $texto = $convertido;
        }
    }

    $texto = strtolower($texto);
    $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);
    $texto = trim($texto, '-');
    return $texto;
}

function gerarSlugUnico(PDO $pdo, string $titulo, ?int $idAtual = null): string {
    $base = gerarSlug($titulo);
    if ($base === '') {
        $base = 'post';
    }

    $slug = $base;
    $contador = 1;

    while (true) {
        if ($idAtual !== null) {
            $stmt = $pdo->prepare('SELECT id FROM posts WHERE slug = :slug AND id <> :id LIMIT 1');
            $stmt->execute([':slug' => $slug, ':id' => $idAtual]);
        } else {
            $stmt = $pdo->prepare('SELECT id FROM posts WHERE slug = :slug LIMIT 1');
            $stmt->execute([':slug' => $slug]);
        }

        if (!$stmt->fetch()) {
            return $slug;
        }

        $contador++;
        $slug = $base . '-' . $contador;
    }
}

$id   = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : null;
$post = null;
$erro = '';

if ($id !== null) {
    $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$post) {
        header('Location: index.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarCSRF($_POST['csrf_token'] ?? '')) {
        die('Requisição inválida. Recarregue a página e tente novamente.');
    }

    $titulo          = trim($_POST['titulo'] ?? '');
    $resumo          = trim($_POST['resumo'] ?? '');
    $conteudo        = strip_tags($_POST['conteudo'] ?? '', '<p><br><strong><em><b><i><ul><ol><li><h2><h3><blockquote><a><img><span>');
    $regiao          = trim($_POST['regiao'] ?? ($post['regiao'] ?? ''));
    $status          = in_array($_POST['status'] ?? '', ['rascunho', 'publicado'], true) ? $_POST['status'] : 'rascunho';
    $data_publicacao = !empty($_POST['data_publicacao']) ? $_POST['data_publicacao'] : null;
    $imagem          = $_POST['imagem_atual'] ?? ($post['imagem'] ?? null);

    $regioesValidas = ['Norte', 'Nordeste', 'Centro-Oeste', 'Sudeste'];
    if ($regiao !== '' && !in_array($regiao, $regioesValidas, true)) {
        $regiao = null;
    } elseif ($regiao === '') {
        $regiao = null;
    }

    if (!empty($_FILES['imagem_file']['tmp_name'])) {
        $uploadBase = dirname(__DIR__) . '/uploads/posts';
        $uploadDir  = $uploadBase . '/';
        $ext        = strtolower(pathinfo($_FILES['imagem_file']['name'], PATHINFO_EXTENSION));
        $allowedExts  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo        = finfo_open(FILEINFO_MIME_TYPE);
        $mime         = finfo_file($finfo, $_FILES['imagem_file']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($ext, $allowedExts, true) || !in_array($mime, $allowedMimes, true)) {
            $erro = 'Formato inválido. Use JPG, PNG, GIF ou WebP.';
        } elseif (($_FILES['imagem_file']['size'] ?? 0) > 5 * 1024 * 1024) {
            $erro = 'Imagem muito grande. Máximo 5 MB.';
        } else {
            if (!is_dir($uploadBase) && !mkdir($uploadBase, 0755, true)) {
                $erro = 'Não foi possível criar a pasta de uploads.';
            } else {
                $filename = uniqid('img_', true) . '.' . $ext;
                $destino  = $uploadDir . $filename;

                if (move_uploaded_file($_FILES['imagem_file']['tmp_name'], $destino)) {
                    $imagem = 'uploads/posts/' . $filename;
                } else {
                    $erro = 'Falha ao salvar a imagem no servidor.';
                }
            }
        }
    }

    if ($titulo === '') {
        $erro = 'O título é obrigatório.';
    }

    if (empty($erro)) {
        $slug = gerarSlugUnico($pdo, $titulo, $id);

        try {
            if ($id !== null) {
                $stmt = $pdo->prepare(
                    'UPDATE posts
                     SET titulo = :titulo,
                         slug = :slug,
                         resumo = :resumo,
                         conteudo = :conteudo,
                         regiao = :regiao,
                         status = :status,
                         data_publicacao = :dp,
                         imagem = :img
                     WHERE id = :id'
                );

                $stmt->execute([
                    ':titulo' => $titulo,
                    ':slug' => $slug,
                    ':resumo' => $resumo !== '' ? $resumo : null,
                    ':conteudo' => $conteudo !== '' ? $conteudo : null,
                    ':regiao' => $regiao,
                    ':status' => $status,
                    ':dp' => $data_publicacao,
                    ':img' => $imagem !== '' ? $imagem : null,
                    ':id' => $id,
                ]);
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO posts (titulo, slug, resumo, conteudo, regiao, status, data_publicacao, imagem)
                     VALUES (:titulo, :slug, :resumo, :conteudo, :regiao, :status, :dp, :img)'
                );

                $stmt->execute([
                    ':titulo' => $titulo,
                    ':slug' => $slug,
                    ':resumo' => $resumo !== '' ? $resumo : null,
                    ':conteudo' => $conteudo !== '' ? $conteudo : null,
                    ':regiao' => $regiao,
                    ':status' => $status,
                    ':dp' => $data_publicacao,
                    ':img' => $imagem !== '' ? $imagem : null,
                ]);
            }

            header('Location: index.php');
            exit;
        } catch (\PDOException $e) {
            error_log('[BrasilDNA] super post-form: ' . $e->getMessage());
            $erro = 'Erro ao salvar. Tente novamente.';
        }
    }

    $post = [
        'titulo' => $titulo,
        'slug' => $post['slug'] ?? '',
        'resumo' => $resumo,
        'conteudo' => $conteudo,
        'regiao' => $regiao,
        'status' => $status,
        'data_publicacao' => $data_publicacao,
        'imagem' => $imagem,
    ];
}

$vTitulo   = $_POST['titulo'] ?? ($post['titulo'] ?? '');
$vResumo   = $_POST['resumo'] ?? ($post['resumo'] ?? '');
$vConteudo = $_POST['conteudo'] ?? ($post['conteudo'] ?? '');
$vRegiao   = $_POST['regiao'] ?? ($post['regiao'] ?? '');
$vStatus   = $_POST['status'] ?? ($post['status'] ?? 'rascunho');
$vData     = $_POST['data_publicacao'] ?? ($post['data_publicacao'] ?? date('Y-m-d'));
$vImagem   = $post['imagem'] ?? ($_POST['imagem_atual'] ?? '');

// URL base dinâmica — funciona em qualquer domínio sem alterar o código
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
         . '://' . $_SERVER['HTTP_HOST'];

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
  <input type="hidden" name="status" id="status-field" value="<?= htmlspecialchars($vStatus) ?>">
  <input type="hidden" name="conteudo" id="conteudo-field" value="">
  <input type="hidden" name="imagem_atual" id="imagem-atual-field" value="<?= htmlspecialchars($vImagem) ?>">
  <input type="hidden" name="regiao" value="<?= htmlspecialchars((string) $vRegiao) ?>">

  <div class="post-form-layout">
    <div class="post-main-card">
      <div class="adm-form__group">
        <label class="adm-form__label" for="titulo">Título do post</label>
        <input class="adm-form__input" type="text" id="titulo" name="titulo" placeholder="Digite o título do post" value="<?= htmlspecialchars($vTitulo) ?>" required>
      </div>

      <div class="adm-form__group">
        <label class="adm-form__label" for="resumo">
          Resumo
          <span class="post-label-hint">(aparece nos cards da home)</span>
        </label>
        <textarea class="adm-form__textarea" id="resumo" name="resumo" placeholder="Breve descrição que aparecerá nos cards da página inicial..." style="min-height:80px;resize:none;"><?= htmlspecialchars($vResumo) ?></textarea>
      </div>

      <div class="adm-form__group">
        <label class="adm-form__label">Corpo do post</label>
        <textarea id="editor" name="conteudo"><?= $vConteudo ?></textarea>
      </div>

</div>

    <div class="post-side-card">
      <div class="post-side-section">
        <div class="post-side-label">Status</div>
        <div class="post-status-toggle">
          <button type="button" class="post-status-btn <?= $vStatus === 'rascunho' ? 'is-active' : '' ?>" data-status="rascunho" onclick="setStatus('rascunho')">Rascunho</button>
          <button type="button" class="post-status-btn <?= $vStatus === 'publicado' ? 'is-active' : '' ?>" data-status="publicado" onclick="setStatus('publicado')">Publicado</button>
        </div>
      </div>

      <div class="post-side-section">
        <div class="post-side-label">Imagem destacada</div>
        <?php $srcPreview = $vImagem ? '../' . htmlspecialchars($vImagem, ENT_QUOTES, 'UTF-8') : ''; ?>
        <img id="img-preview" src="<?= $srcPreview ?>" alt="Imagem atual" style="<?= $vImagem ? '' : 'display:none;' ?>width:100%;border-radius:8px;margin-bottom:10px;object-fit:cover;max-height:160px;">
        <label class="post-img-upload" for="imagem-upload">
          <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
          </svg>
          <span id="img-label"><?= $vImagem ? 'Trocar imagem' : 'Enviar imagem' ?></span>
        </label>
        <input type="file" id="imagem-upload" name="imagem_file" accept="image/*" style="display:none;">
      </div>

      <div class="post-side-section">
        <label class="post-side-label" for="data_publicacao">Data de publicação</label>
        <input class="adm-form__input" type="date" id="data_publicacao" name="data_publicacao" value="<?= htmlspecialchars($vData) ?>">
      </div>

      <div class="post-side-actions">
        <button type="submit" class="btn btn-primary btn-full" onclick="setStatus('publicado'); syncEditor();">Publicar</button>
        <button type="submit" class="btn btn-ghost btn-full" onclick="setStatus('rascunho'); syncEditor();">Salvar rascunho</button>
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
  if (url) {
    document.getElementById('editor').focus();
    document.execCommand('createLink', false, url);
  }
}

function insertImg() {
  var url = prompt('URL da imagem:');
  if (url) {
    document.getElementById('editor').focus();
    document.execCommand('insertImage', false, url);
  }
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
  var label = document.getElementById('img-label');
  var reader = new FileReader();

  reader.onload = function(e) {
    preview.src = e.target.result;
    preview.style.display = 'block';
    label.textContent = file.name;
  };

  reader.readAsDataURL(file);
});

tinymce.init({
  license_key: 'gpl',
  selector: '#editor',
  height: 450,
  menubar: true,
  plugins: 'link lists image table code autolink preview searchreplace wordcount emoticons',
  toolbar: 'undo redo | styleselect | bold italic underline | ' +
           'alignleft aligncenter alignright alignjustify | ' +
           'bullist numlist outdent indent | link image table | code | ' +
           'searchreplace preview emoticons | coluna2',
  content_style: `
    body { font-family: Inter, sans-serif; font-size: 15px; }

    /* Layout de colunas dentro do editor */
    .layout-cols {
      display: flex;
      gap: 24px;
      align-items: flex-start;
      margin: 16px 0;
    }
    .layout-cols .col-text { flex: 1; min-width: 0; }
    .layout-cols .col-img  { flex: 0 0 40%; }
    .layout-cols .col-img img { width: 100%; height: auto; border-radius: 6px; }

    /* Mobile: empilha as colunas */
    @media (max-width: 640px) {
      .layout-cols { flex-direction: column; }
      .layout-cols .col-img { flex: 0 0 100%; }
    }
  `,
  // URL dinâmica: funciona em qualquer domínio
  images_upload_url: '<?= $baseUrl ?>/super-admin/upload_image.php',
  automatic_uploads: true,
  paste_data_images: true,
  relative_urls: false,
  remove_script_host: false,
  convert_urls: true,

  // Botão customizado: insere bloco de texto + imagem lado a lado
  setup: function (editor) {
    editor.ui.registry.addButton('coluna2', {
      text: '⬛ Texto + Imagem',
      tooltip: 'Inserir bloco: texto à esquerda, imagem à direita',
      onAction: function () {
        editor.insertContent(`
          <div class="layout-cols">
            <div class="col-text">
              <p>Digite o texto aqui...</p>
            </div>
            <div class="col-img">
              <img src="" alt="Descrição da imagem" />
            </div>
          </div>
          <p></p>
        `);
      }
    });
  },

  images_upload_handler: function (blobInfo, progress) {
    return new Promise(function (resolve, reject) {
      var xhr = new XMLHttpRequest();
      xhr.open('POST', '<?= $baseUrl ?>/super-admin/upload_image.php');
      xhr.upload.onprogress = function (e) {
        progress(e.loaded / e.total * 100);
      };
      xhr.onload = function () {
        if (xhr.status !== 200) { reject('Erro HTTP: ' + xhr.status); return; }
        var json;
        try { json = JSON.parse(xhr.responseText); } catch (e) {
          reject('Resposta inválida do servidor.'); return;
        }
        if (!json || typeof json.location !== 'string') {
          reject(json && json.error ? json.error : 'Upload falhou.'); return;
        }
        resolve(json.location);
      };
      xhr.onerror = function () { reject('Erro de rede ao enviar imagem.'); };
      var formData = new FormData();
      formData.append('file', blobInfo.blob(), blobInfo.filename());
      xhr.send(formData);
    });
  }
});
</script>

<?php require_once __DIR__ . '/includes/layout-footer.php'; ?>