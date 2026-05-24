import { test as guestTest, expect as guestExpect } from '@playwright/test';
import { test, expect } from '../../helpers/fixtures';
import { PLAYWRIGHT_BASE_URL } from '../../helpers/config';
import { fetchCsrfToken } from '../../helpers/csrf';
import { UserActions, DomainAssertions } from '../../helpers/feature-domain';

const malformedId = 'invalid-@@@';

test.describe('Users feature behavior', () => {
    test('store happy path creates user visible in users data', async ({ page, request }) => {
        const name = `PW User ${Date.now()}`;
        const email = `pw_user_${Date.now()}@example.com`;
        const response = await UserActions.create(page, request, name, email);

        expect(response.status()).toBe(302);

        const dataResponse = await UserActions.data(request, name);
        await DomainAssertions.expectDataContainsTitle(dataResponse, name);
    });

    test('validation failure returns required name field error', async ({ page, request }) => {
        const response = await request.post(`${PLAYWRIGHT_BASE_URL}/users`, {
            failOnStatusCode: false,
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': await fetchCsrfToken(page),
            },
            form: {},
        });

        await DomainAssertions.expectValidationError(response, 'name');
    });

    test('create form shows validation alert as first element', async ({ page }) => {
        await page.goto(`${PLAYWRIGHT_BASE_URL}/users/create`);
        await page.locator('form button[type="submit"], form input[type="submit"]').first().click();

        await expect(page.locator('.col-lg-12 > .alert.alert-danger').first()).toBeVisible();
        const firstChildClass = await page
            .locator('.col-lg-12 > :first-child')
            .evaluate((el) => el.className);
        expect(firstChildClass).toContain('alert');
    });

    test('update with malformed user id returns 404', async ({ page, request }) => {
        const response = await request.patch(
            `${PLAYWRIGHT_BASE_URL}/users/${malformedId}`,
            {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
                form: { name: 'Invalid Update', email: 'invalid@example.com' },
            },
        );

        expect(response.status()).toBe(404);
    });

    test('delete with malformed user id returns 404', async ({ page, request }) => {
        const response = await request.delete(
            `${PLAYWRIGHT_BASE_URL}/users/${malformedId}`,
            {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
            },
        );

        expect(response.status()).toBe(404);
    });

    test('data endpoint returns structured payload with data key', async ({ request }) => {
        const response = await UserActions.data(request, 'User');
        expect(response.status()).toBe(200);
        const payload = await response.json();
        expect(payload).toHaveProperty('data');
    });
});

guestTest('guest is redirected from users index', async ({ page }) => {
    await page.goto(`${PLAYWRIGHT_BASE_URL}/users`);
    await guestExpect(page).toHaveURL(/login/);
});
