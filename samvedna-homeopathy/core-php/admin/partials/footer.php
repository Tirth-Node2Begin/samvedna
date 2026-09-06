    </div><!-- .content -->
  </div><!-- .main -->
</div><!-- .layout -->
<script>
/* Mobile sidebar drawer: toggle an off-canvas class on .layout. Progressive
   enhancement only — no app logic, and the desktop layout never uses it. */
(function () {
  var layout   = document.querySelector('.layout');
  var toggle   = document.querySelector('[data-nav-toggle]');
  if (!layout || !toggle) return;

  var backdrop = document.querySelector('[data-nav-backdrop]');
  var closeBtn = document.querySelector('[data-nav-close]');
  var links    = layout.querySelectorAll('.sidebar nav a');

  function setOpen(open) {
    layout.classList.toggle('is-nav-open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    document.body.style.overflow = open ? 'hidden' : '';
  }

  toggle.addEventListener('click', function () {
    setOpen(!layout.classList.contains('is-nav-open'));
  });
  if (backdrop) backdrop.addEventListener('click', function () { setOpen(false); });
  if (closeBtn) closeBtn.addEventListener('click', function () { setOpen(false); });
  links.forEach(function (a) { a.addEventListener('click', function () { setOpen(false); }); });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') setOpen(false);
  });
  window.addEventListener('resize', function () {
    if (window.innerWidth > 900) setOpen(false);
  });
})();
</script>
</body>
</html>
