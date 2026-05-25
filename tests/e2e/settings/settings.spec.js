const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, jsonHeaders, expectValidationError } = require('../helpers/plain-e2e');

test.describe.serial('Settings behavior', () => {
  let savedSettings = null;

  test.beforeEach(async ({ page }) => {
    savedSettings = null;
    await loginAsAdmin(page);
    const request = page.context().request;

    const getResponse = await request.get(`${BASE_URL}/settings`, {
      failOnStatusCode: false,
      headers: await jsonHeaders(page),
    });

    if (getResponse.status() === 200) {
      const payload = await getResponse.json();
      savedSettings = payload.settings ?? null;
    }
  });

  test.afterEach(async ({ page }) => {
    if (!savedSettings) return;

    const request = page.context().request;
    const restoreResponse = await request.patch(`${BASE_URL}/settings/overall`, {
      failOnStatusCode: false,
      headers: await jsonHeaders(page),
      data: savedSettings,
    });

    expect(restoreResponse.ok()).toBe(true);
  });

  test('guest is redirected from settings route', async ({ browser }) => {
    const context = await browser.newContext();
    const page = await context.newPage();
    await page.goto(`${BASE_URL}/settings`);
    await expect(page).toHaveURL(/login/);
    await context.close();
  });

  test('updating overall settings returns success message', async ({ page }) => {
    const request = page.context().request;

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

    expect(response.status()).toBe(200);
    expect(payload.message).toContain('Overall settings successfully updated');
  });

  test('invalid currency returns validation error', async ({ page }) => {
    const request = page.context().request;

    const response = await request.patch(`${BASE_URL}/settings/overall`, {
      failOnStatusCode: false,
      headers: await jsonHeaders(page),
      data: {
        client_number: 20000,
        invoice_number: 20000,
        currency: 'NOTREAL',
      },
    });

    await expectValidationError(response, 'currency');
  });

  test('settings form submit without required numbers shows validation alert', async ({ page }) => {
    await page.goto(`${BASE_URL}/settings`);
    await page.locator('form button[type="submit"], form input[type="submit"]').first().click();
    await expect(page.locator('.alert.alert-danger, .invalid-feedback').first()).toBeVisible();
  });

  test('settings support endpoints are reachable for authenticated users', async ({ page }) => {
    const request = page.context().request;

    const businessHours = await request.get(`${BASE_URL}/settings/business-hours`, {
      failOnStatusCode: false,
      headers: { Accept: 'application/json' },
    });

    const dateFormats = await request.get(`${BASE_URL}/settings/date-formats`, {
      failOnStatusCode: false,
      headers: { Accept: 'application/json' },
    });

    expect(businessHours.status()).toBe(200);
    expect(dateFormats.status()).toBe(200);
  });
});
