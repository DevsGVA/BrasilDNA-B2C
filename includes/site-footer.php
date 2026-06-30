<?php
require_once __DIR__ . '/config.php';
$v_js = file_exists(__DIR__ . '/../assets/main.js') ? filemtime(__DIR__ . '/../assets/main.js') : 1;
?>
<!-- ===== FOOTER ===== -->
<footer class="site-footer">
  <div class="container footer-top">
    <div class="footer-brand">
      <img src="<?= BASE_URL ?>assets/images/logo_brasilDNA_branco.png"
           alt="Brasil DNA" class="footer-logo" height="40" loading="lazy">
      <p>Experience the Essence of Brazil.</p>
      <div class="social-links">
        <a href="https://www.facebook.com/brasildna" target="_blank" rel="noopener" aria-label="Facebook">
          <i class="bi bi-facebook"></i>
        </a>
        <a href="https://www.instagram.com/dnabrasil_official" target="_blank" rel="noopener" aria-label="Instagram">
          <i class="bi bi-instagram"></i>
        </a>
        <a href="https://www.linkedin.com/company/global-vision-access/" target="_blank" rel="noopener" aria-label="LinkedIn">
          <i class="bi bi-linkedin"></i>
        </a>
      </div>
    </div>

    <nav class="footer-nav">
      <h4>Navigate</h4>
      <a href="<?= BASE_URL ?>index.php">Home</a>
      <a href="<?= BASE_URL ?>pages/about-us.php">About Us</a>
      <a href="<?= BASE_URL ?>pages/news.php">News</a>
      <a href="<?= BASE_URL ?>index.php#clients">Destinations</a>
    </nav>

    <div class="footer-presented">
      <h4>Initiative presented by</h4>
      <img src="<?= BASE_URL ?>assets/images/globalvisioaccess.svg"
           alt="GVA — Global Vision Access" loading="lazy">
    </div>
  </div>

  <div class="footer-partners-bar">
    <div class="container footer-partners-row">
      <img src="<?= BASE_URL ?>assets/images/Logotipo_Brasil.png" alt="Marca Brasil" loading="lazy">
      <div class="logo-embratur">
        <img src="<?= BASE_URL ?>assets/images/embratur.png" alt="" loading="lazy">
        <!-- <span class="embratur-wordmark"></span> -->
      </div>
      <img src="<?= BASE_URL ?>assets/images/Logotipo_Ministerio.png" alt="Ministério do Turismo" loading="lazy">
    </div>
  </div>

  <div class="footer-bottom">
    <div class="container painel">
      <p>&copy; <?= date('Y') ?> Brasil DNA. All rights reserved.</p>
      <a href="<?= BASE_URL ?>admin/login.php">Administrative Panel</a>
    </div>
  </div>
</footer>

<script>window.SITE_BASE = '<?= BASE_URL ?>';</script>
<script type="module" src="<?= BASE_URL ?>assets/main.js?v=<?= $v_js ?>"></script>
</body>
</html>
