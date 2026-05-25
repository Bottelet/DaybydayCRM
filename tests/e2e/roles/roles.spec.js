const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createRole, roleData, jsonHeaders, expectValidationError, uniqueValue } = require('../helpers/plain-e2e');

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

test('role updates return a success message and reflect in the datatable payload', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { name } = await createRole(page, request, uniqueValue('pw_role_update'));
  const createdDataResponse = await roleData(request, name);
  const createdDataPayload = await createdDataResponse.json();
  const createdRows = Array.isArray(createdDataPayload?.data) ? createdDataPayload.data : [];
  const createdRow = createdRows.find(row => row.name === name);
  const updatedName = uniqueValue('pw_role_updated');

  /* Act */
  const updateResponse = await request.patch(`${BASE_URL}/roles/update/${createdRow.external_id}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {
      name: updatedName,
      description: `${updatedName} description`,
    },
  });
  const updatePayload = await updateResponse.json();
  const dataResponse = await roleData(request, updatedName);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(createdDataResponse.status()).toBe(200);
  expect(createdRow?.external_id).toBeTruthy();
  expect(updateResponse.status()).toBe(200);
  expect(String(updatePayload.message || '').toLowerCase()).toContain('updated');
  expect(dataResponse.status()).toBe(200);
  const rows = Array.isArray(dataPayload?.data) ? dataPayload.data : [];
  expect(rows.some(row => row.name === updatedName)).toBe(true);
});
