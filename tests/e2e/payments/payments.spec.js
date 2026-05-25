const { test, expect } = require('@playwright/test');
const {
  BASE_URL,
  loginAsAdmin,
  createInvoice,
  addPayment,
  paymentsData,
  jsonHeaders,
  expectValidationError,
} = require('../helpers/plain-e2e');

test('adding payment makes it visible in invoice payments feed', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { invoiceExternalId } = await createInvoice(page, request);

  const createResponse = await addPayment(page, request, invoiceExternalId, {
    description: 'Playwright payment detail',
  });

  const dataResponse = await paymentsData(request, invoiceExternalId);
  const payload = await dataResponse.json();

  expect(createResponse.status()).toBe(201);
  expect(dataResponse.status()).toBe(200);
  expect((payload.data ?? []).some((row) => String(row.description ?? '').includes('Playwright payment detail'))).toBe(true);
});

test('zero payment amount returns validation error', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { invoiceExternalId } = await createInvoice(page, request);

  const response = await addPayment(page, request, invoiceExternalId, { amount: 0 });
  await expectValidationError(response, 'amount');
});

test('invalid payment source returns validation error', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { invoiceExternalId } = await createInvoice(page, request);

  const response = await addPayment(page, request, invoiceExternalId, {
    source: 'invalid_source',
  });

  await expectValidationError(response, 'source');
});

test('comma decimal amount is accepted', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { invoiceExternalId } = await createInvoice(page, request);

  const response = await addPayment(page, request, invoiceExternalId, {
    amount: '10,50',
    description: 'comma separator payment',
  });

  expect(response.status()).toBe(201);
});

test('deleting payment removes it from invoice payments feed', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { invoiceExternalId } = await createInvoice(page, request);
  const description = `payment-to-delete-${Date.now()}`;

  const createResponse = await addPayment(page, request, invoiceExternalId, { description });
  expect(createResponse.status()).toBe(201);

  const beforeResponse = await paymentsData(request, invoiceExternalId);
  const beforePayload = await beforeResponse.json();
  const row = (beforePayload.data ?? []).find((item) => String(item.description ?? '').includes(description));
  expect(row?.external_id).toBeTruthy();

  const deleteResponse = await request.delete(`${BASE_URL}/payment/${row.external_id}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
  });

  const afterResponse = await paymentsData(request, invoiceExternalId);
  const afterPayload = await afterResponse.json();

  expect(deleteResponse.status()).toBe(200);
  expect((afterPayload.data ?? []).some((item) => String(item.description ?? '').includes(description))).toBe(false);
});
