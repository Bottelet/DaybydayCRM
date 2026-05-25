const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createInvoice, addPayment, paymentsData, jsonHeaders, expectValidationError } = require('../helpers/plain-e2e');

test('adding a payment to an invoice shows the payment in the invoice payment feed', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { invoiceExternalId } = await createInvoice(page, request);

  /* Act */
  const createResponse = await addPayment(page, request, invoiceExternalId, {
    description: 'Playwright payment detail',
  });
  const dataResponse = await paymentsData(request, invoiceExternalId);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(createResponse.status(), 'Payment creation should return 201').toBe(201);
  expect(dataResponse.status(), 'Payments data feed should return 200').toBe(200);
  const rows = Array.isArray(dataPayload?.data) ? dataPayload.data : [];
  expect(rows.length, 'At least one payment row should appear after creation').toBeGreaterThan(0);
  expect(
    rows.some(row => String(row.description ?? '').includes('Playwright payment detail')),
    'The specific payment description should appear in the payments feed'
  ).toBe(true);
});

test('submitting a payment with a zero amount returns a field-level validation error', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { invoiceExternalId } = await createInvoice(page, request);

  /* Act – zero is explicitly forbidden by the payment amount validation */
  const response = await addPayment(page, request, invoiceExternalId, {
    amount: 0,
  });

  /* Assert */
  await expectValidationError(response, 'amount');
});

test('deleting a payment removes it from the invoice payment feed', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const description = `Playwright delete payment ${Date.now()}`;
  const { invoiceExternalId } = await createInvoice(page, request);
  const createResponse = await addPayment(page, request, invoiceExternalId, { description });

  /* Locate the created payment row */
  const createdDataResponse = await paymentsData(request, invoiceExternalId);
  /* Locate the created payment row */
  const createdDataResponse = await paymentsData(request, invoiceExternalId);
  expect(createdDataResponse.status(), 'Payments data feed should return 200 before locating a row').toBe(200);
  const createdPayload = await createdDataResponse.json();
  const createdRows = Array.isArray(createdPayload?.data) ? createdPayload.data : [];
  const createdRow = createdRows.find(row => String(row.description ?? '').includes(description));
  expect(createdRow?.external_id, 'Created payment must have an external_id for deletion').toBeTruthy();

  /* Act */
  const deleteResponse = await request.delete(`${BASE_URL}/payment/${createdRow.external_id}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
  });
  const dataResponse = await paymentsData(request, invoiceExternalId);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(createResponse.status(), 'Payment creation should return 201').toBe(201);
  expect(deleteResponse.status(), 'Payment deletion should return 200').toBe(200);
  expect(dataResponse.status(), 'Payments feed after delete should return 200').toBe(200);
  const rows = Array.isArray(dataPayload?.data) ? dataPayload.data : [];
  expect(
    rows.some(row => String(row.description ?? '').includes(description)),
    `Deleted payment with description "${description}" must not appear in the feed`
  ).toBe(false);
});
