<?php
// ════════════════════════════════════════════════════════════════
// ETAPA 1 — BAIXAR IMAGENS DO XML
// Acesse: super-admin/importar-imagens.php
// ════════════════════════════════════════════════════════════════

require_once __DIR__ . '/../includes/conexao.php';

define('XML_FILE',    __DIR__ . '/brasildna.WordPress.2026-06-30.xml');
define('MAP_FILE',    __DIR__ . '/imagens_map.json');
define('PROG_FILE',   __DIR__ . '/imagens_progresso.json');
define('UPLOAD_DIR',  __DIR__ . '/../uploads/posts/');
define('LOTE',        20);

if (!file_exists(XML_FILE)) {
    die('<p>❌ Arquivo XML não encontrado em: ' . XML_FILE . '</p>');
}

// ── Cria pasta uploads/posts/ se não existir ──────────────────
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Proteção PHP na pasta uploads
$htaccess = UPLOAD_DIR . '.htaccess';
if (!file_exists($htaccess)) {
    file_put_contents($htaccess, "Options -Indexes\n<FilesMatch '\\.php$'>\n  Deny from all\n</FilesMatch>\n");
}

// ── Lê ou inicia mapa e progresso ────────────────────────────
$map  = file_exists(MAP_FILE)  ? (json_decode(file_get_contents(MAP_FILE),  true) ?? []) : [];
$prog = file_exists(PROG_FILE) ? (json_decode(file_get_contents(PROG_FILE), true) ?? []) : [];
$processadas = $prog['processadas'] ?? 0;

// ── Coleta todas as URLs de imagem do XML ────────────────────
$urls = [];
$reader = new XMLReader();
$reader->open(XML_FILE, null, LIBXML_NOERROR | LIBXML_NOWARNING);
while ($reader->read()) {
    if ($reader->nodeType !== XMLReader::ELEMENT || $reader->name !== 'item') continue;
    $item  = new SimpleXMLElement($reader->readOuterXML());
    $ns    = $item->getNamespaces(true);
    $wp    = isset($ns['wp'])      ? $item->children($ns['wp'])      : null;
    $cnt   = isset($ns['content']) ? $item->children($ns['content']) : null;

    if ($wp && (string)$wp->post_type === 'attachment') {
        $u = trim((string)$wp->attachment_url);
        if ($u !== '') $urls[$u] = true;
    }

    $html = $cnt ? (string)$cnt->encoded : '';
    preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $m);
    foreach ($m[1] as $u) {
        $u = trim($u);
        if ($u !== '') $urls[$u] = true;
    }
}
$reader->close();

$todas     = array_keys($urls);
$total     = count($todas);
$restantes = array_slice($todas, $processadas);

// ── Função de download ───────────────────────────────────────
function baixarImagem(string $url, string $uploadDir): ?string
{
    $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','gif','webp'], true)) $ext = 'jpg';

    $filename = 'wp_' . md5($url) . '.' . $ext;
    $destino  = $uploadDir . $filename;

    if (file_exists($destino)) {
        return 'uploads/posts/' . $filename;
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        CURLOPT_HTTPHEADER     => [
            'Accept: image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
            'Accept-Language: pt-BR,pt;q=0.9,en;q=0.8',
            'Referer: https://novo.brasildna.com/',
        ],
    ]);
    $dados = curl_exec($ch);
    $http  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $mime  = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    if ($http !== 200 || empty($dados)) return null;

    $allowedMimes = ['image/jpeg','image/png','image/gif','image/webp'];
    $mimeLimpo    = strtolower(explode(';', $mime)[0]);
    if (!in_array($mimeLimpo, $allowedMimes, true)) return null;

    file_put_contents($destino, $dados);
    return 'uploads/posts/' . $filename;
}

// ── Processa lote atual ──────────────────────────────────────
$loteAtual  = array_slice($restantes, 0, LOTE);
$resultados = [];

foreach ($loteAtual as $url) {
    $local = baixarImagem($url, UPLOAD_DIR);
    $map[$url] = $local;
    $resultados[] = [
        'url'    => $url,
        'local'  => $local,
        'status' => $local ? '✅ baixada' : '❌ não é imagem (mime: text/html)',
    ];
    $processadas++;
}

file_put_contents(MAP_FILE,  json_encode($map,  JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
file_put_contents(PROG_FILE, json_encode(['processadas' => $processadas], JSON_PRETTY_PRINT));

$pct = $total > 0 ? round($processadas / $total * 100) : 100;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Etapa 1 — Importar Imagens</title>
<?php if (count($restantes) > LOTE): ?>
<meta http-equiv="refresh" content="3">
<?php endif; ?>
<style>
  body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 30px; color: #222; }
  h2   { color: #1a5c34; }
  .progress { background: #ddd; border-radius: 8px; height: 22px; margin: 16px 0; }
  .progress__bar { background: #1a5c34; height: 22px; border-radius: 8px; transition: width .4s; }
  table { border-collapse: collapse; width: 100%; font-size: 13px; margin-top: 20px; }
  th,td { border: 1px solid #ccc; padding: 6px 10px; text-align: left; }
  th    { background: #1a5c34; color: #fff; }
  .ok   { background: #d4edda; }
  .err  { background: #f8d7da; }
  .box  { background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 14px 18px; margin-top: 20px; font-size: 13px; }
  .box code { display: block; background: #eee; border-radius: 4px; padding: 4px 8px; margin: 4px 0; }
</style>
</head>
<body>

<h2>📥 Etapa 1 — Baixar Imagens</h2>
<p>📊 Total de imagens no XML: <strong><?= $total ?></strong></p>
<p>✅ Processadas até agora: <strong><?= $processadas ?></strong></p>
<p>⏳ Restantes: <strong><?= count($restantes) ?></strong></p>

<div class="progress">
  <div class="progress__bar" style="width:<?= $pct ?>%"></div>
</div>
<p><?= $pct ?>% concluído</p>

<?php if (count($restantes) <= LOTE): ?>
  <h3>🎉 Todas as imagens foram processadas!</h3>
  <p>✅ Baixadas com sucesso: <strong><?= array_sum(array_map(fn($v) => $v !== null ? 1 : 0, $map)) ?></strong></p>
  <p>Agora execute a <strong>Etapa 2</strong>: acesse <code>importar-posts.php</code></p>
  <div class="box">
    <strong>⚠️ Após concluir a Etapa 2, apague estes arquivos:</strong>
    <code>super-admin/importar-imagens.php</code>
    <code>super-admin/importar-posts.php</code>
    <code>super-admin/brasildna.WordPress.2026-06-30.xml</code>
    <code>super-admin/imagens_map.json</code>
    <code>super-admin/imagens_progresso.json</code>
  </div>
<?php else: ?>
  <p>⏳ Processando lote <?= ceil($processadas / LOTE) ?> — a página recarrega automaticamente a cada 3 segundos...</p>
<?php endif; ?>

<table>
  <thead>
    <tr><th>Status</th><th>URL original</th><th>Arquivo salvo</th></tr>
  </thead>
  <tbody>
    <?php foreach ($resultados as $r): ?>
    <tr class="<?= $r['local'] ? 'ok' : 'err' ?>">
      <td><?= htmlspecialchars($r['status']) ?></td>
      <td><?= htmlspecialchars($r['url']) ?></td>
      <td><?= $r['local'] ? htmlspecialchars($r['local']) : '—' ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

</body>
</html>