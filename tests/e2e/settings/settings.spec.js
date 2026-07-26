const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, jsonHeaders, expectValidationError } = require('../helpers/plain-e2e');

test.describe.serial('Settings behavior', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
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

    // client_number/invoice_number must be strictly greater than the current
    // max across all rows (see ClientNumberValidator/InvoiceNumberValidator),
    // and within validateClientNumberSize's <= 9999999 cap — a hardcoded value
    // gets stale as test data accumulates and can't just be Date.now() either.
    // Settings#index already computes safe next values; reuse those.
    const currentSettings = await (await request.get(`${BASE_URL}/settings`, {
      headers: { Accept: 'application/json' },
    })).json();

    const response = await request.patch(`${BASE_URL}/settings/overall`, {
      failOnStatusCode: false,
      headers: await jsonHeaders(page),
      data: {
        company: `PW Settings ${Date.now()}`,
        country: 'GB',
        language: 'en',
        currency: 'GBP',
        client_number: currentSettings.client_number,
        invoice_number: currentSettings.invoice_number,
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
    // client_number/invoice_number are pre-filled with computed "next" values
    // (see settings/index.blade.php), so a plain click submits a valid
    // payload — clear them to genuinely trigger the required-field validation.
    await page.locator('input[name="client_number"]').fill('');
    await page.locator('input[name="invoice_number"]').fill('');
    await page.locator('form button[type="submit"], form input[type="submit"]').first().click();
    await expect(page.locator('.alert.alert-danger, .invalid-feedback').first()).toBeVisible();
  });

  test('settings business-hours endpoint reachable', async ({ page }) => {
    const request = page.context().request;

    const businessHours = await request.get(`${BASE_URL}/settings/business-hours`, {
      failOnStatusCode: false,
      headers: { Accept: 'application/json' },
    });

    expect(businessHours.status()).toBe(200);
  });

  test('settings date-formats endpoint reachable', async ({ page }) => {
    const request = page.context().request;

    const dateFormats = await request.get(`${BASE_URL}/settings/date-formats`, {
      failOnStatusCode: false,
      headers: { Accept: 'application/json' },
    });

    expect(dateFormats.status()).toBe(200);
  });
});
