/* Krogci skupin v trgovini (navpicen stolpec desno): oznaci trenutno skupino,
   ze prelistane skupine in pobarva crto do trenutne. Ob spremembi za kratek cas
   pokaze ime skupine. */
(function () {
  var nav = document.querySelector('[data-shop-jump]');
  if (!nav) { return; }
  var header = document.querySelector('.site-header');
  function headerBottom() {
    return header && getComputedStyle(header).position === 'sticky' ? header.getBoundingClientRect().bottom : 0;
  }
  function offset() { document.documentElement.style.setProperty('--zv-jump-top', headerBottom() + 'px'); }
  offset();
  window.addEventListener('resize', offset, { passive: true });

  var items = Array.prototype.slice.call(nav.querySelectorAll('[data-jump]'));
  var sections = Array.prototype.slice.call(document.querySelectorAll('[data-shop-group]'));
  var current = null, timer = null;

  function mark(key) {
    if (key === current) { return; }
    var index = -1;
    items.forEach(function (a, i) {
      var on = a.getAttribute('data-jump') === key;
      if (on) { index = i; }
      a.classList.toggle('is-active', on);
      if (on) { a.setAttribute('aria-current', 'true'); } else { a.removeAttribute('aria-current'); }
    });
    items.forEach(function (a, i) { a.classList.toggle('is-past', i < index); a.classList.remove('is-announce'); });
    nav.style.setProperty('--zv-jump-progress', items.length > 1 ? String(Math.max(0, index) / (items.length - 1)) : '0');
    if (current !== null && items[index]) {
      items[index].classList.add('is-announce');
      clearTimeout(timer);
      timer = setTimeout(function () { items[index].classList.remove('is-announce'); }, 1800);
    }
    current = key;
  }

  // Trenutna je zadnja skupina, katere zacetek je ze v zgornji tretjini zaslona.
  var ticking = false;
  function update() {
    ticking = false;
    var line = headerBottom() + window.innerHeight * 0.3;
    var key = sections.length ? sections[0].getAttribute('data-shop-group') : null;
    sections.forEach(function (s) { if (s.getBoundingClientRect().top <= line) { key = s.getAttribute('data-shop-group'); } });
    if (key) { mark(key); }
  }
  window.addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(update); } }, { passive: true });
  update();
})();
