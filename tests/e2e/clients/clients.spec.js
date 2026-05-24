const { test, expect } = require('@playwright/test');
const { loginAsAdmin, createClient, clientData, jsonHeaders, expectValidationError, uniqueValue, BASE_URL } = require('../helpers/plain-e2e');

test('client creation shows up in the searchable clients data table', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const companyName = uniqueValue('PW Client');
  const { response } = await createClient(page, request, companyName);

  /* Act */
  const dataResponse = await clientData(request, companyName);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(response.status()).toBe(201);
  expect(dataResponse.status()).toBe(200);
  expect(JSON.stringify(dataPayload)).toContain(companyName);
});

test('client validation returns a field error instead of a generic success page', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;

  /* Act */
  const response = await request.post(`${BASE_URL}/clients`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {},
  });

  /* Assert */
  await expectValidationError(response, 'name');
});
