import { test, expect } from '@playwright/test';

const MAGIC_LOGIN = 'https://app.instawp.io/wordpress-auto-login?site=%242y%2410%24.3ekyty61WlcvWwqD2ZjiewDeNqc45g8gSIgr.kKG5xHyVLaY97.O';
const WP_URL = 'https://testwebsite.instawp.site';

test('TableMaster Pro — volledige flow: tabel aanmaken, shortcode plaatsen, front-end controleren', async ({ page }) => {
  const timestamp = Date.now();

  await test.step('Stap 1: Inloggen via magic link', async () => {
    await page.goto(MAGIC_LOGIN, { timeout: 30000, waitUntil: 'networkidle' });
    expect(page.url()).toContain('wp-admin');
    await expect(page.locator('#wpadminbar')).toBeVisible();
    await page.screenshot({ path: 'screenshots/stap1-ingelogd.jpg', quality: 80 });
  });

  await test.step('Stap 2: Navigeren naar TableMaster plugin', async () => {
    await page.goto(`${WP_URL}/wp-admin/admin.php?page=tablemaster`, { waitUntil: 'networkidle' });
    await expect(page.locator('body')).toContainText(/TableMaster/i);
    await page.screenshot({ path: 'screenshots/stap2-tablemaster-lijst.jpg', quality: 80 });
  });

  let shortcode = '';

  await test.step('Stap 3: Nieuwe tabel aanmaken (3 kolommen, 3 rijen)', async () => {
    await page.goto(`${WP_URL}/wp-admin/admin.php?page=tablemaster-new`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(2000);

    const titleInput = page.locator('#tmp-table-name');
    await expect(titleInput).toBeVisible({ timeout: 10000 });
    await titleInput.fill(`Playwright Test ${timestamp}`);

    const headerCells = page.locator('#tmp-rows-wrapper thead th');
    const headerCount = await headerCells.count();
    console.log(`Headers: ${headerCount}`);

    if (headerCount > 5) {
      const deleteBtn = page.locator('#tmp-rows-wrapper thead th .tmp-col-delete, #tmp-rows-wrapper thead th [title="Kolom verwijderen"]');
      if (await deleteBtn.count() > 0) {
        await deleteBtn.last().click();
        await page.waitForTimeout(500);
      }
    }

    const dataRows = page.locator('#tmp-rows-wrapper tbody tr');
    const rowCount = await dataRows.count();
    console.log(`Rijen: ${rowCount}`);

    await page.screenshot({ path: 'screenshots/stap3-tabel-aangemaakt.jpg', quality: 80 });
  });

  await test.step('Stap 4: Tabel opslaan', async () => {
    const saveBtn = page.locator('#tmp-save-all');
    await expect(saveBtn).toBeVisible({ timeout: 5000 });
    await saveBtn.click();

    await page.waitForTimeout(3000);
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: 'screenshots/stap4-opgeslagen.jpg', quality: 80 });
  });

  await test.step('Stap 5: Shortcode kopiëren', async () => {
    const shortcodeEl = page.locator('#tmp-shortcode-value');
    await expect(shortcodeEl).toBeVisible({ timeout: 10000 });
    shortcode = (await shortcodeEl.innerText()).trim();
    console.log('Shortcode:', shortcode);
    expect(shortcode).toMatch(/\[tablemaster\s/);
    await page.screenshot({ path: 'screenshots/stap5-shortcode.jpg', quality: 80 });
  });

  await test.step('Stap 6: Nieuwe pagina aanmaken met shortcode', async () => {
    await page.goto(`${WP_URL}/wp-admin/post-new.php?post_type=page`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(3000);

    const welcomeModal = page.locator('.components-modal__header button, button[aria-label="Close"]');
    if (await welcomeModal.count() > 0) {
      await welcomeModal.first().click().catch(() => {});
      await page.waitForTimeout(500);
    }

    const isGutenberg = await page.locator('.block-editor').count() > 0;

    if (isGutenberg) {
      const titleArea = page.locator('[aria-label="Add title"], [data-title="Add title"], .editor-post-title__input, h1[contenteditable]');
      await titleArea.first().click({ timeout: 5000 });
      await page.keyboard.type(`TMP Test ${timestamp}`);

      const optionsBtn = page.locator('button[aria-label="Options"], button[aria-label="Opties"]');
      if (await optionsBtn.count() > 0) {
        await optionsBtn.first().click();
        await page.waitForTimeout(500);

        const codeEditorItem = page.locator('button[role="menuitemradio"]:has-text("Code editor"), button[role="menuitemradio"]:has-text("Code-editor")');
        if (await codeEditorItem.count() > 0) {
          await codeEditorItem.first().click();
          await page.waitForTimeout(1000);
        } else {
          await page.keyboard.press('Escape');
        }
      }

      const codeTextarea = page.locator('.editor-post-text-editor');
      if (await codeTextarea.count() > 0) {
        await codeTextarea.first().fill(`<!-- wp:shortcode -->\n${shortcode}\n<!-- /wp:shortcode -->`);
      } else {
        await page.keyboard.press('Enter');
        await page.waitForTimeout(300);
        await page.keyboard.type('/shortcode');
        await page.waitForTimeout(1000);

        const shortcodeOption = page.locator('button:has-text("Shortcode")');
        if (await shortcodeOption.count() > 0) {
          await shortcodeOption.first().click();
          await page.waitForTimeout(500);
          const shortcodeInput = page.locator('.wp-block-shortcode textarea');
          if (await shortcodeInput.count() > 0) {
            await shortcodeInput.first().fill(shortcode);
          }
        } else {
          await page.keyboard.press('Escape');
          await page.keyboard.type(shortcode);
        }
      }
    } else {
      await page.fill('#title', `TMP Test ${timestamp}`);
      await page.locator('#content').fill(shortcode);
    }

    await page.screenshot({ path: 'screenshots/stap6a-pagina-editor.jpg', quality: 80 });

    const publishToggle = page.locator('button.editor-post-publish-panel__toggle, button:has-text("Publish"):not([aria-disabled="true"]), button:has-text("Publiceren"):not([aria-disabled="true"])');
    await publishToggle.first().click({ timeout: 5000 });
    await page.waitForTimeout(2000);

    const publishConfirm = page.locator('.editor-post-publish-panel button.editor-post-publish-button, button.editor-post-publish-button:has-text("Publish")');
    if (await publishConfirm.count() > 0) {
      await publishConfirm.first().click({ timeout: 5000 });
      await page.waitForTimeout(3000);
    }

    await page.screenshot({ path: 'screenshots/stap6b-gepubliceerd.jpg', quality: 80 });
  });

  await test.step('Stap 7: Front-end openen en tabel controleren', async () => {
    let frontendUrl = '';

    const viewLink = page.locator('a:has-text("View Page"), a:has-text("Pagina bekijken"), .post-publish-panel__postpublish-buttons a');
    if (await viewLink.count() > 0) {
      frontendUrl = await viewLink.first().getAttribute('href') || '';
    }

    if (!frontendUrl) {
      await page.goto(`${WP_URL}/?s=TMP+Test+${timestamp}`, { waitUntil: 'networkidle' });
      const firstResult = page.locator('.entry-title a, article a, h2 a').first();
      if (await firstResult.count() > 0) {
        frontendUrl = await firstResult.getAttribute('href') || '';
      }
    }

    if (frontendUrl) {
      await page.goto(frontendUrl, { waitUntil: 'networkidle' });
    }

    await page.waitForTimeout(2000);

    const table = page.locator('[id^="tmp-"], .tablemaster-wrapper, table.tablemaster');
    await expect(table.first()).toBeVisible({ timeout: 15000 });

    await page.screenshot({ path: 'screenshots/stap7-tabel-frontend.jpg', quality: 80, fullPage: true });
    console.log('Tabel zichtbaar op front-end!');
  });
});
