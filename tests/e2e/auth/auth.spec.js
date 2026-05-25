const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin } = require('../helpers/plain-e2e');

test('guest requesting dashboard is redirected to login', async ({ page }) => {
  await page.goto(`${BASE_URL}/dashboard`);
  await expect(page).toHaveURL(/login/);
});

test('admin can authenticate and load dashboard', async ({ page }) => {
  await loginAsAdmin(page);
  await expect(page).toHaveURL(/dashboard|clients|projects|tasks|leads/);
});

test('login form shows error feedback for invalid credentials', async ({ page }) => {
  await page.goto(`${BASE_URL}/login`);
  await page.locator('input[name="email"]').fill('wrong@example.com');
  await page.locator('input[name="password"]').fill('wrong-password');
  await page.getByRole('button', { name: /log ?in|sign ?in/i }).click();

  await expect(page).toHaveURL(/login/);
  await expect(page.locator('.alert.alert-danger, .invalid-feedback').first()).toBeVisible();
});

test('empty login submit shows validation feedback', async ({ page }) => {
  await page.goto(`${BASE_URL}/login`);
  await page.getByRole('button', { name: /log ?in|sign ?in/i }).click();

  await expect(page).toHaveURL(/login/);
  await expect(page.locator('.alert.alert-danger, .invalid-feedback').first()).toBeVisible();
});

test('authenticated user can logout and loses dashboard access', async ({ page }) => {
  await loginAsAdmin(page);

  await page.goto(`${BASE_URL}/logout`);
  await expect(page).toHaveURL(/login/);

  await page.goto(`${BASE_URL}/dashboard`);
  await expect(page).toHaveURL(/login/);
});

test('forgot-password form rejects empty submit with validation feedback', async ({ page }) => {
  await page.goto(`${BASE_URL}/password/reset`);
  await page.getByRole('button', { name: /send password reset link|email password reset link/i }).click();
  await expect(page.locator('.alert.alert-danger, .invalid-feedback').first()).toBeVisible();
});
