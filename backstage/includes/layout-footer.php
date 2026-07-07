<?php
// footer idêntico ao super-admin/includes/layout-footer.php
?>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmFKfqCVDyZe+45f0bQ9IaL+Y/M" crossorigin="anonymous"></script>
<script>
(function () {
  var toggle  = document.getElementById('admMobToggle');
  var overlay = document.getElementById('admMobOverlay');
  var sidebar = document.querySelector('.adm-sidebar');
  if (!toggle) return;
  function openMenu()  { sidebar.classList.add('is-open');  overlay.classList.add('is-open'); }
  function closeMenu() { sidebar.classList.remove('is-open'); overlay.classList.remove('is-open'); }
  toggle.addEventListener('click', openMenu);
  overlay.addEventListener('click', closeMenu);
})();
</script>
</body>
</html>
