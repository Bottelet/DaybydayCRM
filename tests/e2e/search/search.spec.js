const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createClient, uniqueValue } = require('../helpers/plain-e2e');

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

test('search accepts supported domain types', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  for (const type of ['client', 'clients', 'task', 'project', 'lead', 'user']) {
    const response = await request.get(`${BASE_URL}/search/Test/${type}`, {
      failOnStatusCode: false,
      headers: { Accept: 'application/json' },
    });

    expect(response.status(), `Expected ${type} search to be accepted`).toBe(200);
  }
});

