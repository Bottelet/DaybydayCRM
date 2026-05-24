import { test, expect } from '../../helpers/fixtures';
import { PLAYWRIGHT_BASE_URL } from '../../helpers/config';

for (const type of ['client', 'clients', 'task', 'project', 'lead', 'user']) {
  test(`search accepts valid "${type}" types`, async ({ request }) => {
    const response = await request.get(`${PLAYWRIGHT_BASE_URL}/search/Test/${type}`, {
      failOnStatusCode: false,
      headers: { Accept: 'application/json' },
    });

    expect(response.status()).toBe(200);
    const payload = await response.json();
    expect(payload).toHaveProperty('hits');
  });
}

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
