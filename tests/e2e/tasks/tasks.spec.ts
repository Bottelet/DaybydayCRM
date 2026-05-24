import { test as guestTest, expect as guestExpect } from '@playwright/test';
import { test, expect } from '../../helpers/fixtures';
import { PLAYWRIGHT_BASE_URL } from '../../helpers/config';
import { fetchCsrfToken } from '../../helpers/csrf';
import { TaskActions, DomainAssertions } from '../../helpers/feature-domain';

const malformedId = 'invalid-@@@';

test.describe('Tasks feature behavior', () => {
    test('store happy path creates task visible in data endpoint', async ({ page, request }) => {
        const title = `PW Task ${Date.now()}`;
        const { response } = await TaskActions.create(page, request, title);

        expect(response.status()).toBe(200);
        const payload = await response.json();
        expect(payload).toHaveProperty('task_external_id');

        const dataResponse = await TaskActions.data(request, title);
        await DomainAssertions.expectDataContainsTitle(dataResponse, title);
    });

    test('validation failure returns required title field error', async ({ page, request }) => {
        const response = await request.post(`${PLAYWRIGHT_BASE_URL}/tasks`, {
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
        await page.goto(`${PLAYWRIGHT_BASE_URL}/tasks/create`);
        await page.locator('form button[type="submit"], form input[type="submit"]').first().click();

        await expect(page.locator('.col-lg-12 > .alert.alert-danger').first()).toBeVisible();
        const firstChildClass = await page
            .locator('.col-lg-12 > :first-child')
            .evaluate((el) => el.className);
        expect(firstChildClass).toContain('alert');
    });

    test('status update returns explicit error for malformed task id', async ({ page, request }) => {
        const response = await request.patch(
            `${PLAYWRIGHT_BASE_URL}/tasks/updatestatus/${malformedId}`,
            {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
                form: { status_id: 'abc' },
            },
        );

        expect(response.status()).toBe(400);
        const payload = await response.json();
        expect(payload.error).toContain('Invalid status id');
    });

    test('status transition updates task status successfully', async ({ page, request }) => {
        const title = `PW Task Workflow ${Date.now()}`;
        const { response, statusId } = await TaskActions.create(page, request, title);
        const created = await response.json();
        const taskExternalId = created.task_external_id as string;

        const statusResponse = await request.patch(
            `${PLAYWRIGHT_BASE_URL}/tasks/updatestatus/${taskExternalId}`,
            {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
                form: { status_id: statusId },
            },
        );

        expect(statusResponse.status()).toBe(200);
        const payload = await statusResponse.json();
        expect(payload.message).toContain('Task status is updated');
    });

    test('delete removes task from data endpoint', async ({ page, request }) => {
        const title = `PW Task Delete ${Date.now()}`;
        const { response } = await TaskActions.create(page, request, title);
        const payload = await response.json();
        const externalId = payload.task_external_id as string;

        const deleteResponse = await request.delete(
            `${PLAYWRIGHT_BASE_URL}/tasks/${externalId}`,
            {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
            },
        );

        expect(deleteResponse.status()).toBe(200);

        const dataResponse = await TaskActions.data(request, title);
        expect(JSON.stringify(await dataResponse.json())).not.toContain(title);
    });

    test('data endpoint returns structured payload with data key', async ({ request }) => {
        const response = await TaskActions.data(request, 'Task');
        expect(response.status()).toBe(200);
        const payload = await response.json();
        expect(payload).toHaveProperty('data');
    });
});

guestTest('guest is redirected from tasks index', async ({ page }) => {
    await page.goto(`${PLAYWRIGHT_BASE_URL}/tasks`);
    await guestExpect(page).toHaveURL(/login/);
});
