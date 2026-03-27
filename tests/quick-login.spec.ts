import { test, expect } from '@playwright/test';

const MAGIC_LOGIN = 'https://app.instawp.io/wordpress-auto-login?site=%242y%2410%24.3ekyty61WlcvWwqD2ZjiewDeNqc45g8gSIgr.kKG5xHyVLaY97.O';
const WP_URL = 'https://testwebsite.instawp.site';

test('Quick login test via magic link', async ({ page }) => {
  console.log('Navigating to magic login...');
  await page.goto(MAGIC_LOGIN, { timeout: 30000, waitUntil: 'networkidle' });
  console.log('After magic login, URL:', page.url());

  await page.screenshot({ path: 'screenshots/login-test.jpg', quality: 80 });

  const isAdmin = page.url().includes('wp-admin');
  const hasAdminBar = await page.locator('#wpadminbar').count() > 0;
  console.log('Is admin:', isAdmin, 'Has admin bar:', hasAdminBar);

  expect(isAdmin || hasAdminBar).toBeTruthy();
});
