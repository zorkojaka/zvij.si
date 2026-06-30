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

  function wcEndpoint(endpoint) {
    if (window.wc_add_to_cart_params && window.wc_add_to_cart_params.wc_ajax_url) {
      return window.wc_add_to_cart_params.wc_ajax_url.replace('%%endpoint%%', endpoint);
    }
    return window.location.origin + '/?wc-ajax=' + endpoint;
  }

  function updateCartCountFromFragments(fragments) {
    var updated = false;
    if (!fragments) {
      return updated;
    }
    Object.keys(fragments).some(function (selector) {
      if (selector.indexOf('.site-cart') === -1) {
        return false;
      }
      var wrap = document.createElement('div');
      wrap.innerHTML = fragments[selector];
      var count = wrap.querySelector('b');
      var live = document.querySelector('.site-cart b');
      if (count && live) {
        live.textContent = count.textContent;
        updated = true;
        return true;
      }
      return false;
    });
    return updated;
  }

  function incrementCartCount() {
    var live = document.querySelector('.site-cart b');
    if (!live) {
      return;
    }
    var current = parseInt(live.textContent || '0', 10);
    if (!Number.isNaN(current)) {
      live.textContent = String(current + 1);
    }
  }

  function initVariationAdds(scope) {
    scope.querySelectorAll('.zv-carousel-card').forEach(function (card) {
      var choices = Array.prototype.slice.call(card.querySelectorAll('[data-variation-choice]'));
      var add = card.querySelector('[data-variable-add]');
      var status = card.querySelector('[data-cart-status]');
      var price = card.querySelector('[data-price-out]');

      choices.forEach(function (choice) {
        choice.addEventListener('click', function () {
          activate(choices, choice);
          if (price && choice.getAttribute('data-price')) {
            price.textContent = choice.getAttribute('data-price');
          }
          if (add) {
            add.disabled = false;
            add.dataset.productId = choice.getAttribute('data-product-id') || '';
            add.dataset.variationId = choice.getAttribute('data-variation-id') || '';
            add.dataset.attrs = choice.getAttribute('data-attrs') || '{}';
          }
          if (status) {
            status.textContent = '';
          }
        });
      });

      if (!add) {
        return;
      }

      add.addEventListener('click', function () {
        if (add.disabled || add.classList.contains('is-loading')) {
          return;
        }

        var productId = add.dataset.productId;
        var variationId = add.dataset.variationId;
        if (!productId || !variationId) {
          if (status) {
            status.textContent = 'Najprej izberi količino.';
          }
          return;
        }

        var attrs = {};
        try {
          attrs = JSON.parse(add.dataset.attrs || '{}');
        } catch (e) {
          attrs = {};
        }

        var body = new URLSearchParams();
        body.set('add-to-cart', variationId);
        body.set('product_id', variationId);
        body.set('variation_id', variationId);
        body.set('quantity', '1');
        Object.keys(attrs).forEach(function (key) {
          body.set(key, attrs[key]);
          body.set('variation[' + key + ']', attrs[key]);
        });

        add.classList.add('is-loading');
        add.disabled = true;
        if (status) {
          status.textContent = 'Dodajam ...';
        }

        fetch(wcEndpoint('add_to_cart'), {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
          },
          body: body.toString()
        }).then(function (response) {
          return response.json();
        }).then(function (data) {
          if (!data || data.error) {
            throw new Error('add-to-cart failed');
          }
          if (!updateCartCountFromFragments(data.fragments)) {
            incrementCartCount();
          }
          if (window.jQuery) {
            window.jQuery(document.body).trigger('added_to_cart', [data.fragments, data.cart_hash, window.jQuery(add)]);
          }
          if (status) {
            status.textContent = 'Dodano v košarico.';
          }
        }).catch(function () {
          if (status) {
            status.textContent = 'Ni šlo. Poskusi še enkrat.';
          }
        }).finally(function () {
          add.classList.remove('is-loading');
          add.disabled = false;
        });
      });
    });
  }

  function initCarousel(root) {
    var viewport = root.querySelector('[data-carousel-viewport]');
    var track = root.querySelector('[data-carousel-track]');
    var prev = root.querySelector('[data-carousel-prev]');
    var next = root.querySelector('[data-carousel-next]');
    var originals = Array.prototype.slice.call(root.querySelectorAll('[data-carousel-card]'));
    if (!viewport || !track || originals.length === 0) {
      return;
    }

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var index = originals.length;
    var gap = 0;
    var cardWidth = 0;
    var timer = 0;
    var paused = false;
    var dragging = false;
    var dragStart = 0;
    var dragDelta = 0;

    function cloneCards() {
      originals.forEach(function (card) {
        var clone = card.cloneNode(true);
        clone.setAttribute('aria-hidden', 'true');
        clone.querySelectorAll('a, button').forEach(function (item) {
          item.setAttribute('tabindex', '-1');
        });
        track.appendChild(clone);
      });
      originals.slice().reverse().forEach(function (card) {
        var clone = card.cloneNode(true);
        clone.setAttribute('aria-hidden', 'true');
        clone.querySelectorAll('a, button').forEach(function (item) {
          item.setAttribute('tabindex', '-1');
        });
        track.insertBefore(clone, track.firstChild);
      });
    }

    function measure() {
      var first = track.querySelector('[data-carousel-card]');
      if (!first) {
        return;
      }
      var styles = window.getComputedStyle(track);
      gap = parseFloat(styles.columnGap || styles.gap || '0') || 0;
      cardWidth = first.getBoundingClientRect().width + gap;
      move(false);
    }

    function move(animate) {
      if (!cardWidth) {
        return;
      }
      track.style.transition = animate ? 'transform 420ms ease' : 'none';
      track.style.transform = 'translate3d(' + ((index * cardWidth * -1) + dragDelta) + 'px, 0, 0)';
    }

    function go(delta) {
      dragDelta = 0;
      index += delta;
      move(true);
    }

    function normalize() {
      if (index >= originals.length * 2) {
        index -= originals.length;
        move(false);
      }
      if (index < originals.length) {
        index += originals.length;
        move(false);
      }
    }

    function start() {
      if (reduceMotion || timer) {
        return;
      }
      timer = window.setInterval(function () {
        if (!paused && !dragging) {
          go(1);
        }
      }, 3600);
    }

    function stop() {
      if (timer) {
        window.clearInterval(timer);
        timer = 0;
      }
    }

    cloneCards();
    initVariationAdds(root);
    measure();
    start();

    track.addEventListener('transitionend', normalize);
    window.addEventListener('resize', measure);

    if (prev) {
      prev.addEventListener('click', function () {
        go(-1);
      });
    }
    if (next) {
      next.addEventListener('click', function () {
        go(1);
      });
    }

    root.addEventListener('mouseenter', function () {
      paused = true;
    });
    root.addEventListener('mouseleave', function () {
      paused = false;
    });
    root.addEventListener('focusin', function () {
      paused = true;
    });
    root.addEventListener('focusout', function () {
      paused = false;
    });

    viewport.addEventListener('keydown', function (event) {
      if (event.key === 'ArrowLeft') {
        event.preventDefault();
        go(-1);
      }
      if (event.key === 'ArrowRight') {
        event.preventDefault();
        go(1);
      }
    });

    viewport.addEventListener('pointerdown', function (event) {
      dragging = true;
      paused = true;
      dragStart = event.clientX;
      dragDelta = 0;
      viewport.setPointerCapture(event.pointerId);
      move(false);
    });

    viewport.addEventListener('pointermove', function (event) {
      if (!dragging) {
        return;
      }
      dragDelta = event.clientX - dragStart;
      move(false);
    });

    function endDrag(event) {
      if (!dragging) {
        return;
      }
      dragging = false;
      if (viewport.hasPointerCapture(event.pointerId)) {
        viewport.releasePointerCapture(event.pointerId);
      }
      if (Math.abs(dragDelta) > cardWidth * 0.18) {
        go(dragDelta < 0 ? 1 : -1);
      } else {
        dragDelta = 0;
        move(true);
      }
      paused = false;
    }

    viewport.addEventListener('pointerup', endDrag);
    viewport.addEventListener('pointercancel', endDrag);
    window.addEventListener('beforeunload', stop);
  }

  document.querySelectorAll('[data-zv-carousel]').forEach(initCarousel);
})();
