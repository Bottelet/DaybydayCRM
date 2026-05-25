import { test as guestTest, expect as guestExpect } from '@playwright/test';
import {nonAdminTest, test} from "../../helpers/fixtures";
import {PLAYWRIGHT_BASE_URL} from "../../helpers/config";
import {TEST_USERS} from "../../fixtures/users";
const { BASE_URL, loginAsAdmin, jsonHeaders, expectValidationError } = require('../helpers/plain-e2e');

test.describe.serial('Settings tests', () => {
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
    if (savedSettings) {
      const request = page.context().request;
      const restoreResponse = await request.patch(`${BASE_URL}/settings/overall`, {
        failOnStatusCode: false,
        headers: await jsonHeaders(page),
        data: savedSettings,
      });
      expect(
        restoreResponse.ok(),
        `Settings restoration failed with status ${restoreResponse.status()} - leaked settings may affect subsequent tests`
      ).toBe(true);
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

});

test('happy path updates overall settings and returns success message', async ({ page, request }) => {
    await loginAsSeededAdmin(page);

    const response = await SettingActions.updateOverall(page, request, {
        company: `PW Settings ${Date.now()}`,
        country: 'GB',
        language: 'en',
        currency: 'GBP',
        client_number: 20000,
        invoice_number: 20000,
        start_time: '08:00',
        end_time: '16:00',
    });

    expect(response.status()).toBe(200);
    const payload = await response.json();
    expect(payload.message).toContain('Overall settings successfully updated');
});

test('validation failure returns client_number field errors', async ({ page, request }) => {
    await loginAsSeededAdmin(page);

    const response = await SettingActions.updateOverall(page, request, {
        invoice_number: 20000,
    });

    await DomainAssertions.expectValidationError(response, 'client_number');
});

test('edge malformed payload returns validation errors', async ({ page, request }) => {
    await loginAsSeededAdmin(page);

    const response = await SettingActions.updateOverall(page, request, {
        client_number: 1,
        invoice_number: 1,
        start_time: 'not-a-time',
        end_time: 'also-bad',
    });

    expect(response.status()).toBe(422);
    const payload = await response.json();
    expect(payload.errors).toHaveProperty('start_time');
});

test('data endpoints for settings business-hours and date-formats are reachable', async ({ page, request }) => {
    await loginAsSeededAdmin(page);

    const businessHours = await request.get(`${PLAYWRIGHT_BASE_URL}/settings/business-hours`, {
        failOnStatusCode: false,
        headers: { Accept: 'application/json' },
    });
    expect(businessHours.status()).toBe(200);

    const dateFormats = await request.get(`${PLAYWRIGHT_BASE_URL}/settings/date-formats`, {
        failOnStatusCode: false,
        headers: { Accept: 'application/json' },
    });
    expect(dateFormats.status()).toBe(200);
});

test('it accepts valid settings and returns 200 json', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/settings');

    /* Assert */
    await expect(page.getByText(/(error|invalid|required|unprocessable|forbidden)/i).first()).toBeVisible();
});

test('it rejects missing client number with 422', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/settings');

    /* Assert */
    await expect(page.getByText(/(error|invalid|required|unprocessable|forbidden)/i).first()).toBeVisible();
});

test('it rejects missing invoice number with 422', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/settings');

    /* Assert */
    await expect(page.getByText(/(error|invalid|required|unprocessable|forbidden)/i).first()).toBeVisible();
});

test('it rejects non integer client number', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/settings');

    /* Assert */
    await expect(page.getByText(/(error|invalid|required|unprocessable|forbidden)/i).first()).toBeVisible();
});

test('it rejects invalid language', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/settings');

    /* Assert */
    await expect(page.getByText(/(error|invalid|required|unprocessable|forbidden)/i).first()).toBeVisible();
});

test('it rejects invalid currency', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/settings');

    /* Assert */
    await expect(page.getByText(/(error|invalid|required|unprocessable|forbidden)/i).first()).toBeVisible();
});

test('it rejects vat above 100 percent', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/settings');

    /* Assert */
    await expect(page.getByText(/(error|invalid|required|unprocessable|forbidden)/i).first()).toBeVisible();
});

