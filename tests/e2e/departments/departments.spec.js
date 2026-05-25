const { test, expect } = require('@playwright/test');
const { loginAsAdmin, createDepartment, departmentData, BASE_URL, jsonHeaders, expectValidationError, uniqueValue } = require('../helpers/plain-e2e');

test('creating a department registers it in the departments data table', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;

  /* Act */
  const { response, name } = await createDepartment(page, request);
  const dataResponse = await departmentData(request, name);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(response.status(), 'Department creation should return a 302 redirect').toBe(302);
  expect(dataResponse.status(), 'Departments data table should return 200').toBe(200);
  const rows = dataPayload.data || [];
  expect(
    rows.some(row => row.name === name),
    `Newly created department "${name}" should appear in the data table`
  ).toBe(true);
});

test('unauthenticated users are redirected to login before the department creation page', async ({ page }) => {
  /* Arrange – navigate without a session */
  await page.goto(`${BASE_URL}/departments/create`);

  /* Assert */
  await expect(page, 'Guest should land on the login page, not the department form').toHaveURL(/login/);
});

test('submitting the department form without required fields returns a name field validation error', async ({ page }) => {
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

test('deleting a department removes it from the departments data table', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const name = uniqueValue('PW Dept Delete');
  const { response: createResponse } = await createDepartment(page, request, name);
  expect(createResponse.status(), 'Department creation must succeed before delete test').toBe(302);

  /* Locate the created department's external_id */
  const createdDataResponse = await departmentData(request, name);
  const createdDataPayload = await createdDataResponse.json();
  const createdRows = Array.isArray(createdDataPayload?.data) ? createdDataPayload.data : [];
  const createdRow = createdRows.find(row => row.name === name);
  const deptExternalId = createdRow?.external_id;
  expect(deptExternalId, 'Department must have an external_id to be deleted').toBeTruthy();

  /* Act */
  const deleteResponse = await request.delete(`${BASE_URL}/departments/${deptExternalId}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    maxRedirects: 0,
  });
  const dataResponse = await departmentData(request, name);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(deleteResponse.status(), 'Department deletion should return a redirect').toBeLessThan(400);
  expect(dataResponse.status(), 'Departments data table should return 200 after delete').toBe(200);
  const rows = dataPayload.data || [];
  expect(
    rows.some(row => row.name === name),
    `Deleted department "${name}" must not appear in the data table`
  ).toBe(false);
});
