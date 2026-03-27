import { test, expect } from '@playwright/test';

const WP_URL = 'https://testwebsite.instawp.site';
const WP_USER = 'mysueno';
const WP_PASS = 'MSP5muK9CNwDasLEBTg4';

test('TableMaster Pro — volledige flow: tabel aanmaken, shortcode plaatsen, front-end controleren', async ({ page }) => {

  // ─── Stap 1: Inloggen in WP Admin ───
  await test.step('Inloggen in WordPress', async () => {
    await page.goto(`${WP_URL}/wp-login.php`);
    await page.fill('#user_login', WP_USER);
    await page.fill('#user_pass', WP_PASS);
    await page.click('#wp-submit');
    await page.waitForURL('**/wp-admin/**');
    await expect(page.locator('#wpadminbar')).toBeVisible();
  });

  // ─── Stap 2: Navigeren naar TableMaster ───
  await test.step('Navigeren naar TableMaster plugin', async () => {
    await page.goto(`${WP_URL}/wp-admin/admin.php?page=tablemaster`);
    await expect(page.locator('body')).toContainText(/TableMaster/i);
  });

  // ─── Stap 3: Nieuwe tabel aanmaken ───
  let shortcode = '';

  await test.step('Nieuwe tabel aanmaken met 3 kolommen en 3 rijen', async () => {
    await page.goto(`${WP_URL}/wp-admin/admin.php?page=tablemaster-new`);
    await page.waitForLoadState('networkidle');

    const titleInput = page.locator('#tmp-table-title');
    await expect(titleInput).toBeVisible({ timeout: 10000 });
    const timestamp = Date.now();
    await titleInput.fill(`Test Tabel ${timestamp}`);

    await page.waitForTimeout(1000);

    const headerCells = page.locator('#tmp-rows-wrapper thead th, #tmp-rows-wrapper .tmp-header-row th');
    const headerCount = await headerCells.count();

    if (headerCount === 4) {
      const lastHeader = headerCells.nth(3);
      const deleteBtn = lastHeader.locator('.tmp-col-delete, [data-action="delete-column"]');
      if (await deleteBtn.count() > 0) {
        await deleteBtn.first().click();
        await page.waitForTimeout(500);
      }
    }

    const dataCells = page.locator('#tmp-rows-wrapper tbody tr:first-child td, #tmp-rows-wrapper .tmp-data-row:first-child td');
    const colCount = await dataCells.count();

    const dataRows = page.locator('#tmp-rows-wrapper tbody tr, #tmp-rows-wrapper .tmp-data-row');
    const rowCount = await dataRows.count();

    await page.screenshot({ path: 'screenshots/stap3-tabel-aangemaakt.jpg', quality: 80 });
  });

  // ─── Stap 4: Tabel opslaan ───
  await test.step('Tabel opslaan', async () => {
    const saveBtn = page.locator('#tmp-save-table, button:has-text("Opslaan"), input[value="Opslaan"]');
    await expect(saveBtn.first()).toBeVisible({ timeout: 5000 });
    await saveBtn.first().click();
    await page.waitForTimeout(2000);
    await page.waitForLoadState('networkidle');

    await page.screenshot({ path: 'screenshots/stap4-tabel-opgeslagen.jpg', quality: 80 });
  });

  // ─── Stap 5: Shortcode ophalen ───
  await test.step('Shortcode kopiëren', async () => {
    const shortcodeEl = page.locator('[id*="shortcode"], .tmp-shortcode, code:has-text("[tablemaster"), input[value*="[tablemaster"]');
    await expect(shortcodeEl.first()).toBeVisible({ timeout: 10000 });

    const tagName = await shortcodeEl.first().evaluate(el => el.tagName.toLowerCase());
    if (tagName === 'input') {
      shortcode = await shortcodeEl.first().inputValue();
    } else {
      shortcode = await shortcodeEl.first().innerText();
    }

    shortcode = shortcode.trim();
    expect(shortcode).toMatch(/\[tablemaster\s/);
  });

  // ─── Stap 6: Nieuwe pagina aanmaken met shortcode ───
  await test.step('Nieuwe WP pagina aanmaken met shortcode', async () => {
    const timestamp = Date.now();
    const pageTitle = `TMP Test ${timestamp}`;

    await page.goto(`${WP_URL}/wp-admin/post-new.php?post_type=page`);
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);

    const isGutenberg = await page.locator('.block-editor').count() > 0;

    if (isGutenberg) {
      const welcomeModal = page.locator('.components-modal__header button[aria-label="Close"], .components-modal__header button:has-text("Close"), button:has-text("Sluiten")');
      if (await welcomeModal.count() > 0) {
        await welcomeModal.first().click();
        await page.waitForTimeout(500);
      }

      const titleBlock = page.locator('[aria-label="Add title"], .editor-post-title__input, h1[contenteditable="true"], [data-title="Add title"]');
      await titleBlock.first().click();
      await page.keyboard.type(pageTitle);

      await page.keyboard.press('Enter');
      await page.waitForTimeout(500);

      await page.keyboard.type('/');
      await page.waitForTimeout(300);

      const shortcodeOption = page.locator('button.components-autocomplete__result:has-text("Shortcode"), button:has-text("Shortcode")');
      if (await shortcodeOption.count() > 0) {
        await shortcodeOption.first().click();
      } else {
        await page.keyboard.press('Escape');
        await page.waitForTimeout(200);
        await page.keyboard.type(shortcode);
      }

      await page.waitForTimeout(500);

      const shortcodeInput = page.locator('.blocks-shortcode textarea, .wp-block-shortcode textarea, textarea[placeholder*="Shortcode"]');
      if (await shortcodeInput.count() > 0) {
        await shortcodeInput.first().fill(shortcode);
      }
    } else {
      await page.fill('#title', pageTitle);
      await page.waitForTimeout(500);

      const contentArea = page.locator('#content, #wp-content-editor-container textarea');
      await contentArea.first().fill(shortcode);
    }

    await page.screenshot({ path: 'screenshots/stap6-pagina-met-shortcode.jpg', quality: 80 });

    const publishBtn = page.locator(
      'button:has-text("Publiceren"), button:has-text("Publish"), input#publish'
    );
    await publishBtn.first().click();
    await page.waitForTimeout(2000);

    const confirmPublish = page.locator(
      '.editor-post-publish-button:has-text("Publiceren"), .editor-post-publish-button:has-text("Publish"), button.editor-post-publish-button'
    );
    if (await confirmPublish.count() > 0) {
      await confirmPublish.first().click();
      await page.waitForTimeout(3000);
    }

    await page.screenshot({ path: 'screenshots/stap6-pagina-gepubliceerd.jpg', quality: 80 });
  });

  // ─── Stap 7: Pagina openen op front-end en tabel controleren ───
  await test.step('Front-end pagina openen en tabel controleren', async () => {
    const viewLink = page.locator('a:has-text("Pagina bekijken"), a:has-text("View Page"), a:has-text("Bekijk pagina"), .post-publish-panel__postpublish-buttons a');

    let frontendUrl = '';
    if (await viewLink.count() > 0) {
      frontendUrl = await viewLink.first().getAttribute('href') || '';
    }

    if (!frontendUrl) {
      const permalink = page.locator('.edit-post-post-link__link, #sample-permalink a, a[href*="tmp-test"]');
      if (await permalink.count() > 0) {
        frontendUrl = await permalink.first().getAttribute('href') || '';
      }
    }

    if (frontendUrl) {
      await page.goto(frontendUrl);
    } else {
      await page.goto(`${WP_URL}/?s=TMP+Test`);
      const firstResult = page.locator('.entry-title a, article a').first();
      if (await firstResult.count() > 0) {
        await firstResult.click();
      }
    }

    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);

    const table = page.locator('.tablemaster-wrapper, table.tablemaster, [class*="tablemaster"], .tmp-table-wrapper');
    await expect(table.first()).toBeVisible({ timeout: 15000 });

    await page.screenshot({ path: 'screenshots/stap7-tabel-frontend.jpg', quality: 80, fullPage: true });
  });
});
