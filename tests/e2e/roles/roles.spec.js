import { test as guestTest, expect as guestExpect } from '@playwright/test';
import {test} from "../../helpers/fixtures";
const { BASE_URL, loginAsAdmin, createRole, roleData, jsonHeaders, expectValidationError, uniqueValue } = require('../helpers/plain-e2e');
const {PLAYWRIGHT_BASE_URL} = require("../../helpers/config");

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

test('store happy path creates role visible in roles data', async ({ page, request }) => {
    const roleName = `pw_role_${Date.now()}`;
    const response = await RoleActions.create(page, request, roleName);

    expect(response.status()).toBe(200);
    const dataResponse = await RoleActions.data(request, roleName);
    await DomainAssertions.expectDataContainsTitle(dataResponse, roleName);
});

test('validation failure returns required name field error', async ({ page, request }) => {
    const response = await request.post(`${PLAYWRIGHT_BASE_URL}/roles`, {
        failOnStatusCode: false,
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': await (await import('../../helpers/csrf')).fetchCsrfToken(page),
        },
        form: {},
    });

    await DomainAssertions.expectValidationError(response, 'name');
});

test('create form validation alert is rendered at top of page content', async ({ page }) => {
    await page.goto(`${PLAYWRIGHT_BASE_URL}/roles/create`);
    await page.locator('form button[type="submit"], form input[type="submit"]').first().click();

    const errorAlert = page.locator('.col-lg-12 > .alert.alert-danger').first();
    await expect(errorAlert).toBeVisible();

    const firstChildClassName = await page.locator('.col-lg-12 > :first-child').evaluate((element) => element.className);
    expect(firstChildClassName).toContain('alert');
});

test('update workflow on malformed id returns not found', async ({ page, request }) => {
    const response = await request.patch(`${PLAYWRIGHT_BASE_URL}/roles/update/${malformedId}`, {
        failOnStatusCode: false,
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': await (await import('../../helpers/csrf')).fetchCsrfToken(page),
        },
        form: { permissions: [] },
    });

    expect(response.status()).toBe(404);
});

test('data endpoint supports role search filtering', async ({ request }) => {
    const response = await RoleActions.data(request, 'role');
    expect(response.status()).toBe(200);
    const payload = await response.json();
    expect(payload).toHaveProperty('data');
});

test('delete/archive behavior prevents deleting malformed role id', async ({ page, request }) => {
    const response = await request.delete(`${PLAYWRIGHT_BASE_URL}/roles/${malformedId}`, {
        failOnStatusCode: false,
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': await (await import('../../helpers/csrf')).fetchCsrfToken(page),
        },
    });

    expect(response.status()).toBe(404);
});

guestTest('guest is redirected from roles page', async ({ page }) => {
    await page.goto(`${PLAYWRIGHT_BASE_URL}/roles`);
    await guestExpect(page).toHaveURL(/login/);
});
