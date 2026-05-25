const { test, expect } = require('@playwright/test');
const {
  BASE_URL,
  loginAsAdmin,
  createUser,
  userData,
  jsonHeaders,
  expectValidationError,
  uniqueValue,
} = require('../helpers/plain-e2e');

const malformedId = 'invalid-@@@';

test('guest is redirected from users route', async ({ page }) => {
  await page.goto(`${BASE_URL}/users`);
  await expect(page).toHaveURL(/login/);
});

test('creating a user makes it searchable in users data feed', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  const { response, name, email } = await createUser(page, request);
  const dataResponse = await userData(request, name);
  const payload = await dataResponse.json();

  expect(response.status()).toBe(302);
  expect(dataResponse.status()).toBe(200);
  expect((payload.data ?? []).some((row) => row.name === name && row.email === email)).toBe(true);
});

test('empty user payload returns field validation errors', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const response = await request.post(`${BASE_URL}/users`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {},
  });

  await expectValidationError(response, 'name');
});

test('user create form shows alert when submitted empty', async ({ page }) => {
  await loginAsAdmin(page);
  await page.goto(`${BASE_URL}/users/create`);
  await page.locator('form button[type="submit"], form input[type="submit"]').first().click();
  await expect(page.locator('.alert.alert-danger, .invalid-feedback').first()).toBeVisible();
});

test('updating a user name persists in users data feed', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { name, email } = await createUser(page, request, uniqueValue('PW User Update'));

  const createdDataResponse = await userData(request, name);
  const createdDataPayload = await createdDataResponse.json();
  const createdRow = (createdDataPayload.data ?? []).find((row) => row.name === name && row.email === email);
  expect(createdRow?.external_id).toBeTruthy();

  const editResponse = await request.get(`${BASE_URL}/users/${createdRow.external_id}/edit`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
  expect(editResponse.status()).toBe(200);
  const editPayload = await editResponse.json();
  const updatedName = uniqueValue('PW User Updated');

  const updateResponse = await request.patch(`${BASE_URL}/users/${createdRow.external_id}`, {
    failOnStatusCode: false,
    maxRedirects: 0,
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

  expect(updateResponse.status()).toBe(302);
  expect((dataPayload.data ?? []).some((row) => row.name === updatedName && row.email === email)).toBe(true);
});

test('malformed user update id returns not found', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  const updateResponse = await request.patch(`${BASE_URL}/users/${malformedId}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: { name: 'Invalid Update', email: 'invalid@example.com' },
  });

  expect(updateResponse.status()).toBe(404);
});

test('malformed user delete id returns not found', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  const deleteResponse = await request.delete(`${BASE_URL}/users/${malformedId}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
  });

  expect(deleteResponse.status()).toBe(404);
});
