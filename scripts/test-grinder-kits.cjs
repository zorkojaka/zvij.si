const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const base = process.env.ZVIJ_TEST_URL || 'https://dev.inteligent.si';
const expected = {
  black: ['243', '211', '218', '441', '71'],
  silver: ['244', '211', '217', '435', '71'],
  gold: ['245', '213', '216', '437', '71'],
};
(async () => {
  const browser = await chromium.launch();
  try {
    const context = await browser.newContext({ reducedMotion: 'reduce' });
    const page = await context.newPage();
    for (const width of [1440, 390]) {
      await page.setViewportSize({ width, height: 1000 });
      for (const slug of ['srebrn-grinder-40-mm', 'zlat-grinder-52-mm']) {
        const response = await page.goto(`${base}/izdelek/${slug}/`, { waitUntil: 'networkidle' });
        assert.equal(response.status(), 200);
        const fixtureAgeConfirmation = page.locator('#zvij-age [data-age="yes"]');
        if (await fixtureAgeConfirmation.isVisible()) { await fixtureAgeConfirmation.click(); }
        const gallery = page.locator('.woocommerce-product-gallery__wrapper > .woocommerce-product-gallery__image');
        assert.equal(await gallery.count(), 3, 'Three product views visible');
        const photo = gallery.first().locator('img');
        assert((await photo.getAttribute('src')).includes('grinder-real-v1-'), 'New photograph replaces placeholder');
        const gap = await gallery.evaluateAll(elements => elements[1].getBoundingClientRect().top - elements[0].getBoundingClientRect().bottom);
        assert(gap >= 12, 'Product gallery spacing retained');
        console.log(`PASS ${slug} ${width}px: three photos and gallery spacing`);
      }
      await page.goto(`${base}/kiti/`, { waitUntil: 'networkidle' });
      const consent = page.getByRole('button', { name: 'Samo nujni', exact: true });
      if (await consent.isVisible()) { await consent.click({ noWaitAfter: true }); }
      for (const key of ['black', 'silver', 'gold']) {
        const photo = page.locator(`[data-kit-select="${key}"] img`);
        assert((await photo.getAttribute('src')).endsWith(`${key}-kit-real-v1.jpg`));
        const bounds = await photo.boundingBox();
        assert(Math.abs(bounds.width - bounds.height) < 1, 'Kit photograph fits square frame without cropping');
        await page.locator(`[data-kit-select-btn="${key}"]`).click({ noWaitAfter: true });
        const panel = page.locator(`[data-kit-panel="${key}"]`);
        assert(await panel.isVisible());
        const selected = await panel.locator('[data-kb-item]:checked').evaluateAll(es => es.map(e => e.dataset.productId));
        assert.deepEqual(selected, expected[key], `${key} defaults match photographed products`);
        assert.equal(await panel.locator('[data-kb-item]:disabled').count(), 0, 'All five actual products available');
        assert(!await page.locator('[data-kb-add]').isDisabled());
        console.log(`PASS ${key} ${width}px: actual available default components`);
      }
      assert((await page.locator('[data-kit-select="silver"]').innerText()).includes('črn Clipper'), 'Silver exception stated honestly');
      assert(!await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), 'No mobile horizontal overflow');
    }
    // Verify the existing kit purchase path using an isolated anonymous cart, without checkout.
    await page.goto(`${base}/kiti/`, { waitUntil: 'networkidle' });
    await page.locator('[data-kb-add]').click({ noWaitAfter: true });
    await page.waitForFunction(() => document.querySelector('[data-kb-status]').textContent.includes('Kit je v košarici'), null, { timeout: 30000 });
    await page.goto(`${base}/kosarica/`, { waitUntil: 'networkidle' });
    const cartIds = await page.locator('tr.cart_item .product-remove a').evaluateAll(es => es.map(e => e.getAttribute('data-product_id')).sort());
    assert.deepEqual(cartIds, expected.black.slice().sort());
    assert.equal(await page.locator('.zv-kit-crystals').count(), 1, 'Kit loyalty bonus remains available');
    for (let i = 0; i < 5 && await page.locator('.product-remove a').count(); i++) {
      await page.goto(await page.locator('.product-remove a').first().getAttribute('href'), { waitUntil: 'networkidle' });
    }
    assert.equal(await page.locator('tr.cart_item').count(), 0);
    console.log('PASS actual kit purchase, crystal bonus and isolated cart cleanup; no order placed');
    await context.close();
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
