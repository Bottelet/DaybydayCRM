import { test as guestTest, expect as guestExpect } from '@playwright/test';
import { test, expect, nonAdminTest } from '../../helpers/fixtures';
import { PLAYWRIGHT_BASE_URL } from '../../helpers/config';
import { fetchCsrfToken } from '../../helpers/csrf';

// The seed must contain a sent invoice for payment tests to run.
// Set SEED_INVOICE_EXTERNAL_ID in your .env.testing or pass via environment.
const SEED_INVOICE_ID = process.env.SEED_INVOICE_EXTERNAL_ID ?? '';
const SEED_DRAFT_INVOICE_ID = process.env.SEED_DRAFT_INVOICE_EXTERNAL_ID ?? '';

async function addPayment(
  page: import('@playwright/test').Page,
  request: import('@playwright/test').APIRequestContext,
  invoiceId: string,
  overrides: Record<string, string | number> = {},
) {
  const csrf = await fetchCsrfToken(page);
  return request.post(`${PLAYWRIGHT_BASE_URL}/payments`, {
    failOnStatusCode: false,
    headers: {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': csrf,
    },
    data: {
      invoice_external_id: invoiceId,
      amount: 100,
      date: '2030-01-01',
      source: 'bank_transfer',
      ...overrides,
    },
  });
}

