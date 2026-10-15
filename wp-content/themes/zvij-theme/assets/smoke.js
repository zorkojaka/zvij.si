(function () {
  'use strict';
  var logo = document.querySelector('.site-footer__brand img');
  var motion = window.matchMedia('(prefers-reduced-motion: reduce)');
  if (!logo) { return; }
  var canvas = document.createElement('canvas');
  canvas.className = 'zv-smoke';
  canvas.setAttribute('aria-hidden', 'true');
  var context = canvas.getContext('2d');
  if (!context) { return; }
  var frame = 0;
  var previous = 0;
  var time = 0;
  var width = 0;
  var height = 0;
  var sourceX = 0;
  var sourceY = 0;
  var dirty = true;

  function measure() {
    width = window.innerWidth;
    height = window.innerHeight;
    var ratio = Math.min(window.devicePixelRatio || 1, 1.25) * 0.65;
    canvas.width = Math.round(width * ratio);
    canvas.height = Math.round(height * ratio);
    context.setTransform(ratio, 0, 0, ratio, 0, 0);
    var bounds = logo.getBoundingClientRect();
    // The ember at the top of the I, below the leaf, in the brand SVG.
    sourceX = bounds.left + bounds.width * 0.52;
    sourceY = bounds.top + window.scrollY + bounds.height * 0.34;
    canvas.dataset.sourceX = sourceX.toFixed(1);
    canvas.dataset.sourceY = sourceY.toFixed(1);
    dirty = false;
  }

  function drift(distance, strand) {
    var spread = Math.min(150, Math.sqrt(Math.max(0, distance)) * 3);
    var phase = distance * 0.004 - time * 0.00022;
    var ease = Math.min(1, distance / 180);
    return sourceX + ease * spread * (
      Math.sin(phase + strand * 0.37) * 0.65 +
      Math.sin(phase * 0.53 + strand * 0.7 + 1.4) * 0.35
    );
  }

  function draw(now) {
    frame = 0;
    if (motion.matches) { canvas.remove(); return; }
    if (document.hidden) { return; }
    if (now - previous < (width < 600 ? 66 : 50)) { frame = requestAnimationFrame(draw); return; }
    time += previous ? Math.min(80, now - previous) : 0;
    previous = now;
    if (dirty) { measure(); }
    context.clearRect(0, 0, width, height);
    var scroll = window.scrollY;
    var start = Math.max(0, sourceY - scroll - height - 100);
    var end = sourceY - scroll + 100;
    var dark = document.documentElement.dataset.theme === 'dark';
    context.filter = 'blur(5px)';
    // One tapered ribbon per wisp, not a separate blurred draw for every segment.
    var stop = Math.min(sourceY, end);
    for (var strand = 0; strand < 4; strand += 1) {
      var points = [];
      for (var distance = start; distance <= stop; distance += 12) {
        points.push([drift(distance, strand), sourceY - distance - scroll, Math.min(24, Math.sqrt(distance) * 0.45)]);
      }
      points.push([drift(stop, strand), sourceY - stop - scroll, Math.min(24, Math.sqrt(Math.max(0, stop)) * 0.45)]);
      if (points.length < 2 || stop < start) { continue; }
      context.fillStyle = dark ? 'rgba(207,219,210,0.04)' : 'rgba(111,126,116,0.045)';
      context.beginPath();
      points.forEach(function (point, index) {
        context[index ? 'lineTo' : 'moveTo'](point[0] - point[2], point[1]);
      });
      points.reverse().forEach(function (point) { context.lineTo(point[0] + point[2], point[1]); });
      context.closePath();
      context.fill();
    }
    context.filter = 'none';
    frame = requestAnimationFrame(draw);
  }

  function sync() {
    if (frame) { cancelAnimationFrame(frame); frame = 0; }
    previous = 0;
    if (motion.matches) { canvas.remove(); return; }
    if (!canvas.isConnected) { document.body.prepend(canvas); }
    dirty = true;
    if (!document.hidden) { frame = requestAnimationFrame(draw); }
  }
  window.addEventListener('resize', function () { dirty = true; }, {passive: true});
  logo.addEventListener('load', function () { dirty = true; });
  if (window.ResizeObserver) {
    new ResizeObserver(function () { dirty = true; }).observe(document.body);
  }
  motion.addEventListener('change', sync);
  document.addEventListener('visibilitychange', sync);
  sync();
})();
