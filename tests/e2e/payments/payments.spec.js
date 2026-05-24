const { test, expect } = require('@playwright/test');
const { loginAsAdmin, createInvoice, addPayment, paymentsData, expectValidationError } = require('../helpers/plain-e2e');

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
  expect(JSON.stringify(dataPayload)).toContain('Playwright payment detail');
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
