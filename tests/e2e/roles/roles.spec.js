const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createRole, roleData, jsonHeaders, expectValidationError } = require('../helpers/plain-e2e');

test('role creation appears in the roles datatable payload', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { response, name } = await createRole(page, request);

  /* Act */
  const dataResponse = await roleData(request, name);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(response.status()).toBe(200);
  expect(dataResponse.status()).toBe(200);
  const rows = dataPayload.data || [];
  expect(rows.some(row => row.name === name)).toBe(true);
});

test('role validation reports the missing name field', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;

  /* Act */
  const response = await request.post(`${BASE_URL}/roles`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {},
  });

  /* Assert */
  await expectValidationError(response, 'name');
});
