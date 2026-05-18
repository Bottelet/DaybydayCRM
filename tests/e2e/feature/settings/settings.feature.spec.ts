import { test as guestTest, expect as guestExpect } from '@playwright/test';
import { nonAdminTest, test, expect } from '../../helpers/fixtures';
import { PLAYWRIGHT_BASE_URL } from '../../helpers/config';
import { loginAsSeededAdmin } from '../../helpers/admin-auth';
import { SettingActions, DomainAssertions } from '../../helpers/feature-domain';

test.describe('Settings feature behavior', () => {
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

  nonAdminTest('permission behavior denies non-admin fixture user for settings update', async ({ page, request }) => {
    const response = await SettingActions.updateOverall(page, request, {
      client_number: 30000,
      invoice_number: 30000,
    });

    expect(response.status()).toBe(403);
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
});

guestTest('guest is redirected from settings page', async ({ page }) => {
  await page.goto(`${PLAYWRIGHT_BASE_URL}/settings`);
  await guestExpect(page).toHaveURL(/login/);
});
