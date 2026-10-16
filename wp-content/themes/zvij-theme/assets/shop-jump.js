/* Plavajoca vrstica skupin v trgovini: drzi se pod glavo (ce je glava pritrjena)
   in oznaci skupino, ki je trenutno na zaslonu. */
(function () {
  var nav = document.querySelector('[data-shop-jump]');
  if (!nav) { return; }
  var header = document.querySelector('.site-header');
  function offset() {
    var sticky = header && getComputedStyle(header).position === 'sticky';
    document.documentElement.style.setProperty('--zv-jump-top', (sticky ? header.getBoundingClientRect().height : 0) + 'px');
  }
  offset();
  window.addEventListener('resize', offset, { passive: true });

  var links = {};
  nav.querySelectorAll('[data-jump]').forEach(function (a) { links[a.getAttribute('data-jump')] = a; });
  function mark(key) {
    Object.keys(links).forEach(function (k) {
      links[k].classList.toggle('is-active', k === key);
      if (k === key) { links[k].setAttribute('aria-current', 'true'); } else { links[k].removeAttribute('aria-current'); }
    });
    var active = links[key];
    if (active && nav.scrollWidth > nav.clientWidth) {
      nav.scrollTo({ left: active.offsetLeft - nav.clientWidth / 2 + active.offsetWidth / 2, behavior: 'smooth' });
    }
  }
  // Oznacena je zadnja skupina, katere zacetek je ze pod vrstico.
  var sections = Array.prototype.slice.call(document.querySelectorAll('[data-shop-group]'));
  var ticking = false;
  function update() {
    ticking = false;
    var line = nav.getBoundingClientRect().bottom + 60;
    var current = sections.length ? sections[0].getAttribute('data-shop-group') : null;
    sections.forEach(function (s) { if (s.getBoundingClientRect().top <= line) { current = s.getAttribute('data-shop-group'); } });
    if (current) { mark(current); }
  }
  window.addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(update); } }, { passive: true });
  update();
})();
