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
<script>
(function () {
  var selects = document.querySelectorAll('select[data-custom-select]');

  selects.forEach(function (select) {
    var wrap = document.createElement('div');
    wrap.className = 'cust-select';
    select.parentNode.insertBefore(wrap, select);
    wrap.appendChild(select);
    select.classList.add('cust-select__native');

    var trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'cust-select__trigger';
    wrap.appendChild(trigger);

    var list = document.createElement('ul');
    list.className = 'cust-select__list';
    wrap.appendChild(list);

    function labelFor(opt) { return opt.textContent; }

    function render() {
      var opts = Array.prototype.slice.call(select.options);
      trigger.textContent = labelFor(opts[select.selectedIndex] || opts[0]);
      list.innerHTML = '';
      opts.forEach(function (opt, i) {
        var li = document.createElement('li');
        li.textContent = labelFor(opt);
        li.className = 'cust-select__opt' + (i === select.selectedIndex ? ' is-selected' : '');
        li.addEventListener('click', function () {
          select.selectedIndex = i;
          select.dispatchEvent(new Event('change'));
          render();
          close();
        });
        list.appendChild(li);
      });
    }

    function open()  { wrap.classList.add('is-open'); }
    function close() { wrap.classList.remove('is-open'); }

    trigger.addEventListener('click', function (e) {
      e.stopPropagation();
      wrap.classList.contains('is-open') ? close() : open();
    });

    render();
  });

  document.addEventListener('click', function () {
    document.querySelectorAll('.cust-select.is-open').forEach(function (w) {
      w.classList.remove('is-open');
    });
  });
})();
</script>
</body>
</html>
