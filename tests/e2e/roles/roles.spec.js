const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createRole, roleData, jsonHeaders, expectValidationError, uniqueValue } = require('../helpers/plain-e2e');

test('creating a role registers it in the roles datatable payload', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;

  /* Act */
  const { response, name } = await createRole(page, request);
  const dataResponse = await roleData(request, name);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(response.status(), 'Role creation should return 200').toBe(200);
  expect(dataResponse.status(), 'Roles data table should return 200').toBe(200);
  const rows = dataPayload.data || [];
  expect(
    rows.some(row => row.name === name),
    `Newly created role "${name}" should appear by exact name in the data table`
  ).toBe(true);
});

test('submitting a role form without required fields returns a name field validation error', async ({ page }) => {
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

test('updating a role name persists the change and shows the success message', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { name } = await createRole(page, request, uniqueValue('pw_role_update'));
  const createdDataResponse = await roleData(request, name);
  const createdDataPayload = await createdDataResponse.json();
  const createdRows = Array.isArray(createdDataPayload?.data) ? createdDataPayload.data : [];
  const createdRow = createdRows.find(row => row.name === name);
  expect(createdRow?.external_id, 'Created role must have an external_id for update').toBeTruthy();
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
  expect(createdDataResponse.status(), 'Role data table lookup should return 200').toBe(200);
  expect(updateResponse.status(), 'Role update should return 200').toBe(200);
  expect(
    String(updatePayload.message ?? '').toLowerCase(),
    'Response body should confirm the update with an "updated" message'
  ).toContain('updated');
  expect(dataResponse.status(), 'Roles data table should return 200 after update').toBe(200);
  const rows = Array.isArray(dataPayload?.data) ? dataPayload.data : [];
  expect(
    rows.some(row => row.name === updatedName),
    `Updated role "${updatedName}" should appear in the data table`
  ).toBe(true);
});
