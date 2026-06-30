<?php
// ════════════════════════════════════════════════════════════════
// ETAPA 2 — IMPORTAR POSTS DO XML PARA O BANCO
// Acesse: super-admin/importar-posts.php
// ════════════════════════════════════════════════════════════════

require_once __DIR__ . '/../includes/conexao.php';

$pdo->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

define('XML_FILE',   __DIR__ . '/brasildna.WordPress.2026-06-30.xml');
define('MAP_FILE',   __DIR__ . '/imagens_map.json');
define('URL_ANTIGA', 'https://backupwp.brasildna.com/wp-content/uploads/');
define('URL_NOVA',   'https://novo.brasildna.com/uploads/posts/');

if (!file_exists(XML_FILE)) {
    die('<p>❌ XML não encontrado: ' . XML_FILE . '</p>');
}
if (!file_exists(MAP_FILE)) {
    die('<p>❌ imagens_map.json não encontrado. Execute a Etapa 1 primeiro.</p>');
}

$mapaImagens = json_decode(file_get_contents(MAP_FILE), true) ?? [];

// ════════════════════════════════════════════════════════════════
// FUNÇÕES
// ════════════════════════════════════════════════════════════════

function normalizarStatus(string $s): string {
    return strtolower(trim($s)) === 'publish' ? 'publicado' : 'rascunho';
}

function limpar(?string $t): ?string {
    if ($t === null) return null;
    $t = trim($t);
    return $t === '' ? null : $t;
}

function gerarResumo(?string $excerpt, ?string $content, int $max = 300): ?string {
    $e = limpar($excerpt);
    if ($e !== null) return mb_substr(strip_tags($e), 0, $max);
    $c = limpar($content);
    if ($c === null) return null;
    $t = trim(preg_replace('/\s+/', ' ', strip_tags($c)));
    return $t !== '' ? mb_substr($t, 0, $max) : null;
}

function detectarRegiao(array $cats): ?string {
    $regioes = ['Norte','Nordeste','Centro-Oeste','Sudeste','Sul'];
    foreach ($cats as $c) {
        foreach ($regioes as $r) {
            if (mb_strtolower(trim($c)) === mb_strtolower($r)) return $r;
        }
    }
    return null;
}

function substituirUrlsConteudo(string $html, array $mapa): string {
    $html = str_replace(URL_ANTIGA, URL_NOVA, $html);
    foreach ($mapa as $original => $local) {
        if ($local === null) continue;
        $novaUrl = 'https://novo.brasildna.com/' . $local;
        $html = str_replace($original, $novaUrl, $html);
    }
    return $html;
}

function imagemPrincipal(int $wpId, array $attachments, string $conteudo, array $mapa): ?string {
    $normalizarCaminho = function(?string $path): ?string {
        if ($path === null) return null;
        if (str_starts_with($path, 'uploads/posts/')) return $path;
        if (str_starts_with($path, 'uploads/')) {
            return 'uploads/posts/' . basename($path);
        }
        return $path;
    };

    // 1. Attachment vinculado ao post
    if (isset($attachments[$wpId]) && isset($mapa[$attachments[$wpId]])) {
        return $normalizarCaminho($mapa[$attachments[$wpId]]);
    }

    // 2. Primeira <img> do conteúdo
    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $conteudo, $m)) {
        $u = trim($m[1]);
        if (isset($mapa[$u])) return $normalizarCaminho($mapa[$u]);
    }

    return null;
}

// ════════════════════════════════════════════════════════════════
// LER XML
// ════════════════════════════════════════════════════════════════

$posts       = [];
$attachments = [];

$reader = new XMLReader();
$reader->open(XML_FILE, null, LIBXML_NOERROR | LIBXML_NOWARNING);

