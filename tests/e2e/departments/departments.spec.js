const { test, expect } = require('@playwright/test');
const { loginAsAdmin, createDepartment, departmentData, BASE_URL, jsonHeaders, expectValidationError } = require('../helpers/plain-e2e');

test('department creation shows the new department in the datatable payload', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { response, name } = await createDepartment(page, request);

  /* Act */
  const dataResponse = await departmentData(request, name);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(response.status()).toBe(302);
  expect(dataResponse.status()).toBe(200);
  const rows = dataPayload.data || [];
  expect(rows.some(row => row.name === name)).toBe(true);
});

test('guests are redirected away from the department creator', async ({ page }) => {
  /* Arrange */
  await page.goto(`${BASE_URL}/departments/create`);

  /* Act */
  const loginButton = page.getByRole('button', { name: /log ?in|sign ?in/i });

  /* Assert */
  await expect(page).toHaveURL(/login/);
  await expect(loginButton).toBeVisible();
});

test('department validation reports the missing required name field', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;

  /* Act */
  const response = await request.post(`${BASE_URL}/departments`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {},
  });

  /* Assert */
  await expectValidationError(response, 'name');
});
