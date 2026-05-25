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

test('login rejects missing required credentials with explicit validation errors', async ({ page }) => {
  /* Arrange */
  const request = page.context().request;

  /* Act */
  const response = await request.post(`${BASE_URL}/login`, {
    failOnStatusCode: false,
    headers: {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    form: {},
  });
  const payload = await response.json();

  /* Assert */
  expect(response.status()).toBe(422);
  expect(payload.errors).toBeTruthy();
  expect(Object.keys(payload.errors)).toContain('email');
  expect(Object.keys(payload.errors)).toContain('password');
});

test('authenticated users can log out and are redirected back to login', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);

  /* Act */
  await page.goto(`${BASE_URL}/logout`);

  /* Assert */
  await expect(page).toHaveURL(/login/);
});
