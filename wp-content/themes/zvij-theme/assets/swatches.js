/* Slicice razlicic: na kartici izberejo isti gumb kot izbira pod ceno (zamenja
   sliko, ceno in kaj gre v kosarico), na strani izdelka nastavijo izbiro razlicice. */
(function ($) {
  function press(group, id) {
    group.querySelectorAll('[data-swatch]').forEach(function (b) {
      b.setAttribute('aria-pressed', String(b.getAttribute('data-swatch') === String(id)));
    });
  }
  document.addEventListener('click', function (event) {
    var swatch = event.target.closest('[data-swatch]');
    if (swatch) {
      var group = swatch.closest('[data-swatches]');
      var id = swatch.getAttribute('data-swatch');
      press(group, id);
      var card = swatch.closest('.zv-pcard, .zv-carousel-card');
      var choice = card && card.querySelector('[data-variation-choice][data-variation-id="' + id + '"]');
      if (choice) { choice.click(); return; }
      var form = group.closest('form.variations_form');
      if (form && $) {
        var attrs = JSON.parse(swatch.getAttribute('data-attrs') || '{}');
        Object.keys(attrs).forEach(function (name) {
          $(form).find('[name="' + name + '"]').val(attrs[name]).trigger('change');
        });
      }
      return;
    }
    var picked = event.target.closest('[data-variation-choice]');
    if (picked) {
      var host = picked.closest('.zv-pcard, .zv-carousel-card');
      var swatches = host && host.querySelector('[data-swatches]');
      if (swatches) { press(swatches, picked.getAttribute('data-variation-id')); }
    }
  });
  if ($) {
    $(document).on('found_variation', 'form.variations_form', function (event, variation) {
      var group = this.querySelector('[data-swatches]');
      if (group && variation) { press(group, variation.variation_id); }
    });
  }
})(window.jQuery);
