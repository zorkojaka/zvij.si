/* Izbira različice: sličice in gumbi na kartici izberejo isto (home.js zamenja
   sliko, ceno in kaj gre v košarico), na strani izdelka pa nastavijo Woo izbiro. */
(function ($) {
  function press(host, id) {
    host.querySelectorAll('[data-swatch]').forEach(function (b) {
      b.setAttribute('aria-pressed', String(b.getAttribute('data-swatch') === String(id)));
    });
    host.querySelectorAll('[data-variation-choice]').forEach(function (b) {
      var on = b.getAttribute('data-variation-id') === String(id);
      b.classList.toggle('on', on);
      b.setAttribute('aria-pressed', String(on));
    });
  }
  function setForm(form, btn) {
    if (!$) { return; }
    var attrs = JSON.parse(btn.getAttribute('data-attrs') || '{}');
    Object.keys(attrs).forEach(function (name) {
      $(form).find('[name="' + name + '"]').val(attrs[name]).trigger('change');
    });
  }
  document.addEventListener('click', function (event) {
    var btn = event.target.closest('[data-swatch], [data-variation-choice]');
    if (!btn) { return; }
    var id = btn.getAttribute('data-swatch') || btn.getAttribute('data-variation-id');
    var form = btn.closest('form.variations_form');
    if (form) {
      press(form, id);
      setForm(form, btn);
      return;
    }
    var card = btn.closest('.zv-pcard, .zv-carousel-card');
    if (!card) { return; }
    press(card, id);
    if (btn.hasAttribute('data-swatch')) {
      var choice = card.querySelector('[data-variation-choice][data-variation-id="' + id + '"]');
      if (choice) { choice.click(); }
    }
  });
  // Ob nalaganju strani izdelka Woo obrazec dobi prvo (označeno) različico.
  document.querySelectorAll('form.variations_form [data-variation-choice].on').forEach(function (btn) {
    setForm(btn.closest('form.variations_form'), btn);
  });
  if ($) {
    $(document).on('found_variation', 'form.variations_form', function (event, variation) {
      if (variation) { press(this, variation.variation_id); }
    });
  }
})(window.jQuery);
