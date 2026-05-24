const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createUser, userData, jsonHeaders, expectValidationError } = require('../helpers/plain-e2e');

test('user creation appears in the users datatable payload', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { response, name, email } = await createUser(page, request);

  /* Act */
  const dataResponse = await userData(request, name);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(response.status()).toBe(302);
  expect(JSON.stringify(dataPayload)).toContain(name);
  expect(JSON.stringify(dataPayload)).toContain(email);
});

test('user validation reports the missing name field', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;

  /* Act */
  const response = await request.post(`${BASE_URL}/users`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {},
  });

  /* Assert */
  await expectValidationError(response, 'name');
});
