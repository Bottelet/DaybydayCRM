import { test as guestTest, expect as guestExpect } from '@playwright/test';
import { test, expect, nonAdminTest } from '../../helpers/fixtures';
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

    test('admin can access settings index', async ({ page }) => {
        await loginAsSeededAdmin(page);
        await page.goto(`${PLAYWRIGHT_BASE_URL}/settings`);
        await expect(page).toHaveURL(/settings/);
    });

    test('validation failure returns client_number field error', async ({ page, request }) => {
        await loginAsSeededAdmin(page);

        const response = await SettingActions.updateOverall(page, request, {
            invoice_number: 20000,
        });

        await DomainAssertions.expectValidationError(response, 'client_number');
    });

    test('validation failure returns invoice_number field error', async ({ page, request }) => {
        await loginAsSeededAdmin(page);

        const response = await SettingActions.updateOverall(page, request, {
            client_number: 20000,
        });

        await DomainAssertions.expectValidationError(response, 'invoice_number');
    });

    test('rejects invalid start_time format with 422', async ({ page, request }) => {
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

    test('rejects invalid end_time format with 422', async ({ page, request }) => {
        await loginAsSeededAdmin(page);

        const response = await SettingActions.updateOverall(page, request, {
            client_number: 20000,
            invoice_number: 20000,
            start_time: '08:00',
            end_time: 'not-a-time',
        });

        expect(response.status()).toBe(422);
        const payload = await response.json();
        expect(payload.errors).toHaveProperty('end_time');
    });

    test('rejects invalid currency with 422', async ({ page, request }) => {
        await loginAsSeededAdmin(page);

        const response = await SettingActions.updateOverall(page, request, {
            client_number: 20000,
            invoice_number: 20000,
            currency: 'NOTREAL',
        });

        expect(response.status()).toBe(422);
        const payload = await response.json();
        expect(payload.errors).toHaveProperty('currency');
    });

    test('rejects invalid language with 422', async ({ page, request }) => {
        await loginAsSeededAdmin(page);

        const response = await SettingActions.updateOverall(page, request, {
            client_number: 20000,
            invoice_number: 20000,
            language: 'xx',
        });

        expect(response.status()).toBe(422);
        const payload = await response.json();
        expect(payload.errors).toHaveProperty('language');
    });

    test('rejects country code longer than two characters', async ({ page, request }) => {
        await loginAsSeededAdmin(page);

        const response = await SettingActions.updateOverall(page, request, {
            client_number: 20000,
            invoice_number: 20000,
            country: 'GBR',
        });

        expect(response.status()).toBe(422);
        const payload = await response.json();
        expect(payload.errors).toHaveProperty('country');
    });

    test('business-hours endpoint is reachable and returns 200', async ({ page, request }) => {
        await loginAsSeededAdmin(page);

        const response = await request.get(`${PLAYWRIGHT_BASE_URL}/settings/business-hours`, {
            failOnStatusCode: false,
            headers: { Accept: 'application/json' },
        });

        expect(response.status()).toBe(200);
    });

    test('date-formats endpoint is reachable and returns 200', async ({ page, request }) => {
        await loginAsSeededAdmin(page);

        const response = await request.get(`${PLAYWRIGHT_BASE_URL}/settings/date-formats`, {
            failOnStatusCode: false,
            headers: { Accept: 'application/json' },
        });

        expect(response.status()).toBe(200);
    });

    nonAdminTest('non-admin cannot update overall settings — returns 403', async ({ page, request }) => {
        const response = await SettingActions.updateOverall(page, request, {
            client_number: 30000,
            invoice_number: 30000,
        });

        expect(response.status()).toBe(403);
    });

    nonAdminTest('non-admin cannot access settings index', async ({ page }) => {
        await page.goto(`${PLAYWRIGHT_BASE_URL}/settings`);
        await expect(page).not.toHaveURL(/\/settings$/);
    });
});

guestTest('guest is redirected from settings page', async ({ page }) => {
    await page.goto(`${PLAYWRIGHT_BASE_URL}/settings`);
    await guestExpect(page).toHaveURL(/login/);
});
