const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createAbsence, absenceData, jsonHeaders, uniqueValue } = require('../helpers/plain-e2e');

test('registering an absence creates a record visible in the management feed', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;

  /* Act */
  const { response, externalId: createdExternalId } = await createAbsence(page, request);
  const dataResponse = await absenceData(request);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(response.status(), 'Absence store endpoint should return 200').toBe(200);
  expect(dataResponse.status(), 'Absence data feed should return 200').toBe(200);
  const rows = Array.isArray(dataPayload?.data) ? dataPayload.data : [];
  expect(rows.length, 'At least one absence should be visible in the feed after creation').toBeGreaterThan(0);
  expect(createdExternalId, 'Created absence must have an external_id').toBeTruthy();
  expect(
    rows.some(row => row.external_id === createdExternalId),
    `The created absence with external_id ${createdExternalId} should appear in the feed`
  ).toBe(true);
});

test('an admin with absence-manage permission can register an absence for another user', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const usersResponse = await request.get(`${BASE_URL}/users/users`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
  expect(usersResponse.status(), 'Users collection endpoint should return 200').toBe(200);
  const users = await usersResponse.json();
  const targetUser = users[0];

  /* Act – register absence for target user as admin */
  const createResponse = await request.post(`${BASE_URL}/absences`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {
      user_external_id: targetUser.external_id,
      reason: 'other',
      start_date: '2030/01/07',
      end_date: '2030/01/07',
      radio: 'irrelevant',
      comment: 'Playwright managed absence',
    },
  });

  /* Fetch all absences and locate the one belonging to targetUser (user_id column is
     transformed server-side to the user's display name by the DataTables callback). */
  const allDataResponse = await absenceData(request);
  const allDataPayload = await allDataResponse.json();

  /* Assert */
  expect(createResponse.status(), 'Managed absence creation should return 200').toBe(200);
  expect(allDataResponse.status(), 'Absence data feed should return 200').toBe(200);
  const rows = Array.isArray(allDataPayload?.data) ? allDataPayload.data : [];
  expect(
    rows.some(row => String(row.user_id ?? '').includes(targetUser.name)),
    `At least one row should list ${targetUser.name} as the absence owner (user_id column is rendered as the user display name)`
  ).toBe(true);
});

test('deleting an absence removes it permanently from the management feed', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { response: createResponse, externalId: createdExternalId } = await createAbsence(page, request);
  expect(createResponse.status(), 'Absence creation must succeed before delete test').toBe(200);
  expect(createdExternalId, 'Created absence must have an external_id for deletion').toBeTruthy();

  /* Verify the created absence appears in the feed before deletion */
  const allBeforeResponse = await absenceData(request);
  const allBeforePayload = await allBeforeResponse.json();
  const rowsBefore = Array.isArray(allBeforePayload?.data) ? allBeforePayload.data : [];
  expect(
    rowsBefore.some(row => row.external_id === createdExternalId),
    `Created absence ${createdExternalId} should appear in the feed before deletion`
  ).toBe(true);

  /* Act */
  const deleteResponse = await request.delete(`${BASE_URL}/absences/${createdExternalId}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
  });
  const allAfterResponse = await absenceData(request);
  const allAfterPayload = await allAfterResponse.json();

  /* Assert */
  expect(deleteResponse.status(), 'Delete endpoint should return 200').toBe(200);
  expect(allAfterResponse.status(), 'Absence feed after delete should return 200').toBe(200);
  const rowsAfter = Array.isArray(allAfterPayload?.data) ? allAfterPayload.data : [];
  expect(
    rowsAfter.some(row => row.external_id === createdExternalId),
    `Deleted absence ${createdExternalId} should no longer appear in the feed`
  ).toBe(false);
});

test('submitting an absence form without required fields returns a validation error', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;

  /* Act */
  const response = await request.post(`${BASE_URL}/absences`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {},
  });
  const payload = await response.json();

  /* Assert */
  expect(response.status(), 'Empty absence submission should return 422').toBe(422);
  expect(payload.errors, 'Response should contain field-level validation errors').toBeTruthy();
  expect(Object.keys(payload.errors), 'At least one required field error must be returned').not.toHaveLength(0);
});

test('unauthenticated users are redirected to login before reaching the absence creation page', async ({ page }) => {
  /* Arrange – navigate without logging in */
  await page.goto(`${BASE_URL}/absences/create`);

  /* Assert */
  await expect(page, 'Guest should land on the login page, not the absence form').toHaveURL(/login/);
});
