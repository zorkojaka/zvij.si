const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const code = fs.readFileSync(path.join(__dirname, '../wp-content/themes/zvij-theme/assets/color-scheme.js'), 'utf8');
const key = 'zvij-color-scheme';

function environment({ dark = false, saved = null, blocked = false } = {}) {
  const domEvents = {};
  const windowEvents = {};
  const mediaEvents = {};
  const root = { dataset: {} };
  const values = { [key]: saved };
  let ready = false;
  const button = {
    hidden: true,
    attrs: {},
    events: {},
    setAttribute(name, value) { this.attrs[name] = value; },
    addEventListener(name, handler) { this.events[name] = handler; },
  };
  const media = { matches: dark, addEventListener(name, handler) { mediaEvents[name] = handler; } };
  const window = {
    matchMedia() { return media; },
    localStorage: {
      getItem(name) { if (blocked) throw Error('Storage disabled'); return values[name]; },
      setItem(name, value) { if (blocked) throw Error('Storage disabled'); values[name] = value; },
    },
    addEventListener(name, handler) { windowEvents[name] = handler; },
  };
  const document = {
    documentElement: root,
    querySelector() { return ready ? button : null; },
    addEventListener(name, handler) { domEvents[name] = handler; },
  };
  vm.runInNewContext(code, { window, document });
  return {
    root, button, values,
    mount() { ready = true; domEvents.DOMContentLoaded(); },
    storage(newValue, eventKey = key) { windowEvents.storage({ key: eventKey, newValue }); },
  };
}

const fresh = environment();
assert.equal(fresh.root.dataset.theme, 'dark');
assert.equal(fresh.button.hidden, true);
fresh.mount();
assert.equal(fresh.button.hidden, false);
assert.equal(fresh.button.attrs['aria-label'], 'Vklopi svetlo temo');
assert.equal(fresh.button.attrs['aria-pressed'], 'true');
fresh.button.events.click();
assert.equal(fresh.root.dataset.theme, 'light');
assert.equal(fresh.values[key], 'light');
assert.equal(fresh.button.attrs['aria-pressed'], 'false');
assert.equal(fresh.button.attrs.title, 'Vklopi temno temo');
fresh.button.events.click();
assert.equal(fresh.root.dataset.theme, 'dark');
assert.equal(fresh.values[key], 'dark');
console.log('PASS dark by default, toggle, accessible state and persistence');

assert.equal(environment({ dark: false }).root.dataset.theme, 'dark');
assert.equal(environment({ dark: true }).root.dataset.theme, 'dark');
console.log('PASS dark default regardless of OS preference');

for (const saved of ['light', 'dark']) {
  const explicit = environment({ saved });
  explicit.mount();
  assert.equal(explicit.root.dataset.theme, saved);
}
console.log('PASS saved choice wins over the default');

const storage = environment();
storage.mount();
storage.storage('light');
assert.equal(storage.root.dataset.theme, 'light');
storage.storage('dark', 'unrelated');
assert.equal(storage.root.dataset.theme, 'light');
storage.storage(null);
assert.equal(storage.root.dataset.theme, 'dark');
storage.storage('light');
storage.storage(null, null);
assert.equal(storage.root.dataset.theme, 'dark');
console.log('PASS cross-tab changes and storage clearing');

const blocked = environment({ blocked: true });
assert.equal(blocked.root.dataset.theme, 'dark');
blocked.mount();
blocked.button.events.click();
assert.equal(blocked.root.dataset.theme, 'light');
blocked.button.events.click();
assert.equal(blocked.root.dataset.theme, 'dark');
assert.equal(environment({ saved: 'invalid' }).root.dataset.theme, 'dark');
console.log('PASS unavailable storage and invalid saved values');