while ($reader->read()) {
    if ($reader->nodeType !== XMLReader::ELEMENT || $reader->name !== 'item') continue;

    $item = new SimpleXMLElement($reader->readOuterXML());
    $ns   = $item->getNamespaces(true);
    $wp   = isset($ns['wp'])      ? $item->children($ns['wp'])      : null;
    $cnt  = isset($ns['content']) ? $item->children($ns['content']) : null;
    $exc  = isset($ns['excerpt']) ? $item->children($ns['excerpt']) : null;

    if (!$wp) continue;

    $tipo   = (string) $wp->post_type;
    $postId = (int)    $wp->post_id;
    $parent = (int)    $wp->post_parent;

    if ($tipo === 'attachment') {
        $u = isset($wp->attachment_url) ? trim((string) $wp->attachment_url) : '';
        if ($parent > 0 && $u !== '') $attachments[$parent] = $attachments[$parent] ?? $u;
        continue;
    }

    if ($tipo !== 'post') continue;

    $cats = [];
    foreach ($item->category as $cat) {
        if (in_array((string) $cat['domain'], ['category','post_tag'], true)) {
            $cats[] = trim((string) $cat);
        }
    }

    $conteudo = $cnt ? (string) $cnt->encoded : '';
    $excerpt  = $exc ? (string) $exc->encoded : '';
    $rawDate  = trim((string) $wp->post_date);
    $dataPub  = ($rawDate !== '' && $rawDate !== '0000-00-00 00:00:00')
                ? date('Y-m-d', strtotime($rawDate))
                : null;
    $slug = trim((string) $wp->post_name);

    $posts[$postId] = [
        'titulo'          => trim((string) $item->title),
        'slug'            => $slug !== '' ? $slug : null,
        'resumo'          => gerarResumo($excerpt, $conteudo),
        'conteudo'        => limpar($conteudo),
        'regiao'          => detectarRegiao($cats),
        'status'          => normalizarStatus((string) $wp->status),
        'data_publicacao' => $dataPub,
        'wp_id'           => $postId,
    ];
}
$reader->close();

// ════════════════════════════════════════════════════════════════
// SUBSTITUIR URLS + DEFINIR IMAGEM
// ════════════════════════════════════════════════════════════════

foreach ($posts as $wpId => &$post) {
    if ($post['conteudo'] !== null) {
        $post['conteudo'] = substituirUrlsConteudo($post['conteudo'], $mapaImagens);
    }
    $post['imagem'] = imagemPrincipal($wpId, $attachments, $post['conteudo'] ?? '', $mapaImagens);
}
unset($post);

// ════════════════════════════════════════════════════════════════
// INSERIR NO BANCO
// ════════════════════════════════════════════════════════════════

$sql = "INSERT INTO posts
            (titulo, slug, resumo, conteudo, regiao, status, data_publicacao, imagem)
        VALUES
            (:titulo, :slug, :resumo, :conteudo, :regiao, :status, :data_publicacao, :imagem)";

$stmt       = $pdo->prepare($sql);
$importados = 0;
$erros      = [];

$pdo->beginTransaction();
try {
    foreach ($posts as $post) {
        if (trim($post['titulo']) === '') continue;
        $stmt->execute([
            ':titulo'          => $post['titulo'],
            ':slug'            => $post['slug'],
            ':resumo'          => $post['resumo'],
            ':conteudo'        => $post['conteudo'],
            ':regiao'          => $post['regiao'],
            ':status'          => $post['status'],
            ':data_publicacao' => $post['data_publicacao'],
            ':imagem'          => $post['imagem'],
        ]);
        $importados++;
    }
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    $erros[] = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Etapa 2 — Importar Posts</title>
<style>
  body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 30px; color: #222; }
  h2   { color: #1a5c34; }
  .ok  { color: #1a5c34; font-weight: bold; }
  .err { color: #c0392b; font-weight: bold; }
  .box { background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 14px 18px; margin-top: 20px; font-size: 13px; }
  .box code { display: block; background: #eee; border-radius: 4px; padding: 4px 8px; margin: 4px 0; }
  blockquote { background: #fdecea; border-left: 4px solid #c0392b; padding: 8px 14px; margin: 10px 0; border-radius: 4px; }
  code { font-family: monospace; font-size: 13px; }
</style>
</head>
<body>

<h2>📝 Etapa 2 — Importar Posts</h2>

<p>📝 Posts encontrados no XML: <strong><?= count($posts) ?></strong></p>
<p class="ok">✅ Posts importados com sucesso: <strong><?= $importados ?></strong></p>
<p class="err">❌ Erros: <strong><?= count($erros) ?></strong></p>

<?php if (!empty($erros)): ?>
  <h3>Erros encontrados:</h3>
  <?php foreach ($erros as $e): ?>
  <blockquote><?= htmlspecialchars($e) ?></blockquote>
  <?php endforeach; ?>
<?php else: ?>
  <h3>🎉 Importação concluída com sucesso!</h3>
<?php endif; ?>

<div class="box">
  <strong>⚠️ Apague agora todos os arquivos temporários:</strong>
  <code>super-admin/importar-imagens.php</code>
  <code>super-admin/importar-posts.php</code>
  <code>super-admin/brasildna.WordPress.2026-06-30.xml</code>
  <code>super-admin/imagens_map.json</code>
  <code>super-admin/imagens_progresso.json</code>
</div>

<p style="font-size:12px;color:#888;margin-top:20px;">
  Se o campo <code>slug</code> ainda não existe na tabela, execute antes:<br>
  <code>ALTER TABLE posts ADD COLUMN slug VARCHAR(250) NULL AFTER titulo, ADD UNIQUE KEY uniq_posts_slug (slug);</code>
</p>

</body>
</html>