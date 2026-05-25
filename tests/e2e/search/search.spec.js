const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createClient, uniqueValue } = require('../helpers/plain-e2e');

test('search returns structured hits for a freshly created client', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const companyName = uniqueValue('PW Search Client');
  await createClient(page, request, companyName);

  /* Act */
  const response = await request.get(`${BASE_URL}/search/${encodeURIComponent(companyName)}/client`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
  const payload = await response.json();

  /* Assert */
  expect(response.status()).toBe(200);
  expect(payload).toBeDefined();
  expect(payload.hits).toBeDefined();
  expect(response.status()).toBe(200);
  expect(payload).toHaveProperty('hits');
  expect(payload.hits).toHaveProperty('hits');
  expect(Array.isArray(payload.hits.hits)).toBe(true);
  expect(payload.hits.hits.length).toBeGreaterThan(0);
  expect(JSON.stringify(payload)).toContain(companyName);
  expect(JSON.stringify(payload)).toContain('/clients/');
});

test('search rejects unsupported search types with an explicit error payload', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;

  /* Act */
  const response = await request.get(`${BASE_URL}/search/test/invoice`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
  const payload = await response.json();

  /* Assert */
  expect(response.status()).toBe(400);
  expect(payload.error).toBe('Invalid search type');
});
