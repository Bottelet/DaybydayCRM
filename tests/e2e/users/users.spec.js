const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createUser, userData, jsonHeaders, expectValidationError, uniqueValue } = require('../helpers/plain-e2e');

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
  expect(dataResponse.status()).toBe(200);
  const rows = dataPayload.data || [];
  expect(rows.some(row => row.name === name && row.email === email)).toBe(true);
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

test('user updates persist changed user details in the datatable payload', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { name, email } = await createUser(page, request, uniqueValue('PW User Update'));
  const createdDataResponse = await userData(request, name);
  const createdDataPayload = await createdDataResponse.json();
  const createdRows = Array.isArray(createdDataPayload?.data) ? createdDataPayload.data : [];
  const createdRow = createdRows.find(row => row.name === name && row.email === email);
  const userExternalId = createdRow?.external_id;
  const editResponse = await request.get(`${BASE_URL}/users/${userExternalId}/edit`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
  const editPayload = await editResponse.json();
  const updatedName = uniqueValue('PW User Updated');

  /* Act */
  const updateResponse = await request.patch(`${BASE_URL}/users/${userExternalId}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {
      name: updatedName,
      email,
      role: Number(Object.keys(editPayload.roles || {})[0]),
      department: Number(Object.keys(editPayload.departments || {})[0]),
    },
  });
  const dataResponse = await userData(request, updatedName);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(createdDataResponse.status()).toBe(200);
  expect(userExternalId).toBeTruthy();
  expect(editResponse.status()).toBe(200);
  expect(updateResponse.status()).toBe(302);
  expect(updateResponse.headers()['location'] ?? '').not.toContain('/login');
  expect(dataResponse.status()).toBe(200);
  const rows = Array.isArray(dataPayload?.data) ? dataPayload.data : [];
  expect(rows.some(row => row.name === updatedName && row.email === email)).toBe(true);
});
