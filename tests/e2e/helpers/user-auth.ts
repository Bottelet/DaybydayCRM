import { expect, type Page } from '@playwright/test';
import { PLAYWRIGHT_BASE_URL, TEST_USER_PASSWORD } from './config';

export async function registerAndLoginNewUser(page: Page) {
  const email = `pw_user_${Date.now()}_${Math.floor(Math.random() * 100000)}@example.com`;
  const name = 'Playwright User';

  const request = page.context().request;
  const registerPage = await request.get(`${PLAYWRIGHT_BASE_URL}/register`, { maxRedirects: 0 });
  const cookies = registerPage.headers()['set-cookie'] ?? '';
  const xsrfToken = decodeURIComponent(cookies.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '');

  const registerResponse = await request.post(`${PLAYWRIGHT_BASE_URL}/register`, {
    headers: {
      'X-XSRF-TOKEN': xsrfToken,
      Accept: 'text/html',
    },
    form: {
      name,
      email,
      password: TEST_USER_PASSWORD,
      password_confirmation: TEST_USER_PASSWORD,
      terms: 'on',
    },
    maxRedirects: 0,
  });

  if (registerResponse.status() !== 302) {
    await page.goto(`${PLAYWRIGHT_BASE_URL}/register`);
    await page.getByLabel('Name').fill(name);
    await page.getByLabel('Email').fill(email);
    await page.getByLabel('Password', { exact: true }).fill(TEST_USER_PASSWORD);
    await page.getByLabel('Confirm Password').fill(TEST_USER_PASSWORD);
    const terms = page.getByLabel(/terms/i);
    if (await terms.isVisible()) {
      await terms.check();
    }
    await page.getByRole('button', { name: /register/i }).click();
  }

  await page.goto(`${PLAYWRIGHT_BASE_URL}/dashboard`);
  await expect(page).toHaveURL(/dashboard/);

  return { email, name, password: TEST_USER_PASSWORD };
}
