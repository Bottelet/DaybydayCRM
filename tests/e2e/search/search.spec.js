const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createClient, uniqueValue } = require('../helpers/plain-e2e');

test('navbar search widget renders as a real input, not an empty Vue mount', async ({ page }) => {
  await loginAsAdmin(page);
  await page.goto(`${BASE_URL}/dashboard`);

  // Regression guard: the navbar's <search></search> tag is a Vue component
  // (resources/assets/js/components/Search.vue). Vue previously never
  // mounted anywhere in the app (see resources/assets/js/app.js /
  // vite.config.mjs), so this rendered as an empty custom element with no
  // visible input at all.
  await page.locator('.search-button').click();
  await expect(page.locator('.search-input')).toBeVisible();
  await expect(page.locator('.search-input')).toHaveAttribute('placeholder', /search term/i);
});

test('search returns client hit structure for a newly created company', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const companyName = uniqueValue('PW Search Client');
  await createClient(page, request, companyName);

  const response = await request.get(`${BASE_URL}/search/${encodeURIComponent(companyName)}/client`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
  const payload = await response.json();

  expect(response.status()).toBe(200);
  expect(payload).toHaveProperty('hits');
  expect(payload.hits).toHaveProperty('hits');
  expect(Array.isArray(payload.hits.hits)).toBe(true);
  expect(payload.hits.hits.length).toBeGreaterThan(0);
  expect(JSON.stringify(payload)).toContain(companyName);
  expect(JSON.stringify(payload)).toContain('/clients/');
});

test('search rejects unsupported type with explicit 400 error', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  const response = await request.get(`${BASE_URL}/search/test/invoice`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });

  const payload = await response.json();
  expect(response.status()).toBe(400);
  expect(payload.error).toBe('Invalid search type');
});

test('search type matching is case-insensitive', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  const response = await request.get(`${BASE_URL}/search/Test/CLIENT`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });

  expect(response.status()).toBe(200);
  const payload = await response.json();
  expect(payload).toHaveProperty('hits');
});

const supportedTypes = ['client', 'clients', 'task', 'project', 'lead', 'user'];

for (const type of supportedTypes) {
  test(`search accepts supported domain type: ${type}`, async ({ page }) => {
    await loginAsAdmin(page);
    const request = page.context().request;

    const response = await request.get(`${BASE_URL}/search/Test/${type}`, {
      failOnStatusCode: false,
      headers: { Accept: 'application/json' },
    });

    expect(response.status()).toBe(200);
  });
}
