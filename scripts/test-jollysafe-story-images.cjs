const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const base = process.env.ZVIJ_TEST_URL || 'https://dev.inteligent.si';
async function dismiss(page) {
  const age = page.locator('#zvij-age [data-age="yes"]');
  if (await age.isVisible()) await age.click();
  const consent = page.getByRole('button', { name: 'Samo nujni', exact: true });
  if (await consent.isVisible()) await consent.click({ noWaitAfter: true });
}
(async () => {
  const browser = await chromium.launch();
  try {
    const context = await browser.newContext({ reducedMotion: 'reduce' });
    const page = await context.newPage();
    for (const width of [1440, 390]) {
      await page.setViewportSize({ width, height: 1000 });
      await page.goto(`${base}/izdelek/zvij-si-jollysafe/`, { waitUntil: 'networkidle' });
      await dismiss(page);
      const form = page.locator('form.variations_form');
      const image = page.locator('.woocommerce-product-gallery__image img').first();
      for (const [color, filename] of [['Zlata', 'jollysafe-gold-main-v2'], ['Srebrna', '244'], ['Črna', '243']]) {
        await form.getByRole('button', { name: color, exact: true }).click();
        await page.waitForFunction(({ filename }) => {
          const image = document.querySelector('.woocommerce-product-gallery__image img');
          return image.complete && image.naturalWidth > 0 && image.currentSrc.includes(filename);
        }, { filename });
        assert((await image.getAttribute('src')).includes(filename));
      }
      assert(await page.locator('.woocommerce-product-gallery__image').count() >= 3);
      const gallery = await page.locator('.woocommerce-product-gallery__image img').evaluateAll(es => es.map(e => e.src));
      assert(!gallery.some(src => /tulec-zlat-1|tulec-vse-barve|\/245(?:-|\.)/.test(src)), 'No incorrect gold images in gallery');
      await form.getByRole('button', { name: 'Zlata', exact: true }).click();
      await page.waitForFunction(() => document.querySelector('input.variation_id').value === '720');
      assert(!await form.locator('.single_add_to_cart_button').isDisabled());
      assert(!await page.evaluate(() => document.documentElement.scrollWidth > innerWidth));
      await image.evaluate(e => window.scrollTo(0, Math.max(0, e.getBoundingClientRect().top + scrollY - 240)));
      await page.screenshot({ path: `jollysafe-gold-${width}.png`, fullPage: false });
      console.log(`PASS ${width}px: existing color variants switch to correct images, gold purchasable, no overflow`);

      await page.goto(`${base}/trgovina/?zvij_group=pripomocki`, { waitUntil: 'networkidle' });
      const card = page.locator('[data-carousel-source="shop"][data-product-id="243"]');
      assert.equal(await card.count(), 1, 'One merged JollySafe shop card');
      await card.locator('[data-variation-choice]').filter({ hasText: 'Zlata' }).click();
      const cardImage = card.locator('xpath=ancestor::li[contains(@class,"product")]').locator('img').first();
      assert((await cardImage.getAttribute('src')).includes('jollysafe-gold-main-v2'));
      await page.goto(`${base}/kiti/`, { waitUntil: 'networkidle' });
      assert((await page.locator('[data-kit-select="gold"] img').getAttribute('src')).includes('gold-kit-real-v2.jpg'));

      await page.goto(`${base}/zgodba-zvij-si/`, { waitUntil: 'networkidle' });
      const cover = page.locator('.zv-article__media img');
      assert((await cover.getAttribute('src')).includes('zgodba-zvij-si-products-v2'));
      assert(await cover.evaluate(e => e.complete && e.naturalWidth > 0));
      assert(!await page.evaluate(() => document.documentElement.scrollWidth > innerWidth));
      await page.screenshot({ path: `zgodba-${width}.png`, fullPage: false });
      console.log(`PASS ${width}px: shop gold image, corrected kit, story cover loaded`);
    }
    await page.goto(`${base}/izdelek/zvij-si-jollysafe/`, { waitUntil: 'networkidle' });
    await page.locator('form.variations_form').getByRole('button', { name: 'Zlata', exact: true }).click();
    await page.waitForFunction(() => document.querySelector('input.variation_id').value === '720');
    await page.locator('.single_add_to_cart_button').click();
    await page.waitForLoadState('networkidle');
    await page.goto(`${base}/kosarica/`, { waitUntil: 'networkidle' });
    const row = page.locator('tr.cart_item');
    assert.equal(await row.count(), 1);
    assert((await row.innerText()).includes('Zlata'));
    assert((await row.locator('img').getAttribute('src')).includes('jollysafe-gold-main-v2'));
    await page.goto(await row.locator('.product-remove a').getAttribute('href'), { waitUntil: 'networkidle' });
    assert.equal(await page.locator('tr.cart_item').count(), 0);
    console.log('PASS real gold variation added through product UI and removed from isolated cart; no order placed');
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
