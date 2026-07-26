const { test, expect } = require('@playwright/test');
const {
  BASE_URL,
  loginAsAdmin,
  createAbsence,
  absenceData,
  jsonHeaders,
} = require('../helpers/plain-e2e');
test('empty absence payload returns 422 with validation errors', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  const response = await request.post(`${BASE_URL}/absences`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {},
  });

  expect(response.status()).toBe(422);
  const payload = await response.json();
  expect(payload.errors).toBeTruthy();
  expect(Object.keys(payload.errors)).toContain('reason');
  expect(Object.keys(payload.errors)).toContain('start_date');
  expect(Object.keys(payload.errors)).toContain('end_date');
});

test('absence create form shows alert when submitted empty', async ({ page }) => {
  await loginAsAdmin(page);
  await page.goto(`${BASE_URL}/absences/create`);
  // "reason" has no blank option (always defaults to the first choice), and
  // start_date/end_date are backed by pickadate.js: the visible input is a
  // readonly, unnamed decoy, while the real `name="start_date"` field is a
  // hidden input defaulted to today via JS — a plain click never actually
  // submits empty. Clear the hidden inputs directly to genuinely trigger
  // StoreAbsenceRequest's required validation.
  await page.evaluate(() => {
    document.querySelector('input[name="start_date"]').value = '';
    document.querySelector('input[name="end_date"]').value = '';
  });
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
