import { test as guestTest, expect as guestExpect } from '@playwright/test';
import { test, expect } from '../../helpers/fixtures';
import { PLAYWRIGHT_BASE_URL } from '../../helpers/config';
import { fetchCsrfToken } from '../../helpers/csrf';
import { LeadActions, DomainAssertions } from '../../helpers/feature-domain';

const malformedId = 'invalid-@@@';

test.describe('Leads feature behavior', () => {
    test('store happy path creates lead and redirects to lead page', async ({ page, request }) => {
        const title = `PW Lead ${Date.now()}`;
        const { response } = await LeadActions.create(page, request, title);

        expect(response.status()).toBe(302);
        expect(response.headers()['location'] ?? '').toContain('/leads/');

        const dataResponse = await LeadActions.data(request, title);
        await DomainAssertions.expectDataContainsTitle(dataResponse, title);
    });

    test('validation failure returns required title field error', async ({ page, request }) => {
        const response = await request.post(`${PLAYWRIGHT_BASE_URL}/leads`, {
            failOnStatusCode: false,
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': await fetchCsrfToken(page),
            },
            form: {},
        });

        await DomainAssertions.expectValidationError(response, 'title');
    });

    test('create form shows validation alert as first element', async ({ page }) => {
        await page.goto(`${PLAYWRIGHT_BASE_URL}/leads/create`);
        await page.locator('form button[type="submit"], form input[type="submit"]').first().click();

        await expect(page.locator('.col-lg-12 > .alert.alert-danger').first()).toBeVisible();
        const firstChildClass = await page
            .locator('.col-lg-12 > :first-child')
            .evaluate((el) => el.className);
        expect(firstChildClass).toContain('alert');
    });

    test('status mutation on malformed id returns 404', async ({ page, request }) => {
        const response = await request.patch(
            `${PLAYWRIGHT_BASE_URL}/leads/updatestatus/${malformedId}`,
            {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
                form: { status_id: 1 },
            },
        );

        expect(response.status()).toBe(404);
    });

    test('delete removes lead from data endpoint', async ({ page, request }) => {
        const title = `PW Lead Delete ${Date.now()}`;
        const { response } = await LeadActions.create(page, request, title);
        const leadPath = response.headers()['location'] ?? '';
        const externalId = new URL(leadPath, PLAYWRIGHT_BASE_URL).pathname
            .split('/')
            .filter(Boolean)
            .pop() as string;

        const deleteResponse = await request.delete(
            `${PLAYWRIGHT_BASE_URL}/leads/${externalId}/json`,
            {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
            },
        );

        expect(deleteResponse.status()).toBe(200);

        const dataResponse = await LeadActions.data(request, title);
        expect(JSON.stringify(await dataResponse.json())).not.toContain(title);
    });

    test('data endpoint returns structured payload with data key', async ({ request }) => {
        const response = await LeadActions.data(request, 'Lead');
        expect(response.status()).toBe(200);
        const payload = await response.json();
        expect(payload).toHaveProperty('data');
    });
});

guestTest('guest is redirected from leads index', async ({ page }) => {
    await page.goto(`${PLAYWRIGHT_BASE_URL}/leads`);
    await guestExpect(page).toHaveURL(/login/);
});
