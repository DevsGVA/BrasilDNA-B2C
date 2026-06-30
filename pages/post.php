<?php
require_once __DIR__ . '/../includes/conexao.php';

$id   = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : 0;
$post = null;

if ($id > 0) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = :id AND status = "publicado"');
        $stmt->execute([':id' => $id]);
        $post = $stmt->fetch();
        if ($post) {
            require_once __DIR__ . '/../includes/stats.php';
            registrarStat($pdo, 'post', $id, 'cliques');
        }
    } catch (\PDOException $e) {
        $post = null;
    }
}

if (!$post) {
    header('Location: ' . BASE_URL . 'pages/news.php');
    exit;
}

$regionImages = [
    'Norte'        => 'https://images.unsplash.com/photo-1544731612-de7f96afe55f?w=1400&q=80',
    'Nordeste'     => 'https://images.unsplash.com/photo-1583531352515-8884af319dc1?w=1400&q=80',
    'Centro-Oeste' => 'https://images.unsplash.com/photo-1551632811-561732d1e306?w=1400&q=80',
    'Sudeste'      => 'https://images.unsplash.com/photo-1483729558449-99ef09a8c325?w=1400&q=80',
    'Sul'          => 'https://images.unsplash.com/photo-1543059080358-a20ce2f6c83f?w=1400&q=80',
];
$defaultImg = 'https://images.unsplash.com/photo-1483729558449-99ef09a8c325?w=1400&q=80';

$featuredImg = !empty($post['imagem'])
    ? (str_starts_with($post['imagem'], 'http')
        ? htmlspecialchars($post['imagem'], ENT_QUOTES, 'UTF-8')
        : BASE_URL . htmlspecialchars($post['imagem'], ENT_QUOTES, 'UTF-8'))
    : ($regionImages[$post['regiao'] ?? ''] ?? $defaultImg);

$rawDate  = !empty($post['data_publicacao']) ? $post['data_publicacao'] : ($post['criado_em'] ?? '');
$meses    = ['janeiro','fevereiro','março','abril','maio','junho','julho','agosto','setembro','outubro','novembro','dezembro'];
$postDate = '';
if ($rawDate) {
    $ts       = strtotime($rawDate);
    $postDate = date('d', $ts) . ' de ' . $meses[(int) date('n', $ts) - 1] . ' de ' . date('Y', $ts);
}

$resumo      = trim($post['resumo']   ?? '');
$regiao      = trim($post['regiao']   ?? '');
$regiaoSlug  = 'region--' . strtolower(str_replace([' ', '-'], '-', $regiao));
$conteudo = $post['conteudo'] ?? '';

$pageTitle   = htmlspecialchars($post['titulo'], ENT_QUOTES, 'UTF-8') . ' — Brasil DNA';
$currentPage = 'post';
require_once __DIR__ . '/../includes/site-header.php';
?>

<!-- ===== POST CONTENT ===== -->
<section class="post-section">
  <div class="container">
    <div class="post-inner">

      <a href="<?= BASE_URL ?>pages/news.php" class="post-back">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path d="M19 12H5M5 12l7-7M5 12l7 7"/>
        </svg>
        Voltar para News
      </a>

      <div class="post-meta-bar">
        <?php if ($regiao): ?>
          <span class="post-region-badge <?= $regiaoSlug ?>"><?= htmlspecialchars($regiao, ENT_QUOTES, 'UTF-8') ?></span>
        <?php endif; ?>
        <?php if ($postDate): ?>
          <span class="post-meta-date"><?= $postDate ?></span>
        <?php endif; ?>
      </div>

      <h1 class="post-title"><?= htmlspecialchars($post['titulo'], ENT_QUOTES, 'UTF-8') ?></h1>

      <div class="post-featured-img">
        <img src="<?= $featuredImg ?>"
             alt="<?= htmlspecialchars($post['titulo'], ENT_QUOTES, 'UTF-8') ?>"
             fetchpriority="high">
      </div>

      <?php if ($resumo): ?>
        <p class="post-excerpt"><?= htmlspecialchars($resumo, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>

      <?php if ($conteudo): ?>
        <div class="post-body"><?= $conteudo ?></div>
      <?php else: ?>
        <p style="color:var(--ink-soft);font-style:italic;">Content coming soon.</p>
      <?php endif; ?>

      <div class="post-footer-nav">
        <a href="<?= BASE_URL ?>pages/news.php" class="post-back">
          <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M5 12l7-7M5 12l7 7"/>
          </svg>
          Voltar para News
        </a>
      </div>

    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/site-footer.php'; ?>

