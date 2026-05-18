import { test, expect } from '@playwright/test';
import { PLAYWRIGHT_BASE_URL } from '../helpers/config';

test.describe('Password and session authentication UX', () => {
  test('forgot password page is available', async ({ page }) => {
    await page.goto(`${PLAYWRIGHT_BASE_URL}/password/reset`);
    await expect(page.getByRole('button', { name: /send password reset link|email password reset link/i })).toBeVisible();
  });

  test('login page exposes remember me option', async ({ page }) => {
    await page.goto(`${PLAYWRIGHT_BASE_URL}/login`);
    await expect(page.getByLabel(/remember me/i)).toBeVisible();
  });
});
