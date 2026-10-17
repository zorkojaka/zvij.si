/* Vršički po gramih: drsnik in številka sta ista izbira. V košarico gre
   variacija »1 g« s količino = grami; cena na gram pade na mejah (zvij-grams.php). */
(function ($) {
  var box = document.querySelector('[data-grams]');
  if (!box) { return; }
  var form = box.closest('form.variations_form');
  var range = box.querySelector('[data-grams-range]');
  var num = box.querySelector('[data-grams-num]');
  var offer = JSON.parse(box.getAttribute('data-offer') || '{}');
  var tiers = offer.tiers || [];
  var fmt = new Intl.NumberFormat('sl-SI', { style: 'currency', currency: 'EUR' });

  var scale = JSON.parse(range.getAttribute('data-scale') || '[[1,0],[50,100]]');
  // Položaj drsnika (0–1000) <-> grami, po odsekih med mejami.
  function toGrams(pos) {
    var pct = pos / 10;
    for (var i = 1; i < scale.length; i++) {
      var a = scale[i - 1], b = scale[i];
      if (pct <= b[1]) { return Math.round(a[0] + (pct - a[1]) / (b[1] - a[1]) * (b[0] - a[0])); }
    }
    return scale[scale.length - 1][0];
  }
  function toPos(g) {
    for (var i = 1; i < scale.length; i++) {
      var a = scale[i - 1], b = scale[i];
      if (g <= b[0]) { return Math.round((a[1] + (g - a[0]) / (b[0] - a[0]) * (b[1] - a[1])) * 10); }
    }
    return 1000;
  }

  function tierFor(g) {
    var t = tiers[0];
    tiers.forEach(function (x) { if (g >= x.min) { t = x; } });
    return t;
  }

  function nextTier(g) {
    for (var i = 0; i < tiers.length; i++) { if (tiers[i].min > g) { return tiers[i]; } }
    return null;
  }

  function kristali(n) {
    var r = n % 100;
    if (r === 1) { return n + ' kristal'; }
    if (r === 2) { return n + ' kristala'; }
    if (r === 3 || r === 4) { return n + ' kristali'; }
    return n + ' kristalov';
  }

  function pickVariation() {
    if (!form || !$) { return; }
    $(form).find('select[name^="attribute_"]').each(function () {
      var $s = $(this);
      var one = $s.find('option').filter(function () { return /^1\s*g$/i.test(this.value); }).first();
      if (one.length && $s.val() !== one.val()) { $s.val(one.val()).trigger('change'); }
    });
  }

  function update(g, from) {
    g = Math.max(1, Math.min(999, parseInt(g, 10) || 1));
    if (from !== 'num') { num.value = g; }
    if (from !== 'range') { range.value = toPos(g); }
    range.style.setProperty('--zv-fill', (range.value / 10) + '%');

    var t = tierFor(g);
    var total = Math.round(t.unit * g * 100) / 100;
    box.querySelector('[data-grams-total]').textContent = fmt.format(total);
    box.querySelector('[data-grams-unit]').textContent = fmt.format(t.unit) + '/g' + (t.pct ? ' · −' + t.pct + ' %' : '');
    var kr = Math.round(t.kr * g);
    box.querySelector('[data-grams-kr]').textContent = kr > 0 ? '+ ' + kristali(kr) + ' za člane' : '';
    var next = nextTier(g);
    box.querySelector('[data-grams-hint]').textContent = next
      ? 'Od ' + next.min + ' g samo ' + fmt.format(next.unit) + '/g.'
      : 'Najnižja cena na gram.';
    box.querySelectorAll('[data-grams-mark]').forEach(function (m) {
      m.classList.toggle('is-on', g >= parseInt(m.getAttribute('data-grams-mark'), 10));
    });

    var qty = form && form.querySelector('input.qty');
    if (qty) { qty.value = g; }
  }

  range.addEventListener('input', function () { update(toGrams(range.value), 'range'); });
  num.addEventListener('input', function () { if (num.value !== '') { update(num.value, 'num'); } });
  num.addEventListener('change', function () { update(num.value); });
  if ($ && form) {
    // Po izbiri variacije WooCommerce ponastavi količino na 1 — vrnemo grame.
    $(form).on('show_variation', function () { update(num.value); });
  }
  pickVariation();
  update(1);
})(window.jQuery);
