const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createInvoice, addPayment, paymentsData, jsonHeaders, expectValidationError } = require('../helpers/plain-e2e');

test('adding a payment shows the payment details in the invoice payment feed', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { invoiceExternalId } = await createInvoice(page, request);
  const createResponse = await addPayment(page, request, invoiceExternalId, {
    description: 'Playwright payment detail',
  });

  /* Act */
  const dataResponse = await paymentsData(request, invoiceExternalId);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(createResponse.status()).toBe(201);
  expect(dataResponse.status()).toBe(200);
  const rows = Array.isArray(dataPayload?.data) ? dataPayload.data : [];
  expect(rows.length).toBeGreaterThan(0);
  expect(rows.some(row => String(row.description || '').includes('Playwright payment detail'))).toBe(true);
});

test('payment validation rejects a zero amount with a concrete field error', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { invoiceExternalId } = await createInvoice(page, request);

  /* Act */
  const response = await addPayment(page, request, invoiceExternalId, {
    amount: 0,
  });

  /* Assert */
  await expectValidationError(response, 'amount');
});

test('payments can be deleted through the payment destroy endpoint', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const description = `Playwright delete payment ${Date.now()}`;
  const { invoiceExternalId } = await createInvoice(page, request);
  const createResponse = await addPayment(page, request, invoiceExternalId, {
    description,
  });
  const createdDataResponse = await paymentsData(request, invoiceExternalId);
  const createdPayload = await createdDataResponse.json();
  const createdRows = Array.isArray(createdPayload?.data) ? createdPayload.data : [];
  const createdRow = createdRows.find(row => String(row.description || '').includes(description));
  expect(createdRow).toBeTruthy();
  const paymentExternalId = createdRow.external_id;

  /* Act */
  const deleteResponse = await request.delete(`${BASE_URL}/payment/${paymentExternalId}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
  });
  const dataResponse = await paymentsData(request, invoiceExternalId);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(createResponse.status()).toBe(201);
  expect(createdDataResponse.status()).toBe(200);
  expect(createdRow?.external_id).toBeTruthy();
  expect(deleteResponse.status()).toBe(200);
  expect(dataResponse.status()).toBe(200);
  const rows = Array.isArray(dataPayload?.data) ? dataPayload.data : [];
  expect(rows.some(row => String(row.description || '').includes(description))).toBe(false);
});
