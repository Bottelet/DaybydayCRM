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

test('browser create shows success notification and role appears on index', async ({ page }) => {
  await loginAsAdmin(page);

  await page.goto(`${BASE_URL}/roles/create`);

  const name = uniqueValue('pw_browser_role').replace(/\s/g, '_');

  await page.locator('input[name="name"]').fill(name);
  await page.locator('textarea[name="description"], input[name="description"]').first().fill('Browser test role');

  await Promise.all([
    page.waitForURL(`${BASE_URL}/roles`),
    page.locator('form [type="submit"]').first().click(),
  ]);

  // Element UI success toast
  await expect(page.locator('.el-message--success')).toBeVisible();
  await expect(page.locator('.el-message__content')).toContainText('Role created');

  // Role appears in the DataTables list
  await page.waitForLoadState('networkidle');
  await expect(page.locator('table')).toContainText(name);
});

test('browser edit saves role permissions, shows success notification', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  const { response, name } = await createRole(page, request);
  expect(response.status()).toBe(200);

  const dataResponse = await roleData(request, name);
  const dataPayload = await dataResponse.json();
  const row = (dataPayload.data ?? []).find((r) => r.name === name);
  expect(row?.external_id).toBeTruthy();

  await page.goto(`${BASE_URL}/roles/${row.external_id}`);

  await Promise.all([
    page.waitForURL(/\/roles/),
    page.locator('form [type="submit"]').first().click(),
  ]);

  // Element UI success toast
  await expect(page.locator('.el-message--success')).toBeVisible();
  await expect(page.locator('.el-message__content')).toContainText('Role is updated');
});
