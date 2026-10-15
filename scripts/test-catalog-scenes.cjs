const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const base = process.env.ZVIJ_TEST_URL || 'https://dev.inteligent.si';

(async () => {
  const browser = await chromium.launch();
  try {
    const context = await browser.newContext({ reducedMotion: 'reduce' });
    const page = await context.newPage();
    for (const width of [1440, 390]) {
      await page.setViewportSize({ width, height: 1000 });
      for (const group of ['ziggi', 'cbd', 'pripomocki']) {
        await page.goto(`${base}/trgovina/?zvij_group=${group}`, { waitUntil: 'networkidle' });
        await page.locator('.zv-pcard__media').last().scrollIntoViewIfNeeded();
        await page.waitForFunction(() => [...document.querySelectorAll('.zv-pcard__media img')].every(i => i.complete && i.naturalWidth));
        const media = await page.locator('.zv-pcard__media').evaluateAll(elements => elements.map(e => {
          const image = e.querySelector('img');
          return { frameW: e.clientWidth, frameH: e.clientHeight, imageW: image.clientWidth,
            imageH: image.clientHeight, sourceW: image.naturalWidth, sourceH: image.naturalHeight };
        }));
        assert(media.length > 0);
        for (const image of media) {
          assert(Math.abs(image.frameW - image.frameH) <= 1, 'Square stable media frame');
          assert(Math.abs(image.imageW - image.frameW) <= 1 && Math.abs(image.imageH - image.frameH) <= 1, 'Image fills entire frame');
          assert.equal(image.sourceW, image.sourceH, 'Uncropped square source');
        }
        if (group === 'cbd') {
          for (const card of await page.locator('.zv-pcard').all()) {
            const image = card.locator('.zv-pcard__media img');
            const original = await image.getAttribute('src');
            await card.locator('[data-variation-choice]').nth(1).click();
            const multiple = await image.getAttribute('src');
            assert.notEqual(multiple, original, '5g switches to its own full photograph');
            assert.equal(await card.locator('.zv-pcard__media img').count(), 1, 'No overlapping rectangular photo clones');
            await card.locator('[data-variation-choice]').first().click();
            assert.equal(await image.getAttribute('src'), original, '1g restores original image');
          }
        }
        console.log(`PASS ${group} ${width}px: ${media.length} full-bleed square photos`);
      }
      await page.goto(`${base}/`, { waitUntil: 'networkidle' });
      const carouselCard = page.locator('[data-carousel-card]:not([aria-hidden="true"])').filter({ has: page.locator('[data-variation-choice]') }).first();
      const carouselImage = carouselCard.locator('.zv-carousel-card__image img');
      const single = await carouselImage.getAttribute('src');
      await carouselCard.locator('[data-variation-choice]').nth(1).evaluate(button => button.click());
      assert.notEqual(await carouselImage.getAttribute('src'), single, 'Carousel switches to multipack photograph');
      assert.equal(await carouselImage.count(), 1, 'Carousel uses one full-bleed photograph');
      const frame = await carouselCard.locator('.zv-carousel-card__image').boundingBox();
      assert(Math.abs(frame.width - frame.height) < 1, 'Carousel media is square');
      await carouselCard.locator('[data-variation-choice]').first().evaluate(button => button.click());
      assert.equal(await carouselImage.getAttribute('src'), single, 'Carousel restores 1g photograph');
      console.log(`PASS carousel ${width}px: square frame and 1g/5g photographs`);
      assert.equal(await page.locator('.zv-smoke').count(), 0, 'Reduced motion disables smoke');
    }
    await context.close();

    const smokeContext = await browser.newContext({ reducedMotion: 'no-preference' });
    const smokePage = await smokeContext.newPage({ viewport: { width: 1440, height: 1000 } });
    const errors = [];
    smokePage.on('pageerror', error => errors.push(error.message));
    await smokePage.goto(`${base}/o-nas/`, { waitUntil: 'networkidle' });
    await smokePage.waitForTimeout(300);
    const snapshot = () => smokePage.locator('.zv-smoke').evaluate(canvas => {
      const pixels = canvas.getContext('2d').getImageData(0, 0, canvas.width, canvas.height).data;
      let ink = 0;
      for (let i = 3; i < pixels.length; i += 64) { ink += pixels[i]; }
      return { ink, data: canvas.toDataURL(), pointer: getComputedStyle(canvas).pointerEvents };
    });
    const first = await snapshot();
    await smokePage.waitForTimeout(600);
    const second = await snapshot();
    assert(first.ink > 0 && second.ink > 0, 'Smoke canvas is nonblank');
    assert.notEqual(first.data, second.data, 'Smoke animates');
    assert.equal(first.pointer, 'none', 'Smoke never captures clicks');
    await smokePage.locator('.site-footer__brand img').scrollIntoViewIfNeeded();
    await smokePage.waitForTimeout(150);
    const anchor = await smokePage.evaluate(() => {
      const logo = document.querySelector('.site-footer__brand img').getBoundingClientRect();
      const canvas = document.querySelector('.zv-smoke');
      return { expectedX: logo.left + logo.width * .52, actualX: Number(canvas.dataset.sourceX),
        expectedY: logo.top + scrollY + logo.height * .34, actualY: Number(canvas.dataset.sourceY) };
    });
    assert(Math.abs(anchor.actualX - anchor.expectedX) < 2 && Math.abs(anchor.actualY - anchor.expectedY) < 2, 'Smoke remains anchored to footer I while scrolling');
    await smokePage.emulateMedia({ reducedMotion: 'reduce' });
    await smokePage.locator('.zv-smoke').waitFor({ state: 'detached' });
    assert.equal(await smokePage.locator('.zv-smoke').count(), 0, 'Live reduced-motion change stops and removes canvas');
    assert.deepEqual(errors, []);
    console.log('PASS smoke: animated, nonblank, anchored, noninteractive, reduced-motion safe');
    await smokeContext.close();
  } finally {
    await browser.close();
  }
})().catch(error => { console.error(error); process.exitCode = 1; });
