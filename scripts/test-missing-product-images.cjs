const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const base = process.env.ZVIJ_TEST_URL || 'https://dev.inteligent.si';
const slugs = ['ziggi-hemp-classic-slim', 'ziggi-mycelium-mystique-special-edition', 'ziggi-rockets-odyssey-special-edition', 'clipper-plin-za-polnjenje-16-ml'];
(async () => {
  const browser = await chromium.launch();
  try {
    const context = await browser.newContext({ reducedMotion: 'reduce' });
    const page = await context.newPage();
    for (const width of [1440, 390]) {
      await page.setViewportSize({ width, height: 1000 });
      for (const slug of slugs) {
        const response = await page.goto(`${base}/izdelek/${slug}/`, { waitUntil: 'networkidle' });
        assert.equal(response.status(), 200);
        const age = page.locator('#zvij-age [data-age="yes"]');
        if (await age.isVisible()) await age.click();
        const consent = page.getByRole('button', { name: 'Samo nujni', exact: true });
        if (await consent.isVisible()) await consent.click({ noWaitAfter: true });
        const gallery = page.locator('.woocommerce-product-gallery__wrapper > .woocommerce-product-gallery__image');
        assert(await gallery.count() >= 2, 'Two gallery images');
        const data = await gallery.locator('img').evaluateAll(images => images.map(image => ({ src: image.currentSrc, loaded: image.complete && image.naturalWidth > 0 })));
        assert(data.every(image => image.loaded && !image.src.includes('placeholder')));
        assert(new Set(data.map(image => image.src)).size >= 2);
        assert(await gallery.evaluateAll(es => es[1].getBoundingClientRect().top - es[0].getBoundingClientRect().bottom) >= 12);
        assert(!await page.evaluate(() => document.documentElement.scrollWidth > innerWidth));
        if (slug.startsWith('clipper')) assert((await page.locator('h1').innerText()).includes('16 ml'));
        await page.screenshot({ path: `${slug}-${width}.png`, fullPage: false });
        console.log(`PASS ${slug} ${width}px: images loaded, gallery spacing, no overflow`);
      }
    }
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
