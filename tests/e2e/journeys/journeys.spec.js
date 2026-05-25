const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin } = require('../helpers/plain-e2e');

test('the application does not expose a journeys index route today', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;

  /* Act */
  const response = await request.get(`${BASE_URL}/journeys`, {
    failOnStatusCode: false,
    maxRedirects: 0,
  });

  /* Assert */
  expect(response.status()).toBe(404);
});

test('the application does not expose a journeys create route today', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;

  /* Act */
  const response = await request.get(`${BASE_URL}/journeys/create`, {
    failOnStatusCode: false,
  });
  const body = await response.text();

  /* Assert */
  expect(response.status()).toBe(404);
  expect(body.toLowerCase()).toContain('not found');
});
