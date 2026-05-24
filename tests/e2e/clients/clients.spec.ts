import { test as guestTest, expect as guestExpect } from '@playwright/test';
import { test, expect } from '../../helpers/fixtures';
import { PLAYWRIGHT_BASE_URL } from '../../helpers/config';
import { fetchCsrfToken } from '../../helpers/csrf';
import { ClientActions, DomainAssertions } from '../../helpers/feature-domain';

const malformedId = 'invalid-@@@';

test.describe('Clients feature behavior', () => {
    test('store happy path creates client visible in data endpoint', async ({ page, request }) => {
        const companyName = `PW Client ${Date.now()}`;
        const { response } = await ClientActions.create(page, request, companyName);

        expect(response.status()).toBe(201);
        const dataResponse = await ClientActions.data(request, companyName);
        await DomainAssertions.expectDataContainsTitle(dataResponse, companyName);
    });

    test('validation failure returns field-level error for name', async ({ page, request }) => {
        const response = await request.post(`${PLAYWRIGHT_BASE_URL}/clients`, {
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
        await page.goto(`${PLAYWRIGHT_BASE_URL}/clients/create`);
        await page.locator('#submitClient').click();

        await expect(page.locator('.col-lg-12 > .alert.alert-danger').first()).toBeVisible();
        const firstChildClass = await page
            .locator('.col-lg-12 > :first-child')
            .evaluate((el) => el.className);
        expect(firstChildClass).toContain('alert');
    });

    test('update workflow persists new company name', async ({ page, request }) => {
        const companyName = `PW Client Update ${Date.now()}`;
        const { response } = await ClientActions.create(page, request, companyName);
        const payload = await response.json();
        const externalId = payload.client.external_id as string;

        const updateResponse = await request.patch(
            `${PLAYWRIGHT_BASE_URL}/clients/${externalId}`,
            {
                failOnStatusCode: false,
                headers: { 'X-CSRF-TOKEN': await fetchCsrfToken(page) },
                form: {
                    name: `${companyName} Contact`,
                    company_name: `${companyName} Updated`,
                    email: `${Date.now()}@example.com`,
                    primary_number: '12345678',
                    secondary_number: '87654321',
                    vat: `${Date.now()}`.slice(-8),
                    zipcode: '1000',
                    city: 'Copenhagen',
                    industry_id: payload.client.industry_id,
                    user_id: payload.client.user_id,
                },
            },
        );

        expect(updateResponse.status()).toBe(302);

        const showResponse = await request.get(`${PLAYWRIGHT_BASE_URL}/clients/${externalId}`, {
            failOnStatusCode: false,
        });
        expect(showResponse.status()).toBe(200);
        expect(await showResponse.text()).toContain(`${companyName} Updated`);

        const dataResponse = await ClientActions.data(request, `${companyName} Updated`);
        await DomainAssertions.expectDataContainsTitle(dataResponse, `${companyName} Updated`);
    });

    test('delete removes client from data listing', async ({ page, request }) => {
        const companyName = `PW Client Delete ${Date.now()}`;
        const { response } = await ClientActions.create(page, request, companyName);
        const payload = await response.json();
        const externalId = payload.client.external_id as string;

        const deleteResponse = await request.delete(
            `${PLAYWRIGHT_BASE_URL}/clients/${externalId}`,
            {
                failOnStatusCode: false,
                headers: { 'X-CSRF-TOKEN': await fetchCsrfToken(page) },
            },
        );

        expect(deleteResponse.status()).toBe(302);

        const dataResponse = await ClientActions.data(request, companyName);
        const dataPayload = await dataResponse.json();
        expect(JSON.stringify(dataPayload)).not.toContain(companyName);
    });

    test('update with malformed id returns 404', async ({ page, request }) => {
        const response = await request.patch(
            `${PLAYWRIGHT_BASE_URL}/clients/${malformedId}`,
            {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
                form: {},
            },
        );

        expect(response.status()).toBe(404);
    });

    test('data endpoint returns structured payload with data key', async ({ request }) => {
        const response = await ClientActions.data(request, 'PW');
        expect(response.status()).toBe(200);
        const payload = await response.json();
        expect(payload).toHaveProperty('data');
    });
});

guestTest('guest is redirected from clients create', async ({ page }) => {
    await page.goto(`${PLAYWRIGHT_BASE_URL}/clients/create`);
    await guestExpect(page).toHaveURL(/login/);
});
