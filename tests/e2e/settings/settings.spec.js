const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, jsonHeaders, expectValidationError } = require('../helpers/plain-e2e');

let savedSettings = null;

test.beforeEach(async ({ page }) => {
  savedSettings = null;
  await loginAsAdmin(page);
  const request = page.context().request;
  const getResponse = await request.get(`${BASE_URL}/settings/overall`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
  });
  if (getResponse.status() === 200) {
    savedSettings = await getResponse.json();
  }
});

test.afterEach(async ({ page }) => {
  if (savedSettings) {
    const request = page.context().request;
    await request.patch(`${BASE_URL}/settings/overall`, {
      failOnStatusCode: false,
      headers: await jsonHeaders(page),
      data: savedSettings,
    });
  }
});

test('settings updates return the success message from the controller', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;

  /* Act */
  const response = await request.patch(`${BASE_URL}/settings/overall`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    data: {
      company: `PW Settings ${Date.now()}`,
      country: 'GB',
      language: 'en',
      currency: 'GBP',
      client_number: 20000,
      invoice_number: 20000,
      start_time: '08:00',
      end_time: '16:00',
    },
  });
  const payload = await response.json();

  /* Assert */
  expect(response.status()).toBe(200);
  expect(payload.message).toContain('Overall settings successfully updated');
});

test('settings validation reports invalid currency values', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;

  /* Act */
  const response = await request.patch(`${BASE_URL}/settings/overall`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    data: {
      client_number: 20000,
      invoice_number: 20000,
      currency: 'NOTREAL',
    },
  });

  /* Assert */
  await expectValidationError(response, 'currency');
});
