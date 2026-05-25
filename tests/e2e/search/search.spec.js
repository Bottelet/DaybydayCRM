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

test('search rejects invalid and injected types', async ({ request }) => {
    const invalid = await request.get(`${PLAYWRIGHT_BASE_URL}/search/Test/InvalidType`, {
        failOnStatusCode: false,
        headers: { Accept: 'application/json' },
    });
    expect(invalid.status()).toBe(400);

    const arbitraryClass = await request.get(`${PLAYWRIGHT_BASE_URL}/search/Test/Setting`, {
        failOnStatusCode: false,
        headers: { Accept: 'application/json' },
    });
    expect(arbitraryClass.status()).toBe(400);

    const namespaceInjection = await request.get(`${PLAYWRIGHT_BASE_URL}/search/Test/..%2F..%2FUser`, {
        failOnStatusCode: false,
        headers: { Accept: 'application/json' },
    });
    expect(namespaceInjection.status()).toBe(404);

    const classPathInjection = await request.get(`${PLAYWRIGHT_BASE_URL}/search/Test/App%5CModels%5CUser`, {
        failOnStatusCode: false,
        headers: { Accept: 'application/json' },
    });
    expect(classPathInjection.status()).toBe(400);
});

test('search type matching is case insensitive', async ({ request }) => {
    const response = await request.get(`${PLAYWRIGHT_BASE_URL}/search/Test/CLIENT`, {
        failOnStatusCode: false,
        headers: { Accept: 'application/json' },
    });

    expect(response.status()).toBe(200);
    const payload = await response.json();
    expect(payload).toHaveProperty('hits');
});

test('search accepts valid "client" type', async ({ request }) => {
    const response = await request.get(`${PLAYWRIGHT_BASE_URL}/search/Test/client`, {
        failOnStatusCode: false,
        headers: { Accept: 'application/json' },
    });

    expect(response.status()).toBe(200);
    const payload = await response.json();
    expect(payload).toHaveProperty('hits');
});

test('search accepts valid "clients" type', async ({ request }) => {
    const response = await request.get(`${PLAYWRIGHT_BASE_URL}/search/Test/clients`, {
        failOnStatusCode: false,
        headers: { Accept: 'application/json' },
    });

    expect(response.status()).toBe(200);
    const payload = await response.json();
    expect(payload).toHaveProperty('hits');
});

test('search accepts valid "task" type', async ({ request }) => {
    const response = await request.get(`${PLAYWRIGHT_BASE_URL}/search/Test/task`, {
        failOnStatusCode: false,
        headers: { Accept: 'application/json' },
    });

    expect(response.status()).toBe(200);
    const payload = await response.json();
    expect(payload).toHaveProperty('hits');
});

test('search accepts valid "project" type', async ({ request }) => {
    const response = await request.get(`${PLAYWRIGHT_BASE_URL}/search/Test/project`, {
        failOnStatusCode: false,
        headers: { Accept: 'application/json' },
    });

    expect(response.status()).toBe(200);
    const payload = await response.json();
    expect(payload).toHaveProperty('hits');
});

test('search accepts valid "lead" type', async ({ request }) => {
    const response = await request.get(`${PLAYWRIGHT_BASE_URL}/search/Test/lead`, {
        failOnStatusCode: false,
        headers: { Accept: 'application/json' },
    });

    expect(response.status()).toBe(200);
    const payload = await response.json();
    expect(payload).toHaveProperty('hits');
});

test('search accepts valid "user" type', async ({ request }) => {
    const response = await request.get(`${PLAYWRIGHT_BASE_URL}/search/Test/user`, {
        failOnStatusCode: false,
        headers: { Accept: 'application/json' },
    });

    expect(response.status()).toBe(200);
    const payload = await response.json();
    expect(payload).toHaveProperty('hits');
});
