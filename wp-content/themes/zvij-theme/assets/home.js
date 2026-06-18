/**
 * Zvij.si homepage interactions.
 * - DUBI: single-dimension variant selector (42 / 420).
 * - CBD/CBG vršički: sorta x količina selector resolved via an embedded map.
 * - Kit: color selector swaps the flat-lay image, link and caption.
 * Each selector updates the WooCommerce AJAX add-to-cart button's product id.
 */
(function () {
  'use strict';

  function setBuy(scope, pid, priceText) {
    if (priceText) {
      var out = scope.querySelector('[data-price-out]');
      if (out) {
        out.textContent = priceText;
      }
    }
    var add = scope.querySelector('.zh-add');
    if (add && pid) {
      add.setAttribute('data-product_id', pid);
      add.setAttribute('href', '?add-to-cart=' + pid);
    }
  }

  function activate(buttons, chosen) {
    buttons.forEach(function (b) {
      var on = b === chosen;
      b.classList.toggle('on', on);
      b.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
  }

  document.querySelectorAll('[data-zh-simple]').forEach(function (card) {
    var opts = Array.prototype.slice.call(card.querySelectorAll('[data-opt]'));
    opts.forEach(function (b) {
      b.addEventListener('click', function () {
        activate(opts, b);
        setBuy(card, b.getAttribute('data-pid'), b.getAttribute('data-price'));
      });
    });
  });

  document.querySelectorAll('[data-zh-vrs]').forEach(function (card) {
    var map = {};
    var raw = card.querySelector('#zh-vrs-map');
    if (raw) {
      try {
        map = JSON.parse(raw.textContent);
      } catch (e) {
        map = {};
      }
    }
    var sortas = Array.prototype.slice.call(card.querySelectorAll('[data-sorta]'));
    var kols = Array.prototype.slice.call(card.querySelectorAll('[data-kol]'));

    function update() {
      var s = card.querySelector('[data-sorta].on');
      var k = card.querySelector('[data-kol].on');
      if (!s || !k) {
        return;
      }
      var entry = map[s.getAttribute('data-sorta') + '|' + k.getAttribute('data-kol')];
      if (!entry) {
        return;
      }
      var out = card.querySelector('[data-price-out]');
      if (out && entry.price) {
        out.textContent = entry.price;
      }
      var link = card.querySelector('[data-vrs-link]');
      if (link && entry.url) {
        link.setAttribute('href', entry.url);
      }
      var img = card.querySelector('[data-vrs-img]');
      if (img && entry.img) {
        img.setAttribute('src', entry.img);
      }
    }

    sortas.forEach(function (b) {
      b.addEventListener('click', function () {
        activate(sortas, b);
        update();
      });
    });
    kols.forEach(function (b) {
      b.addEventListener('click', function () {
        activate(kols, b);
        update();
      });
    });
  });

  document.querySelectorAll('[data-kitsel]').forEach(function (block) {
    var colors = Array.prototype.slice.call(block.querySelectorAll('[data-color]'));
    var visual = block.querySelector('[data-kit-visual]');
    var link = block.querySelector('[data-kit-link]');
    var cap = block.querySelector('[data-kit-cap]');
    colors.forEach(function (b) {
      b.addEventListener('click', function () {
        activate(colors, b);
        var img = b.getAttribute('data-img');
        if (visual) {
          if (visual.tagName === 'IMG' && img) {
            visual.setAttribute('src', img);
          } else {
            visual.style.background = b.getAttribute('data-bg');
          }
        }
        if (link) {
          link.setAttribute('href', b.getAttribute('data-href'));
        }
        if (cap) {
          cap.textContent = b.getAttribute('data-name');
        }
      });
    });
  });
})();
