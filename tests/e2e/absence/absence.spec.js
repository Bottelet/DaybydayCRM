const { test, expect } = require('@playwright/test');
const {
  BASE_URL,
  loginAsAdmin,
  createAbsence,
  absenceData,
  jsonHeaders,
} = require('../helpers/plain-e2e');
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
  expect(Object.keys(payload.errors ?? {}).length).toBeGreaterThan(0);
});
  expect(Array.isArray(payload.errors)).toBe(true);
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
  const beforeDataResponse = await absenceData(request);
  const beforePayload = await beforeDataResponse.json();
  const beforeIds = new Set((beforePayload.data ?? []).map((row) => row.external_id));

  const { response: createResponse } = await createAbsence(page, request);
  const afterCreateDataResponse = await absenceData(request);
  const afterCreatePayload = await afterCreateDataResponse.json();
  const createdRow = (afterCreatePayload.data ?? []).find((row) => row.external_id && !beforeIds.has(row.external_id));
  expect(createResponse.status()).toBe(200);
  expect(createdRow?.external_id).toBeTruthy();

  const deleteResponse = await request.delete(`${BASE_URL}/absences/${createdRow.external_id}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
  });

  const afterDataResponse = await absenceData(request);
  const afterPayload = await afterDataResponse.json();

  expect(deleteResponse.status()).toBe(200);
  expect((afterPayload.data ?? []).some((row) => row.external_id === createdRow.external_id)).toBe(false);
});
