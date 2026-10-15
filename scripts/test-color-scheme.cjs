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
    system(dark) { media.matches = dark; mediaEvents.change(); },
    storage(newValue, eventKey = key) { windowEvents.storage({ key: eventKey, newValue }); },
  };
}

const light = environment();
assert.equal(light.root.dataset.theme, 'light');
assert.equal(light.button.hidden, true);
light.mount();
assert.equal(light.button.hidden, false);
assert.equal(light.button.attrs['aria-label'], 'Vklopi temno temo');
light.button.events.click();
assert.equal(light.root.dataset.theme, 'dark');
assert.equal(light.values[key], 'dark');
assert.equal(light.button.attrs['aria-pressed'], 'true');
assert.equal(light.button.attrs.title, 'Vklopi svetlo temo');
light.button.events.click();
assert.equal(light.root.dataset.theme, 'light');
assert.equal(light.values[key], 'light');
console.log('PASS toggle, accessible state and persistence');

const system = environment({ dark: true });
assert.equal(system.root.dataset.theme, 'dark');
system.mount();
system.system(false);
assert.equal(system.root.dataset.theme, 'light');
system.system(true);
assert.equal(system.root.dataset.theme, 'dark');
console.log('PASS OS preference applies before the body and follows changes');

for (const saved of ['light', 'dark']) {
  const explicit = environment({ dark: saved === 'light', saved });
  explicit.mount();
  assert.equal(explicit.root.dataset.theme, saved);
  explicit.system(false);
  explicit.system(true);
  assert.equal(explicit.root.dataset.theme, saved);
}
console.log('PASS explicit choice overrides OS preference');

const storage = environment({ dark: true });
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

const blocked = environment({ blocked: true, dark: true });
blocked.mount();
blocked.button.events.click();
assert.equal(blocked.root.dataset.theme, 'light');
blocked.button.events.click();
assert.equal(blocked.root.dataset.theme, 'dark');
assert.equal(environment({ saved: 'invalid', dark: true }).root.dataset.theme, 'dark');
console.log('PASS unavailable storage and invalid saved values');
