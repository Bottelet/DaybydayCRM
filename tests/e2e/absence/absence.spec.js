const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createAbsence, absenceData } = require('../helpers/plain-e2e');

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
  expect(JSON.stringify(dataPayload).toLowerCase()).toContain(reason.toLowerCase());
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
