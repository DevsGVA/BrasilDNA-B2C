<?php
require_once __DIR__ . '/../includes/conexao.php';

$posts = [];
try {
    $stmt  = $pdo->query('SELECT * FROM posts WHERE status = "publicado" ORDER BY data_publicacao IS NULL ASC, data_publicacao DESC');
    $posts = $stmt->fetchAll();
    if (!empty($posts)) {
        require_once __DIR__ . '/../includes/stats.php';
        foreach ($posts as $p) {
            registrarStat($pdo, 'post', (int) $p['id'], 'visualizacoes');
        }
    }
} catch (\PDOException $e) {
    $posts = [];
}

$regionImages = [
    'Norte'        => 'https://images.unsplash.com/photo-1544731612-de7f96afe55f?w=700&q=80',
    'Nordeste'     => 'https://images.unsplash.com/photo-1583531352515-8884af319dc1?w=700&q=80',
    'Centro-Oeste' => 'https://images.unsplash.com/photo-1551632811-561732d1e306?w=700&q=80',
    'Sudeste'      => 'https://images.unsplash.com/photo-1483729558449-99ef09a8c325?w=700&q=80',
    'Sul'          => 'https://images.unsplash.com/photo-1543059080358-a20ce2f6c83f?w=700&q=80',
];
$defaultImg = 'https://images.unsplash.com/photo-1483729558449-99ef09a8c325?w=700&q=80';

function postImage(array $post, array $map, string $default): string {
    $saved = $post['imagem'] ?? '';
    if ($saved) {
        $path = htmlspecialchars($saved, ENT_QUOTES, 'UTF-8');
        return str_starts_with($saved, 'http') ? $path : BASE_URL . $path;
    }
    return $map[$post['regiao'] ?? ''] ?? $default;
}

function postDate(array $post): string {
    $raw = !empty($post['data_publicacao']) ? $post['data_publicacao'] : ($post['criado_em'] ?? '');
    return $raw ? date('d/m/Y', strtotime($raw)) : '';
}

$pageTitle   = 'News & Inspiration — Brasil DNA';
$currentPage = 'news';
require_once __DIR__ . '/../includes/site-header.php';
?>

<!-- ===== HERO ===== -->
<section class="page-hero">
  <div class="page-hero-bg">
    <img src="https://images.unsplash.com/photo-1619546952812-520e98064a52?w=2000&q=80"
         alt="Brasil DNA News" class="page-hero-img" fetchpriority="high">
    <div class="page-hero-overlay"></div>
  </div>

  <div class="hero-flag-stripe" aria-hidden="true">
    <span class="stripe stripe--green"></span>
    <span class="stripe stripe--yellow"></span>
    <span class="stripe stripe--green"></span>
  </div>

  <div class="page-hero-body" data-reveal>
    <span class="label-tag label-tag--light">Latest Stories</span>
    <h1>News &amp; <em>Inspiration</em></h1>
    <p class="page-hero-lead">Stories, guides, and insights from the heart of Brazil.</p>
  </div>
</section>

<!-- ===== FILTROS ===== -->
<div class="news-filter-bar">
  <div class="container">
    <div class="news-filters" role="group" aria-label="Filtrar por região">
      <button class="filter-btn is-active" data-filter="all">Todos</button>
      <button class="filter-btn" data-filter="Nordeste">Nordeste</button>
      <button class="filter-btn" data-filter="Centro-Oeste">Centro-Oeste</button>
      <button class="filter-btn" data-filter="Sul">Sul</button>
      <button class="filter-btn" data-filter="Sudeste">Sudeste</button>
      <button class="filter-btn" data-filter="Norte">Norte</button>
    </div>
  </div>
</div>

<!-- ===== NEWS LISTING ===== -->
<section class="news-page-section">
  <div class="container">

    <?php if (count($posts) > 0): ?>
      <div class="news-grid" id="newsGrid">
        <?php foreach ($posts as $i => $post):
          $img         = postImage($post, $regionImages, $defaultImg);
          $date        = postDate($post);
          $regiao      = trim($post['regiao'] ?? '');
          $regiaoSlug  = 'region--' . strtolower(str_replace([' ', '-'], '-', $regiao));
        ?>
          <article class="news-card" data-region="<?= htmlspecialchars($regiao, ENT_QUOTES, 'UTF-8') ?>">
            <a href="<?= BASE_URL ?>pages/post.php?id=<?= (int) $post['id'] ?>" class="news-img-link">
              <img src="<?= $img ?>"
                   alt="<?= htmlspecialchars($post['titulo'], ENT_QUOTES, 'UTF-8') ?>"
                   loading="<?= $i < 3 ? 'eager' : 'lazy' ?>">
            </a>
            <div class="news-body">
              <?php if ($date || $regiao): ?>
                <div class="news-card-meta">
                  <?php if ($regiao): ?>
                    <span class="news-region <?= $regiaoSlug ?>"><?= htmlspecialchars($regiao, ENT_QUOTES, 'UTF-8') ?></span>
                  <?php endif; ?>
                  <?php if ($regiao && $date): ?>
                    <span class="news-meta-sep">·</span>
                  <?php endif; ?>
                  <?php if ($date): ?>
                    <span class="news-date"><?= $date ?></span>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
              <h3>
                <a href="<?= BASE_URL ?>pages/post.php?id=<?= (int) $post['id'] ?>">
                  <?= htmlspecialchars($post['titulo'], ENT_QUOTES, 'UTF-8') ?>
                </a>
              </h3>
              <a href="<?= BASE_URL ?>pages/post.php?id=<?= (int) $post['id'] ?>" class="news-more">Read more →</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

    <?php else: ?>
      <div class="news-empty">
        <div class="news-empty-icon">
          <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2">
            <path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l6 6v8a2 2 0 01-2 2z"/>
            <path d="M17 21v-8H7v8M7 3v5h8"/>
          </svg>
        </div>
        <h3>No stories published yet</h3>
        <p>Check back soon — exciting stories from Brazil are on their way.</p>
      </div>
    <?php endif; ?>

  </div>
</section>

<script>
(function () {
  var btns  = document.querySelectorAll('.filter-btn');
  var cards = document.querySelectorAll('#newsGrid .news-card');

  btns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      btns.forEach(function (b) { b.classList.remove('is-active'); });
      btn.classList.add('is-active');
      var filter = btn.dataset.filter;
      cards.forEach(function (card) {
        var show = filter === 'all' || card.dataset.region === filter;
        card.style.display = show ? '' : 'none';
      });
    });
  });
}());
</script>

<?php require_once __DIR__ . '/../includes/site-footer.php'; ?>

