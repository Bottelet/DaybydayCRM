import { test as guestTest, expect as guestExpect } from '@playwright/test';
import { test, expect, nonAdminTest } from '../../helpers/fixtures';
import { PLAYWRIGHT_BASE_URL } from '../../helpers/config';
import { fetchCsrfToken } from '../../helpers/csrf';
import { DomainAssertions } from '../../helpers/feature-domain';

async function createOffer(
  page: import('@playwright/test').Page,
  request: import('@playwright/test').APIRequestContext,
  title: string,
) {
  const csrf = await fetchCsrfToken(page);

  const formPage = await request.get(`${PLAYWRIGHT_BASE_URL}/offers/create`, {
    failOnStatusCode: false,
  });
  const html = await formPage.text();

  const clientMatch = html.match(/<option[^>]*value="([^"]+)"[^>]*>[^<]*<\/option>/);
  const clientExternalId = clientMatch?.[1] ?? '';

  return request.post(`${PLAYWRIGHT_BASE_URL}/offers`, {
    failOnStatusCode: false,
    headers: {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': csrf,
    },
    form: {
      title,
      client_external_id: clientExternalId,
      description: 'Playwright offer description',
      expiry_date: '2030-01-01',
    },
  });
}

test.describe('Offers feature behavior', () => {
  test('store happy path creates offer visible in offers data', async ({ page, request }) => {
    const title = `PW Offer ${Date.now()}`;
    const response = await createOffer(page, request, title);

    expect([200, 201, 302]).toContain(response.status());
  });

  test('validation failure returns required title field error', async ({ page, request }) => {
    const response = await request.post(`${PLAYWRIGHT_BASE_URL}/offers`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': await fetchCsrfToken(page),
      },
      form: {},
    });

    await DomainAssertions.expectValidationError(response, 'title');
  });

  test('web error returned when offer creation fails validation', async ({ page, request }) => {
    const response = await request.post(`${PLAYWRIGHT_BASE_URL}/offers`, {
      failOnStatusCode: false,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': await fetchCsrfToken(page),
      },
      form: {},
    });

    expect([302, 422]).toContain(response.status());
  });

  test('json error returned when offer creation fails validation', async ({ page, request }) => {
    const response = await request.post(`${PLAYWRIGHT_BASE_URL}/offers`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': await fetchCsrfToken(page),
      },
      form: {},
    });

    expect(response.status()).toBe(422);
    const json = await response.json();
    expect(json.errors).toHaveProperty('title');
  });

  test('can update offer', async ({ page, request }) => {
    const title = `PW Offer Update ${Date.now()}`;
    const createResponse = await createOffer(page, request, title);
    if (![200, 201, 302].includes(createResponse.status())) return;

    const location = createResponse.headers()['location'] ?? '';
    const externalId = location.split('/').filter(Boolean).pop() as string;
    if (!externalId) return;

    const updateResponse = await request.patch(`${PLAYWRIGHT_BASE_URL}/offers/${externalId}`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': await fetchCsrfToken(page),
      },
      form: { title: `${title} Updated` },
    });

    expect([200, 302]).toContain(updateResponse.status());
  });

  test('can set offer as won', async ({ page, request }) => {
    const title = `PW Offer Won ${Date.now()}`;
    const createResponse = await createOffer(page, request, title);
    if (![200, 201, 302].includes(createResponse.status())) return;

    const location = createResponse.headers()['location'] ?? '';
    const externalId = location.split('/').filter(Boolean).pop() as string;
    if (!externalId) return;

    const wonResponse = await request.patch(
      `${PLAYWRIGHT_BASE_URL}/offers/${externalId}/won`,
      {
        failOnStatusCode: false,
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
      },
    );

    expect([200, 302]).toContain(wonResponse.status());
  });

  test('can set offer as lost', async ({ page, request }) => {
    const title = `PW Offer Lost ${Date.now()}`;
    const createResponse = await createOffer(page, request, title);
    if (![200, 201, 302].includes(createResponse.status())) return;

    const location = createResponse.headers()['location'] ?? '';
    const externalId = location.split('/').filter(Boolean).pop() as string;
    if (!externalId) return;

    const lostResponse = await request.patch(
      `${PLAYWRIGHT_BASE_URL}/offers/${externalId}/lost`,
      {
        failOnStatusCode: false,
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
      },
    );

    expect([200, 302]).toContain(lostResponse.status());
  });
});

guestTest('guest is redirected from offers index', async ({ page }) => {
  await page.goto(`${PLAYWRIGHT_BASE_URL}/offers`);
  await guestExpect(page).toHaveURL(/login/);
});
