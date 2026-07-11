/**
 * Zvij.si Plausible dogodki (samohostan, brez piškotkov).
 * Event model iz RELEASE_PLAN: view_item, add_to_cart, begin_checkout,
 * purchase (purchase se izpiše na thankyou strani prek PHP hooka).
 */
(function () {
  'use strict';

  if (typeof window.plausible !== 'function') {
    return;
  }

  var body = document.body;

  if (body.classList.contains('single-product')) {
    var title = document.querySelector('.product_title');
    window.plausible('view_item', {
      props: { product: title ? title.textContent.trim() : 'neznano' }
    });
  }

  if (body.classList.contains('woocommerce-checkout') && ! body.classList.contains('woocommerce-order-received')) {
    window.plausible('begin_checkout');
  }

  // Woo AJAX gumbi in zvij widgeti (home.js) oboji sprožijo added_to_cart.
  if (window.jQuery) {
    window.jQuery(document.body).on('added_to_cart', function (event, fragments, cartHash, button) {
      var el = button && button[0];
      var card = el && el.closest ? el.closest('li.product, .zv-carousel-card, .zv-card') : null;
      var name = '';
      if (card) {
        var heading = card.querySelector('.woocommerce-loop-product__title, h3, h2');
        if (heading) {
          name = heading.textContent.trim();
        }
      }
      window.plausible('add_to_cart', { props: { product: name || 'neznano' } });
    });
  }
})();
