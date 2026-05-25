const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin } = require('../helpers/plain-e2e');

test('unauthenticated users who request the dashboard are redirected to the login form', async ({ page }) => {
  /* Arrange – navigate without a session */
  await page.goto(`${BASE_URL}/dashboard`);

  /* Assert */
  await expect(page, 'Dashboard should not be accessible without authentication').toHaveURL(/login/);
  await expect(
    page.getByRole('button', { name: /log ?in|sign ?in/i }),
    'Login button should be visible on the redirect page'
  ).toBeVisible();
});

test('admin credentials unlock the authenticated dashboard shell', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);

  /* Assert */
  await expect(page, 'After login the user should leave the login page').not.toHaveURL(/login/);
  await expect(
    page.locator('a[href*="/clients"]').first(),
    'Clients navigation link should be visible in the authenticated shell'
  ).toBeVisible();
});

test('submitting the login form without credentials returns field-level validation errors', async ({ page }) => {
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
  expect(response.status(), 'Empty login submission should return 422').toBe(422);
  expect(payload.errors, 'Response must contain field-level validation errors').toBeTruthy();
  expect(Object.keys(payload.errors), 'The email field error must be present').toContain('email');
  expect(Object.keys(payload.errors), 'The password field error must be present').toContain('password');
});

test('wrong credentials are rejected and do not produce an authenticated session', async ({ page }) => {
  /* Arrange */
  const request = page.context().request;

  /* Act */
  const response = await request.post(`${BASE_URL}/login`, {
    failOnStatusCode: false,
    headers: {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    form: {
      email: 'wrong@example.com',
      password: 'wrongpassword',
    },
  });
  const payload = await response.json();

  /* Verify no authenticated session was created by attempting to access a protected endpoint */
  const protectedResponse = await request.get(`${BASE_URL}/dashboard`, {
    failOnStatusCode: false,
    maxRedirects: 0,
  });

  /* Assert – the server must refuse invalid credentials, not grant access */
  expect(response.status(), 'Wrong credentials should return 422').toBe(422);
  expect(payload.errors, 'A credential error must be returned').toBeTruthy();
  expect(
    [302, 401].includes(protectedResponse.status()),
    'Protected endpoint should be inaccessible without authentication (redirect or 401)'
  ).toBe(true);
});

test('an authenticated user who visits /logout is redirected back to the login page', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);

  /* Act */
  await page.goto(`${BASE_URL}/logout`);

  /* Assert */
  await expect(page, 'Logout should destroy the session and redirect to login').toHaveURL(/login/);
});
