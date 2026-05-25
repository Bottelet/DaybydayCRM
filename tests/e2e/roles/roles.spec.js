const { test, expect } = require('@playwright/test');
const {
  BASE_URL,
  loginAsAdmin,
  createRole,
  roleData,
  jsonHeaders,
  expectValidationError,
  uniqueValue,
} = require('../helpers/plain-e2e');

const malformedId = 'invalid-@@@';

test('guest is redirected from roles route', async ({ page }) => {
  await page.goto(`${BASE_URL}/roles`);
  await expect(page).toHaveURL(/login/);
});

test('creating a role makes it searchable in roles data feed', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { response, name } = await createRole(page, request);
  const dataResponse = await roleData(request, name);
  const payload = await dataResponse.json();

  expect(response.status()).toBe(200);
  expect(dataResponse.status()).toBe(200);
  expect((payload.data ?? []).some((row) => row.name === name)).toBe(true);
});

test('empty role payload returns field validation errors', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const response = await request.post(`${BASE_URL}/roles`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {},
  });

  await expectValidationError(response, 'name');
});

test('role create form shows alert when submitted empty', async ({ page }) => {
  await loginAsAdmin(page);
  await page.goto(`${BASE_URL}/roles/create`);
  await page.locator('form button[type="submit"], form input[type="submit"]').first().click();
  await expect(page.locator('form .alert.alert-danger:visible, form .invalid-feedback:visible').first()).toBeVisible();
});

test('updating role permissions redirects and role stays searchable', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const originalName = uniqueValue('pw_role_update');
  const { name } = await createRole(page, request, originalName);

  const createdDataResponse = await roleData(request, name);
  const createdDataPayload = await createdDataResponse.json();
  const row = (createdDataPayload.data ?? []).find((item) => item.name === name);
  expect(row?.external_id).toBeTruthy();

  const rolePath = `${BASE_URL}/roles/${row.external_id}`;
  const updateResponse = await request.patch(`${BASE_URL}/roles/update/${row.external_id}`, {
    failOnStatusCode: false,
    maxRedirects: 0,
    headers: await jsonHeaders(page, { Referer: rolePath }),
    form: {
      permissions: [],
    },
  });

  const afterDataResponse = await roleData(request, name);
  const afterDataPayload = await afterDataResponse.json();

  expect(updateResponse.status()).toBe(302);
  expect(updateResponse.headers().location ?? '').toContain(rolePath);
  expect((afterDataPayload.data ?? []).some((item) => item.name === name)).toBe(true);
});

test('updating malformed role id returns not found', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  const response = await request.patch(`${BASE_URL}/roles/update/${malformedId}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: { permissions: [] },
  });

  expect(response.status()).toBe(404);
});
