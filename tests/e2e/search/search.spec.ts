import { test as guestTest, expect as guestExpect } from '@playwright/test';
import { test, expect } from '../../helpers/fixtures';
import { PLAYWRIGHT_BASE_URL } from '../../helpers/config';

async function search(
  request: import('@playwright/test').APIRequestContext,
  type: string,
  query = 'a',
) {
  return request.get(
    `${PLAYWRIGHT_BASE_URL}/search?type=${encodeURIComponent(type)}&query=${encodeURIComponent(query)}`,
    {
      failOnStatusCode: false,
      headers: { Accept: 'application/json' },
    },
  );
}

test.describe('Search feature behavior', () => {
  // ── valid types ────────────────────────────────────────────────────────────

  test('search with valid type client returns results', async ({ request }) => {
    const response = await search(request, 'client');

    expect(response.status()).toBe(200);
  });

  test('search with valid type clients plural returns results', async ({ request }) => {
    const response = await search(request, 'clients');

    expect(response.status()).toBe(200);
  });

  test('search with valid type lead returns results', async ({ request }) => {
    const response = await search(request, 'lead');

    expect(response.status()).toBe(200);
  });

  test('search with valid type project returns results', async ({ request }) => {
    const response = await search(request, 'project');

    expect(response.status()).toBe(200);
  });

  test('search with valid type task returns results', async ({ request }) => {
    const response = await search(request, 'task');

    expect(response.status()).toBe(200);
  });

  test('search with valid type user returns results', async ({ request }) => {
    const response = await search(request, 'user');

    expect(response.status()).toBe(200);
  });

  // ── case insensitivity ─────────────────────────────────────────────────────

  test('search type is case insensitive', async ({ request }) => {
    const lower = await search(request, 'client', 'a');
    const upper = await search(request, 'CLIENT', 'a');

    expect(lower.status()).toBe(200);
    expect(upper.status()).toBe(200);

    const lowerPayload = await lower.json();
    const upperPayload = await upper.json();
    expect(JSON.stringify(lowerPayload)).toEqual(JSON.stringify(upperPayload));
  });

  // ── invalid type ───────────────────────────────────────────────────────────

  test('search with invalid type returns 400 error', async ({ request }) => {
    const response = await search(request, 'invoice');

    expect(response.status()).toBe(400);
  });

  test('search with unknown type returns 400 error', async ({ request }) => {
    const response = await search(request, 'foobar');

    expect(response.status()).toBe(400);
  });

  // ── security: injection prevention ────────────────────────────────────────

  test('search prevents arbitrary class instantiation', async ({ request }) => {
    const response = await search(request, 'stdClass');

    expect(response.status()).toBe(400);
  });

  test('search rejects class path injection', async ({ request }) => {
    const response = await search(request, 'App\\Models\\User');

    expect(response.status()).toBe(400);
  });

  test('search rejects namespace injection attempts', async ({ request }) => {
    const response = await search(request, '\\Illuminate\\Support\\Facades\\DB');

    expect(response.status()).toBe(400);
  });

  test('search rejects forward slash class path injection', async ({ request }) => {
    const response = await search(request, 'App/Models/User');

    expect(response.status()).toBe(400);
  });
});

guestTest('guest is redirected from search endpoint', async ({ page }) => {
  await page.goto(`${PLAYWRIGHT_BASE_URL}/search?type=client&query=test`);
  await guestExpect(page).toHaveURL(/login/);
});
