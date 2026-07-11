/**
 * Zvij.si kit sestavljalnik (/kiti/).
 * - Klik na kartico Black/Silver/Gold preklopi panel s komponentami tiste barve.
 * - Checkboxi določajo, kaj gre v košarico (privzeto vse dobavljivo).
 * - »Dodaj kit v košarico« doda izbrane izdelke enega za drugim prek
 *   WooCommerce AJAX (isti vzorec kot home.js) in sproži added_to_cart,
 *   da se osvežita števec košarice in analitika.
 */
(function () {
  'use strict';

  var builder = document.getElementById('kit-builder');
  if (!builder) {
    return;
  }

  function wcEndpoint(endpoint) {
    if (window.wc_add_to_cart_params && window.wc_add_to_cart_params.wc_ajax_url) {
      return window.wc_add_to_cart_params.wc_ajax_url.replace('%%endpoint%%', endpoint);
    }
    return window.location.origin + '/?wc-ajax=' + endpoint;
  }

  function activePanel() {
    return builder.querySelector('[data-kit-panel].is--active');
  }

  function formatEur(value) {
    return value.toFixed(2).replace('.', ',') + ' €';
  }

  function updateTotal() {
    var panel = activePanel();
    var total = 0;
    var count = 0;
    if (panel) {
      panel.querySelectorAll('[data-kb-item]:checked').forEach(function (box) {
        total += parseFloat(box.getAttribute('data-price') || '0');
        count += 1;
      });
    }
    var out = builder.querySelector('[data-kb-total]');
    if (out) {
      out.textContent = formatEur(total);
    }
    var add = builder.querySelector('[data-kb-add]');
    if (add) {
      add.disabled = count === 0;
    }
  }

  function setStatus(text) {
    var status = builder.querySelector('[data-kb-status]');
    if (status) {
      status.textContent = text;
    }
  }

  function selectKit(key) {
    builder.querySelectorAll('[data-kit-panel]').forEach(function (panel) {
      var on = panel.getAttribute('data-kit-panel') === key;
      panel.classList.toggle('is--active', on);
      panel.hidden = !on;
    });
    document.querySelectorAll('[data-kit-select]').forEach(function (card) {
      card.classList.toggle('is--active', card.getAttribute('data-kit-select') === key);
    });
    setStatus('');
    updateTotal();
  }

  document.querySelectorAll('[data-kit-select]').forEach(function (card) {
    card.addEventListener('click', function (event) {
      // klik kjerkoli na kartici izbere kit; gumb poskrbi še za skok na builder
      if (!event.target.closest('[data-kit-select-btn]')) {
        event.preventDefault();
      }
      selectKit(card.getAttribute('data-kit-select'));
    });
  });

  builder.addEventListener('change', function (event) {
    if (event.target.matches('[data-kb-item]')) {
      setStatus('');
      updateTotal();
    }
  });

  function addOne(productId) {
    var body = new URLSearchParams();
    body.set('product_id', productId);
    body.set('quantity', '1');
    return fetch(wcEndpoint('add_to_cart'), {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: body.toString()
    }).then(function (response) {
      if (!response.ok) {
        throw new Error('add_to_cart ' + response.status);
      }
      return response.json();
    }).then(function (data) {
      if (!data || data.error) {
        throw new Error('add_to_cart zavrnjen');
      }
      if (window.jQuery) {
        window.jQuery(document.body).trigger('added_to_cart', [data.fragments, data.cart_hash]);
      }
      return data;
    });
  }

  var addButton = builder.querySelector('[data-kb-add]');
  if (addButton) {
    addButton.addEventListener('click', function () {
      var panel = activePanel();
      if (!panel) {
        return;
      }
      var ids = Array.prototype.map.call(
        panel.querySelectorAll('[data-kb-item]:checked'),
        function (box) { return box.getAttribute('data-product-id'); }
      ).filter(Boolean);

      if (ids.length === 0) {
        return;
      }

      addButton.disabled = true;
      addButton.classList.add('is-loading');
      setStatus('Dodajam v košarico …');

      var chain = Promise.resolve();
      ids.forEach(function (id) {
        chain = chain.then(function () { return addOne(id); });
      });

      function izdelkovBeseda(n) {
        var m = n % 100;
        if (m === 1) { return 'izdelek'; }
        if (m === 2) { return 'izdelka'; }
        if (m === 3 || m === 4) { return 'izdelki'; }
        return 'izdelkov';
      }

      chain.then(function () {
        setStatus('Kit je v košarici (' + ids.length + ' ' + izdelkovBeseda(ids.length) + ').');
      }).catch(function () {
        setStatus('Nekaj je šlo narobe — preveri košarico in poskusi znova.');
      }).finally(function () {
        addButton.disabled = false;
        addButton.classList.remove('is-loading');
        updateTotal();
      });
    });
  }

  updateTotal();
})();