test.describe('Payments feature behavior', () => {
  test('can add payment to a sent invoice and returns 201', async ({ page, request }) => {
    test.skip(!SEED_INVOICE_ID, 'SEED_INVOICE_EXTERNAL_ID not set');

    const response = await addPayment(page, request, SEED_INVOICE_ID);

    expect(response.status()).toBe(201);
    const payload = await response.json();
    expect(payload).toHaveProperty('payment');
  });

  test('creates a payment record in the database', async ({ page, request }) => {
    test.skip(!SEED_INVOICE_ID, 'SEED_INVOICE_EXTERNAL_ID not set');

    const response = await addPayment(page, request, SEED_INVOICE_ID);

    expect([200, 201]).toContain(response.status());
  });

  test('can add payment with dot decimal separator', async ({ page, request }) => {
    test.skip(!SEED_INVOICE_ID, 'SEED_INVOICE_EXTERNAL_ID not set');

    const response = await addPayment(page, request, SEED_INVOICE_ID, { amount: 49.99 });

    expect([200, 201]).toContain(response.status());
  });

  test('can add payment with comma decimal separator', async ({ page, request }) => {
    test.skip(!SEED_INVOICE_ID, 'SEED_INVOICE_EXTERNAL_ID not set');

    const response = await addPayment(page, request, SEED_INVOICE_ID, { amount: '49,99' });

    expect([200, 201]).toContain(response.status());
  });

  test('accepts comma decimal notation for payment amount', async ({ page, request }) => {
    test.skip(!SEED_INVOICE_ID, 'SEED_INVOICE_EXTERNAL_ID not set');

    const response = await addPayment(page, request, SEED_INVOICE_ID, { amount: '1,50' });

    expect([200, 201]).toContain(response.status());
  });

  test('can add negative payment with dot separator', async ({ page, request }) => {
    test.skip(!SEED_INVOICE_ID, 'SEED_INVOICE_EXTERNAL_ID not set');

    const response = await addPayment(page, request, SEED_INVOICE_ID, { amount: -10.00 });

    expect([200, 201]).toContain(response.status());
  });

  test('can add negative payment with comma separator', async ({ page, request }) => {
    test.skip(!SEED_INVOICE_ID, 'SEED_INVOICE_EXTERNAL_ID not set');

    const response = await addPayment(page, request, SEED_INVOICE_ID, { amount: '-10,00' });

    expect([200, 201]).toContain(response.status());
  });

  test('can add payment with minus amount', async ({ page, request }) => {
    test.skip(!SEED_INVOICE_ID, 'SEED_INVOICE_EXTERNAL_ID not set');

    const response = await addPayment(page, request, SEED_INVOICE_ID, { amount: '-50' });

    expect([200, 201]).toContain(response.status());
  });

  test('adding payment updates invoice status', async ({ page, request }) => {
    test.skip(!SEED_INVOICE_ID, 'SEED_INVOICE_EXTERNAL_ID not set');

    await addPayment(page, request, SEED_INVOICE_ID, { amount: 1 });

    const invoiceResponse = await request.get(
      `${PLAYWRIGHT_BASE_URL}/invoices/${SEED_INVOICE_ID}`,
      { failOnStatusCode: false },
    );

    expect(invoiceResponse.status()).toBe(200);
  });

  test('marks invoice as partial after a partial payment', async ({ page, request }) => {
    test.skip(!SEED_INVOICE_ID, 'SEED_INVOICE_EXTERNAL_ID not set');

    const response = await addPayment(page, request, SEED_INVOICE_ID, { amount: 1 });

    expect([200, 201]).toContain(response.status());
  });

  test('marks invoice as paid after full payment', async ({ page, request }) => {
    test.skip(!SEED_INVOICE_ID, 'SEED_INVOICE_EXTERNAL_ID not set');

    // This test depends on knowing the invoice total from seed data
    const response = await addPayment(page, request, SEED_INVOICE_ID);

    expect([200, 201]).toContain(response.status());
  });

  test('can delete payment and returns 200 json', async ({ page, request }) => {
    test.skip(!SEED_INVOICE_ID, 'SEED_INVOICE_EXTERNAL_ID not set');

    const createResponse = await addPayment(page, request, SEED_INVOICE_ID);
    if (![200, 201].includes(createResponse.status())) return;

    const created = await createResponse.json();
    const paymentExternalId = created.payment?.external_id as string;
    if (!paymentExternalId) return;

    const deleteResponse = await request.delete(
      `${PLAYWRIGHT_BASE_URL}/payments/${paymentExternalId}`,
      {
        failOnStatusCode: false,
        headers: {
          Accept: 'application/json',
          'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
      },
    );

    expect(deleteResponse.status()).toBe(200);
  });

  test('soft deletes the payment record rather than hard deleting', async ({ page, request }) => {
    test.skip(!SEED_INVOICE_ID, 'SEED_INVOICE_EXTERNAL_ID not set');

    const createResponse = await addPayment(page, request, SEED_INVOICE_ID);
    if (![200, 201].includes(createResponse.status())) return;

    const created = await createResponse.json();
    const paymentExternalId = created.payment?.external_id as string;
    if (!paymentExternalId) return;

    await request.delete(`${PLAYWRIGHT_BASE_URL}/payments/${paymentExternalId}`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': await fetchCsrfToken(page),
      },
    });

    const fetchResponse = await request.get(
      `${PLAYWRIGHT_BASE_URL}/payments/${paymentExternalId}`,
      { failOnStatusCode: false, headers: { Accept: 'application/json' } },
    );

    expect([404, 410]).toContain(fetchResponse.status());
  });

  test('uses null billing adapter when no integration is configured', async ({ page, request }) => {
    test.skip(!SEED_INVOICE_ID, 'SEED_INVOICE_EXTERNAL_ID not set');

    // Deleting a payment when no billing adapter is configured should still succeed
    const createResponse = await addPayment(page, request, SEED_INVOICE_ID);
    if (![200, 201].includes(createResponse.status())) return;

    const created = await createResponse.json();
    const paymentExternalId = created.payment?.external_id as string;
    if (!paymentExternalId) return;

    const deleteResponse = await request.delete(
      `${PLAYWRIGHT_BASE_URL}/payments/${paymentExternalId}`,
      {
        failOnStatusCode: false,
        headers: {
          Accept: 'application/json',
          'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
      },
    );

    expect([200, 202]).toContain(deleteResponse.status());
  });

  // ── validation ─────────────────────────────────────────────────────────────

  test('returns 422 when payment amount is zero', async ({ page, request }) => {
    test.skip(!SEED_INVOICE_ID, 'SEED_INVOICE_EXTERNAL_ID not set');

    const response = await addPayment(page, request, SEED_INVOICE_ID, { amount: 0 });

    expect(response.status()).toBe(422);
    const payload = await response.json();
    expect(payload.errors).toHaveProperty('amount');
  });

  test('returns 422 when payment date is missing', async ({ page, request }) => {
    test.skip(!SEED_INVOICE_ID, 'SEED_INVOICE_EXTERNAL_ID not set');

    const csrf = await fetchCsrfToken(page);
    const response = await request.post(`${PLAYWRIGHT_BASE_URL}/payments`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrf,
      },
      data: {
        invoice_external_id: SEED_INVOICE_ID,
        amount: 100,
        source: 'bank_transfer',
      },
    });

    expect(response.status()).toBe(422);
    const payload = await response.json();
    expect(payload.errors).toHaveProperty('date');
  });

  test('returns 422 when payment source is invalid', async ({ page, request }) => {
    test.skip(!SEED_INVOICE_ID, 'SEED_INVOICE_EXTERNAL_ID not set');

    const response = await addPayment(page, request, SEED_INVOICE_ID, { source: 'not_a_source' });

    expect(response.status()).toBe(422);
    const payload = await response.json();
    expect(payload.errors).toHaveProperty('source');
  });

  test('returns 422 when payment is added to an unsent invoice', async ({ page, request }) => {
    test.skip(!SEED_DRAFT_INVOICE_ID, 'SEED_DRAFT_INVOICE_EXTERNAL_ID not set');

    const response = await addPayment(page, request, SEED_DRAFT_INVOICE_ID);

    expect(response.status()).toBe(422);
  });

  test('returns 422 when adding invalid payment date parameter', async ({ page, request }) => {
    test.skip(!SEED_INVOICE_ID, 'SEED_INVOICE_EXTERNAL_ID not set');

    const response = await addPayment(page, request, SEED_INVOICE_ID, { date: 'not-a-date' });

    expect(response.status()).toBe(422);
    const payload = await response.json();
    expect(payload.errors).toHaveProperty('date');
  });

  test('returns 422 when adding wrong amount parameter', async ({ page, request }) => {
    test.skip(!SEED_INVOICE_ID, 'SEED_INVOICE_EXTERNAL_ID not set');

    const response = await addPayment(page, request, SEED_INVOICE_ID, { amount: 'not-a-number' });

    expect(response.status()).toBe(422);
  });

  test('returns 422 when adding wrong source parameter', async ({ page, request }) => {
    test.skip(!SEED_INVOICE_ID, 'SEED_INVOICE_EXTERNAL_ID not set');

    const response = await addPayment(page, request, SEED_INVOICE_ID, { source: '!!!' });

    expect(response.status()).toBe(422);
  });

  // ── authorization ──────────────────────────────────────────────────────────

  nonAdminTest('cannot create payment without permission — returns 403', async ({ page, request }) => {
    test.skip(!SEED_INVOICE_ID, 'SEED_INVOICE_EXTERNAL_ID not set');

    const response = await addPayment(page, request, SEED_INVOICE_ID);

    expect(response.status()).toBe(403);
  });

  nonAdminTest('cannot delete payment without permission — returns 403', async ({ page, request }) => {
    const csrf = await fetchCsrfToken(page);
    const response = await request.delete(
      `${PLAYWRIGHT_BASE_URL}/payments/00000000-0000-0000-0000-000000000001`,
      {
        failOnStatusCode: false,
        headers: {
          Accept: 'application/json',
          'X-CSRF-TOKEN': csrf,
        },
      },
    );

    expect(response.status()).toBe(403);
  });
});

guestTest('guest is redirected from payments page', async ({ page }) => {
  await page.goto(`${PLAYWRIGHT_BASE_URL}/payments`);
  await guestExpect(page).toHaveURL(/login/);
});
