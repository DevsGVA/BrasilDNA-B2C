    </main><!-- /adm-content -->
  </div><!-- /adm-main -->
</div><!-- /adm-shell -->

<script>
(function() {
  var toggle  = document.getElementById('admMobToggle');
  var overlay = document.getElementById('admMobOverlay');
  var sidebar = document.querySelector('.adm-sidebar');
  if (!toggle || !sidebar) return;

  function openSidebar()  { sidebar.classList.add('is-open'); overlay.classList.add('is-open'); }
  function closeSidebar() { sidebar.classList.remove('is-open'); overlay.classList.remove('is-open'); }

  toggle.addEventListener('click', function() {
    sidebar.classList.contains('is-open') ? closeSidebar() : openSidebar();
  });
  overlay.addEventListener('click', closeSidebar);
})();
</script>
</body>
</html>
