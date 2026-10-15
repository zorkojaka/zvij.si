const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(process.env.ZVIJ_SMOKE_FILE || path.join(__dirname, '../wp-content/themes/zvij-theme/assets/smoke.js'), 'utf8');

function simulation(random) {
  let scheduled;
  let points = [];
  const context = {
    setTransform() {}, clearRect() { points = []; }, beginPath() {}, closePath() {}, fill() {},
    moveTo(x, y) { points.push([x, y]); }, lineTo(x, y) { points.push([x, y]); },
  };
  const canvas = { dataset: {}, getContext: () => context, setAttribute() {}, remove() {}, isConnected: false };
  const logo = { getBoundingClientRect: () => ({ left: 250, top: 500, width: 200, height: 200 }), addEventListener() {} };
  const math = Object.create(Math);
  math.random = random;
  vm.runInNewContext(source, {
    Math: math,
    document: {
      querySelector: () => logo, createElement: () => canvas,
      body: { prepend() { canvas.isConnected = true; } },
      documentElement: { dataset: { theme: 'light' } }, hidden: false, addEventListener() {},
    },
    window: { innerWidth: 800, innerHeight: 800, scrollY: 0, devicePixelRatio: 1,
      matchMedia: () => ({ matches: false, addEventListener() {} }), addEventListener() {} },
    requestAnimationFrame(callback) { scheduled = callback; return 1; },
    cancelAnimationFrame() {},
  });
  return now => { scheduled(now); return { points, x: Number(canvas.dataset.sourceX), y: Number(canvas.dataset.sourceY) }; };
}

const calm = simulation(() => 0);
const maximum = simulation(() => 1);
const values = [1, 0, 0, 0, 0.7, 0, 0.2, 0];
let index = 0;
const variable = simulation(() => values[index++ % values.length]);
let previousShift = 0;
let increases = false;
let decreases = false;
for (let now = 100; now <= 30000; now += 50) {
  const base = calm(now);
  const max = maximum(now);
  const gust = variable(now);
  assert.deepEqual(gust.points[0], base.points[0], 'Source stays attached to the logo');
  for (let i = 0; i < base.points.length; i++) {
    const distance = Math.max(0, base.y - base.points[i][1]);
    const limit = Math.min(distance * .55, 800) * .1 * Math.min(1, distance / 180);
    for (const test of [max, gust]) {
      const shift = test.points[i][0] - base.points[i][0];
      assert(shift >= -1e-8 && shift <= limit + 1e-8, 'Airflow remains left-to-right within 0-10 percent');
    }
  }
  const shift = gust.points[25][0] - base.points[25][0];
  assert(Math.abs(shift - previousShift) < 1, 'Wind changes smoothly without jumps');
  increases ||= shift > previousShift + .001;
  decreases ||= shift < previousShift - .001;
  previousShift = shift;
}
assert(increases && decreases, 'Random target strength both increases and decreases');
console.log('PASS wind: 0-10 percent, left-to-right only, smooth random variation, fixed source');
