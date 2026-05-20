import { test, expect } from '@playwright/test';
import { PLAYWRIGHT_BASE_URL } from '../helpers/config';

test.describe('Authentication flows', () => {
  test('guest is redirected away from dashboard', async ({ page }) => {
    await page.goto(`${PLAYWRIGHT_BASE_URL}/dashboard`);
    await expect(page).toHaveURL(/login|signin/);
  });

  test('login page is accessible to guests', async ({ page }) => {
    await page.goto(`${PLAYWRIGHT_BASE_URL}/login`);
    await expect(page.getByRole('button', { name: /log ?in|sign ?in/i })).toBeVisible();
  });
});
