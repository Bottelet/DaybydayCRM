const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin } = require('../helpers/plain-e2e');

test('guests trying to open the dashboard land on the login form', async ({ page }) => {
  /* Arrange */
  await page.goto(`${BASE_URL}/dashboard`);

  /* Act */
  const loginButton = page.getByRole('button', { name: /log ?in|sign ?in/i });

  /* Assert */
  await expect(page).toHaveURL(/login/);
  await expect(loginButton).toBeVisible();
});

test('admin login reaches the authenticated dashboard shell', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);

  /* Act */
  const clientsLink = page.locator('a[href*="/clients"]').first();

  /* Assert */
  await expect(page).not.toHaveURL(/login/);
  await expect(clientsLink).toBeVisible();
});
