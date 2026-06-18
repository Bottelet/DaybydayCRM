const { test, expect } = require('@playwright/test');
const {
  BASE_URL,
  loginAsAdmin,
  createDepartment,
  departmentData,
  jsonHeaders,
  expectValidationError,
  uniqueValue,
} = require('../helpers/plain-e2e');

test('guest is redirected from departments create route', async ({ page }) => {
  await page.goto(`${BASE_URL}/departments/create`);
  await expect(page).toHaveURL(/login/);
});

test('creating a department makes it searchable in department data feed', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  const { response, name } = await createDepartment(page, request);
  const dataResponse = await departmentData(request, name);
  const payload = await dataResponse.json();

  expect(response.status()).toBe(302);
  expect(dataResponse.status()).toBe(200);
  expect((payload.data ?? []).some((row) => row.name === name)).toBe(true);
});

test('empty department payload returns field validation errors', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const response = await request.post(`${BASE_URL}/departments`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {},
  });

  await expectValidationError(response, 'name');
});

test('department create form shows alert when submitted empty', async ({ page }) => {
  await loginAsAdmin(page);
  await page.goto(`${BASE_URL}/departments/create`);
  await page.locator('form button[type="submit"], form input[type="submit"]').first().click();
  await expect(page.locator('.alert.alert-danger, .invalid-feedback').first()).toBeVisible();
});

test('deleting a department removes it from department data feed', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const name = uniqueValue('PW Dept Delete');

  const { response: createResponse } = await createDepartment(page, request, name);
  expect(createResponse.status()).toBe(302);

  const createdDataResponse = await departmentData(request, name);
  const createdDataPayload = await createdDataResponse.json();
  const row = (createdDataPayload.data ?? []).find((item) => item.name === name);
  expect(row?.external_id).toBeTruthy();

  const deleteResponse = await request.delete(`${BASE_URL}/departments/${row.external_id}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    maxRedirects: 0,
  });

  const afterDataResponse = await departmentData(request, name);
  const afterDataPayload = await afterDataResponse.json();

  expect(deleteResponse.status()).toBeLessThan(400);
  expect((afterDataPayload.data ?? []).some((item) => item.name === name)).toBe(false);
});

test('browser create shows success notification and department appears on index', async ({ page }) => {
  await loginAsAdmin(page);

  await page.goto(`${BASE_URL}/departments/create`);

  const name = uniqueValue('PW Browser Dept');

  await page.locator('input[name="name"]').fill(name);
  await page.locator('textarea[name="description"]').fill('Browser test department');

  await Promise.all([
    page.waitForURL(`${BASE_URL}/departments`),
    page.locator('form [type="submit"]').first().click(),
  ]);

  // Element UI success toast
  await expect(page.locator('.el-message--success')).toBeVisible();
  await expect(page.locator('.el-message__content')).toContainText('Successfully created new department');

  // Department appears in the DataTables list
  await page.waitForLoadState('networkidle');
  await expect(page.locator('table')).toContainText(name);
});
