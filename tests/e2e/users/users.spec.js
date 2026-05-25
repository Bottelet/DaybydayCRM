const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createUser, userData, jsonHeaders, expectValidationError, uniqueValue } = require('../helpers/plain-e2e');

test('creating a user registers the account in the users datatable payload', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;

  /* Act */
  const { response, name, email } = await createUser(page, request);
  const dataResponse = await userData(request, name);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(response.status(), 'User creation should return a 302 redirect').toBe(302);
  expect(dataResponse.status(), 'Users data table should return 200').toBe(200);
  const rows = dataPayload.data || [];
  expect(
    rows.some(row => row.name === name && row.email === email),
    `Newly created user "${name}" with email "${email}" should appear in the data table`
  ).toBe(true);
});

test('submitting a user form without required fields returns a name field validation error', async ({ page }) => {
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

test('updating a user display name persists the change in the users datatable payload', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { name, email } = await createUser(page, request, uniqueValue('PW User Update'));

  /* Locate the created user row to get the external_id */
  const createdDataResponse = await userData(request, name);
  const createdDataPayload = await createdDataResponse.json();
  const createdRows = Array.isArray(createdDataPayload?.data) ? createdDataPayload.data : [];
  const createdRow = createdRows.find(row => row.name === name && row.email === email);
  const userExternalId = createdRow?.external_id;
  expect(userExternalId, 'Created user must have an external_id for update').toBeTruthy();

  /* Fetch edit page data to obtain role and department IDs */
  const editResponse = await request.get(`${BASE_URL}/users/${userExternalId}/edit`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
  expect(editResponse.status(), 'User edit endpoint should return 200').toBe(200);
  const editPayload = await editResponse.json();
  const updatedName = uniqueValue('PW User Updated');

  /* Act */
  const updateResponse = await request.patch(`${BASE_URL}/users/${userExternalId}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {
      name: updatedName,
      email,
      role: Number(Object.keys(editPayload.roles ?? {})[0]),
      department: Number(Object.keys(editPayload.departments ?? {})[0]),
    },
  });
  const dataResponse = await userData(request, updatedName);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(updateResponse.status(), 'User update should return a 302 redirect').toBe(302);
  expect(updateResponse.headers()['location'] ?? '', 'Redirect must not point to login').not.toContain('/login');
  expect(dataResponse.status(), 'Users data table should return 200 after update').toBe(200);
  const rows = Array.isArray(dataPayload?.data) ? dataPayload.data : [];
  expect(
    rows.some(row => row.name === updatedName && row.email === email),
    `Updated user "${updatedName}" should appear in the data table with the original email`
  ).toBe(true);
});
