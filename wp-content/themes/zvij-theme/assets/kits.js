/**
 * Zvij.si Kit showcase interactions.
 * Each kit row lists its products as chips. Core products start selected,
 * optional add-ons start unselected. Clicking a chip toggles whether it is
 * included; the order panel reflects the selected count. Showcase only —
 * the "Naroči kit" CTA is a placeholder until checkout is ready.
 */
(function () {
  'use strict';

  function updateCount(kit) {
    var count = kit.querySelectorAll('[data-kit-product].is--selected').length;
    var output = kit.querySelector('[data-kit-count]');
    if (output) {
      output.textContent = String(count);
    }
    var cta = kit.querySelector('[data-kit-order]');
    if (cta) {
      cta.disabled = count === 0;
    }
  }

  function selectedItems(kit) {
    return Array.prototype.map.call(
      kit.querySelectorAll('[data-kit-product].is--selected'),
      function (chip) {
        return {
          id: chip.getAttribute('data-product-id') || '',
          sku: chip.getAttribute('data-sku') || '',
          label: chip.getAttribute('data-label') || ''
        };
      }
    );
  }

  function toggleChip(chip, kit) {
    var selected = chip.classList.toggle('is--selected');
    chip.setAttribute('aria-pressed', selected ? 'true' : 'false');
    updateCount(kit);
    var status = kit.querySelector('[data-kit-status]');
    if (status) {
      status.hidden = true;
    }
  }

  function resetKit(kit) {
    kit.querySelectorAll('[data-kit-product]').forEach(function (chip) {
      var def = chip.getAttribute('data-default') === '1';
      chip.classList.toggle('is--selected', def);
      chip.setAttribute('aria-pressed', def ? 'true' : 'false');
    });
    updateCount(kit);
    var status = kit.querySelector('[data-kit-status]');
    if (status) {
      status.hidden = true;
    }
  }

  function orderKit(kit) {
    var items = selectedItems(kit);
    var status = kit.querySelector('[data-kit-status]');
    if (!status) {
      return;
    }
    status.hidden = false;
    if (items.length === 0) {
      status.textContent = 'Izberi vsaj en izdelek.';
      return;
    }
    // Placeholder: collect the selection. Real bundle/cart wiring comes later.
    var skus = items
      .map(function (item) {
        return item.sku;
      })
      .filter(Boolean);
    var productIds = items
      .map(function (item) {
        return item.id;
      })
      .filter(Boolean);
    status.dataset.skus = skus.join(',');
    status.dataset.productIds = productIds.join(',');
    status.textContent =
      'Izbranih ' + items.length + ' izdelkov. Naročanje kita je v pripravi (dev).';
  }

  function initKit(kit) {
    updateCount(kit);
    kit.addEventListener('click', function (event) {
      var chip = event.target.closest('[data-kit-product]');
      if (chip && kit.contains(chip)) {
        event.preventDefault();
        toggleChip(chip, kit);
        return;
      }

      var reset = event.target.closest('[data-kit-reset]');
      if (reset && kit.contains(reset)) {
        event.preventDefault();
        resetKit(kit);
        return;
      }

      var order = event.target.closest('[data-kit-order]');
      if (order && kit.contains(order)) {
        event.preventDefault();
        orderKit(kit);
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-kit]').forEach(initKit);
  });
})();