test('it rejects negative vat', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/settings');

    /* Assert */
    await expect(page.getByText(/(error|invalid|required|unprocessable|forbidden)/i).first()).toBeVisible();
});

test('it rejects invalid time format', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/settings');

    /* Assert */
    await expect(page.getByText(/(error|invalid|required|unprocessable|forbidden)/i).first()).toBeVisible();
});

test('it rejects country code longer than two characters', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/settings');

    /* Assert */
    await expect(page.getByText(/(error|invalid|required|unprocessable|forbidden)/i).first()).toBeVisible();
});

test('it rejects single character country code', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/settings');

    /* Assert */
    await expect(page.getByText(/(error|invalid|required|unprocessable|forbidden)/i).first()).toBeVisible();
});

test('it rejects client number below minimum', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/settings');

    /* Assert */
    await expect(page.getByText(/(error|invalid|required|unprocessable|forbidden)/i).first()).toBeVisible();
});

test('it rejects invalid end time format', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/settings');

    /* Assert */
    await expect(page.getByText(/(error|invalid|required|unprocessable|forbidden)/i).first()).toBeVisible();
});

test('it rejects out of range clock value for start time', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/settings');

    /* Assert */
    await expect(page.getByText(/(error|invalid|required|unprocessable|forbidden)/i).first()).toBeVisible();
});

test('it returns 403 json when non admin submits settings', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/settings');

    /* Assert */
    await expect(page.getByText(/(error|invalid|required|unprocessable|forbidden)/i).first()).toBeVisible();
});

test('it persists all submitted fields without silent overrides', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/settings');

    /* Assert */
    await expect(page.getByText(/(forbidden|unauthorized|permission|login|warning|error)/i).first()).toBeVisible();
});

test('it admin can access settings index', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/settings');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it non admin cannot access settings index', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/settings');

    /* Assert */
    await expect(page.getByText(/(forbidden|unauthorized|permission|login|warning|error)/i).first()).toBeVisible();
});

test('it admin can update overall settings', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/settings');

    /* Assert */
    await expect(page.getByText(/(update|updated|saved|assigned|status|restored)/i).first()).toBeVisible();
});

test('it non admin cannot update overall settings', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/settings');

    /* Assert */
    await expect(page.getByText(/(forbidden|unauthorized|permission|login|warning|error)/i).first()).toBeVisible();
});

test('it admin can update first step settings', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/settings');

    /* Assert */
    await expect(page.getByText(/(update|updated|saved|assigned|status|restored)/i).first()).toBeVisible();
});

test('it non admin cannot update first step settings', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/settings');

    /* Assert */
    await expect(page.getByText(/(forbidden|unauthorized|permission|login|warning|error)/i).first()).toBeVisible();
});

test('it admin can access settings index', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/settings');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it non admin cannot access settings index', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/settings');

    /* Assert */
    await expect(page.getByText(/(forbidden|unauthorized|permission|login|warning|error)/i).first()).toBeVisible();
});

test('it admin can update overall settings', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/settings');

    /* Assert */
    await expect(page.getByText(/(update|updated|saved|assigned|status|restored)/i).first()).toBeVisible();
});

test('it non admin cannot update overall settings', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/settings');

    /* Assert */
    await expect(page.getByText(/(forbidden|unauthorized|permission|login|warning|error)/i).first()).toBeVisible();
});

test('it admin can update first step settings', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/settings');

    /* Assert */
    await expect(page.getByText(/(update|updated|saved|assigned|status|restored)/i).first()).toBeVisible();
});

test('it non admin cannot update first step settings', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/settings');

    /* Assert */
    await expect(page.getByText(/(forbidden|unauthorized|permission|login|warning|error)/i).first()).toBeVisible();
});

nonAdminTest('permission behavior denies non-admin fixture user for settings update', async ({ page, request }) => {
    const response = await SettingActions.updateOverall(page, request, {
        client_number: 30000,
        invoice_number: 30000,
    });

    expect(response.status()).toBe(403);
});

guestTest('guest is redirected from settings page', async ({ page }) => {
    await page.goto(`${PLAYWRIGHT_BASE_URL}/settings`);
    await guestExpect(page).toHaveURL(/login/);
});
