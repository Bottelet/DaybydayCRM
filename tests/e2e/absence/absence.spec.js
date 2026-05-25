const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createAbsence, absenceData, jsonHeaders } = require('../helpers/plain-e2e');

test('absence registration is visible in the absence data feed', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { response, reason } = await createAbsence(page, request);

  /* Act */
  const dataResponse = await absenceData(request, reason);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(response.status()).toBe(200);
  expect(dataResponse.status()).toBe(200);
  const rows = Array.isArray(dataPayload?.data) ? dataPayload.data : [];
  expect(rows.length).toBeGreaterThan(0);
  expect(rows[0]).toHaveProperty('external_id');
});

test('absence can be registered for another user when management mode is used', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const usersResponse = await request.get(`${BASE_URL}/users/users`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
  const users = await usersResponse.json();
  const targetUser = users[0];

  /* Act */
  const createResponse = await request.post(`${BASE_URL}/absences`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {
      user_external_id: targetUser.external_id,
      reason: 'other',
      start_date: '2030/01/05',
      end_date: '2030/01/05',
      radio: 'irrelevant',
      comment: 'Playwright managed absence',
    },
  });
  const dataResponse = await absenceData(request, targetUser.name);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(usersResponse.status()).toBe(200);
  expect(createResponse.status()).toBe(200);
  expect(dataResponse.status()).toBe(200);
  const rows = Array.isArray(dataPayload?.data) ? dataPayload.data : [];
  expect(rows.some(row => String(row.user_id || '').includes(targetUser.name))).toBe(true);
});

test('absence deletion removes the created absence from the data feed', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { reason } = await createAbsence(page, request);
  const createdDataResponse = await absenceData(request, reason);
  const createdDataPayload = await createdDataResponse.json();
  const createdRows = Array.isArray(createdDataPayload?.data) ? createdDataPayload.data : [];
  const createdRow = createdRows[0];

  /* Act */
  const deleteResponse = await request.delete(`${BASE_URL}/absences/${createdRow.external_id}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
  });
  const dataResponse = await absenceData(request, reason);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(createdDataResponse.status()).toBe(200);
  expect(createdRow?.external_id).toBeTruthy();
  expect(deleteResponse.status()).toBe(200);
  const rows = Array.isArray(dataPayload?.data) ? dataPayload.data : [];
  expect(rows.some(row => row.external_id === createdRow.external_id)).toBe(false);
});

test('guests are redirected to login from the absence create page', async ({ page }) => {
  /* Arrange */
  await page.goto(`${BASE_URL}/absences/create`);

  /* Act */
  const loginButton = page.getByRole('button', { name: /log ?in|sign ?in/i });

  /* Assert */
  await expect(page).toHaveURL(/login/);
  await expect(loginButton).toBeVisible();
});
