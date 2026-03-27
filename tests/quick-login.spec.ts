import { test, expect } from '@playwright/test';

test('Quick login test', async ({ page }) => {
  console.log('Navigating to login page...');
  await page.goto('https://testwebsite.instawp.site/wp-login.php', { timeout: 30000 });
  console.log('Page loaded, title:', await page.title());

  await page.fill('#user_login', 'mysueno');
  await page.fill('#user_pass', 'MSP5muK9CNwDasLEBTg4');
  console.log('Credentials filled');

  await page.click('#wp-submit');
  console.log('Submit clicked');

  await page.waitForURL('**/wp-admin/**', { timeout: 30000 });
  console.log('Logged in! URL:', page.url());

  await page.screenshot({ path: 'screenshots/login-test.jpg', quality: 80 });
  console.log('Screenshot saved');
});
