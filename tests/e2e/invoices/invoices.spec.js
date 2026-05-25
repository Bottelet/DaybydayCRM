const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createInvoice } = require('../helpers/plain-e2e');

test('guest is redirected from invoices route', async ({ page }) => {
  await page.goto(`${BASE_URL}/invoices/overdue`);
  await expect(page).toHaveURL(/login/);
});

test('created invoice detail route is accessible for authenticated admin', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { invoiceExternalId } = await createInvoice(page, request);

  const response = await request.get(`${BASE_URL}/invoices/${invoiceExternalId}`, {
    failOnStatusCode: false,
  });

  expect(response.status()).toBe(200);
  expect(await response.text()).toContain(invoiceExternalId);
});

test('invoice payments data endpoint returns structured payload', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { invoiceExternalId } = await createInvoice(page, request);

  const response = await request.get(`${BASE_URL}/invoices/payments-data/${invoiceExternalId}?draw=1&start=0&length=25`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
  const payload = await response.json();

  expect(response.status()).toBe(200);
  expect(payload).toHaveProperty('data');
  expect(Array.isArray(payload.data)).toBe(true);
});

test('unknown invoice routes return not found', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const missingId = '00000000-0000-0000-0000-000000000000';

  const showResponse = await request.get(`${BASE_URL}/invoices/${missingId}`, {
    failOnStatusCode: false,
  });

  const paymentsResponse = await request.get(`${BASE_URL}/invoices/payments-data/${missingId}?draw=1&start=0&length=25`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });

  expect(showResponse.status()).toBe(404);
  expect(paymentsResponse.status()).toBe(404);
});
