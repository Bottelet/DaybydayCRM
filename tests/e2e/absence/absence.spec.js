const { test, expect } = require('@playwright/test');
const {
  BASE_URL,
  loginAsAdmin,
  createAbsence,
  absenceData,
  jsonHeaders,
} = require('../helpers/plain-e2e');

test('guest is redirected from absences create route', async ({ page }) => {
  await page.goto(`${BASE_URL}/absences/create`);
  await expect(page).toHaveURL(/login/);
});

test('creating an absence makes it visible in absences data feed', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  const { response, externalId } = await createAbsence(page, request);
  const dataResponse = await absenceData(request);
  const payload = await dataResponse.json();

  expect(response.status()).toBe(200);
  expect(dataResponse.status()).toBe(200);
  expect((payload.data ?? []).some((row) => row.external_id === externalId)).toBe(true);
});

test('empty absence payload returns validation errors', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  const response = await request.post(`${BASE_URL}/absences`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {},
  });

  const payload = await response.json();
  expect(response.status()).toBe(422);
  expect(payload.errors).toBeTruthy();
  expect(Object.keys(payload.errors).length).toBeGreaterThan(0);
});

test('absence create form shows alert when submitted empty', async ({ page }) => {
  await loginAsAdmin(page);
  await page.goto(`${BASE_URL}/absences/create`);
  await page.locator('form button[type="submit"], form input[type="submit"]').first().click();
  await expect(page.locator('.alert.alert-danger, .invalid-feedback').first()).toBeVisible();
});

test('deleting an absence removes it from absences data feed', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  const { response: createResponse, externalId } = await createAbsence(page, request);
  expect(createResponse.status()).toBe(200);
  expect(externalId).toBeTruthy();

  const deleteResponse = await request.delete(`${BASE_URL}/absences/${externalId}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
  });

  const afterDataResponse = await absenceData(request);
  const afterPayload = await afterDataResponse.json();

  expect(deleteResponse.status()).toBe(200);
  expect((afterPayload.data ?? []).some((row) => row.external_id === externalId)).toBe(false);
});
