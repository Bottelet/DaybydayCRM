const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin } = require('../helpers/plain-e2e');

test('journeys index route is not exposed', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  const response = await request.get(`${BASE_URL}/journeys`, {
    failOnStatusCode: false,
    maxRedirects: 0,
  });

  expect(response.status()).toBe(404);
});

test('journeys create route is not exposed', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  const response = await request.get(`${BASE_URL}/journeys/create`, {
    failOnStatusCode: false,
    maxRedirects: 0,
  });

  expect(response.status()).toBe(404);
});
