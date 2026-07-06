const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, dismissTourIfVisible } = require('../helpers/plain-e2e');

test('guest requesting dashboard is redirected to login', async ({ page }) => {
  await page.goto(`${BASE_URL}/dashboard`);
  await expect(page).toHaveURL(/login/);
});

test('admin can authenticate and load dashboard', async ({ page }) => {
  await loginAsAdmin(page);
  await dismissTourIfVisible(page);
  await expect(page).toHaveURL(/dashboard|clients|projects|tasks|leads/);
  await expect(page.locator('.modal-backdrop')).not.toBeVisible();
});

test('dashboard is interactive after login with no blocking overlays', async ({ page }) => {
  await loginAsAdmin(page);

  // Tour may fire on first visit — dismiss it if present
  await dismissTourIfVisible(page);

  // No modal backdrops covering the UI
  await expect(page.locator('.modal-backdrop')).not.toBeVisible();

  // Dashboard stat boxes must be present and accessible
  await expect(page.locator('.small-box').first()).toBeVisible();
});

test('bootstrap tour close button dismisses tour and cookie persists across reload', async ({ browser }) => {
  // Use a fresh context with NO pre-set cookies so the tour actually fires.
  const context = await browser.newContext();
  const page = await context.newPage();

  await loginAsAdmin(page);

  const tour = page.locator('.popover.tour');
  const tourVisible = await tour.isVisible({ timeout: 3000 }).catch(() => false);

  if (!tourVisible) {
    // Tour is disabled server-side (TOUR_DISABLED=true) — nothing to test here.
    await context.close();
    return;
  }

  // Close via the × button
  await page.locator('.popover.tour [data-role="end"]').first().click();
  await expect(tour).not.toBeVisible();

  // Reload — tour must not reappear because the dismissal cookie was set
  await page.reload();
  await page.waitForLoadState('networkidle');
  await expect(tour).not.toBeVisible();

  await context.close();
});

test('login form shows error feedback for invalid credentials', async ({ page }) => {
  await page.goto(`${BASE_URL}/login`);
  await page.locator('input[name="email"]').fill('wrong@example.com');
  await page.locator('input[name="password"]').fill('wrong-password');
  await page.getByRole('button', { name: /log ?in|sign ?in/i }).click();

  await expect(page).toHaveURL(/login/);
  await expect(page.locator('.alert.alert-danger, .invalid-feedback, .help-block').first()).toBeVisible();
});

test('empty login submit shows validation feedback', async ({ page }) => {
  await page.goto(`${BASE_URL}/login`);
  await page.getByRole('button', { name: /log ?in|sign ?in/i }).click();

  await expect(page).toHaveURL(/login/);
  await expect(page.locator('.alert.alert-danger, .invalid-feedback, .help-block').first()).toBeVisible();
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
  await expect(page.locator('.alert.alert-danger, .invalid-feedback, .help-block').first()).toBeVisible();
});
