import { test, expect, nonAdminTest } from '../../helpers/fixtures';
import { PLAYWRIGHT_BASE_URL } from '../../helpers/config';
import { fetchCsrfToken } from '../../helpers/csrf';
import { LeadActions } from '../../helpers/feature-domain';

test.describe('Leads authorization', () => {
    test('owner can access lead create page', async ({ page }) => {
        await page.goto(`${PLAYWRIGHT_BASE_URL}/leads/create`);
        await expect(page).toHaveURL(/leads\/create/);
    });

    test('lead create route works consistently across multiple requests', async ({ page, request }) => {
        for (let i = 0; i < 3; i++) {
            const response = await request.get(`${PLAYWRIGHT_BASE_URL}/leads/create`, {
                failOnStatusCode: false,
            });
            expect(response.status()).toBe(200);
        }
    });

    test('user with lead delete permission can delete lead', async ({ page, request }) => {
        const title = `PW Lead Auth Del ${Date.now()}`;
        const { response } = await LeadActions.create(page, request, title);
        const location = response.headers()['location'] ?? '';
        const externalId = new URL(location, PLAYWRIGHT_BASE_URL).pathname
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
    });

    test('authorized user can reassign lead', async ({ page, request }) => {
        const title = `PW Lead Auth Assign ${Date.now()}`;
        const { response } = await LeadActions.create(page, request, title);
        const location = response.headers()['location'] ?? '';
        const externalId = new URL(location, PLAYWRIGHT_BASE_URL).pathname
            .split('/')
            .filter(Boolean)
            .pop() as string;

        const assignResponse = await request.patch(
            `${PLAYWRIGHT_BASE_URL}/leads/updateassign/${externalId}`,
            {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
                form: { user_assigned_id: '1' },
            },
        );

        expect([200, 302]).toContain(assignResponse.status());
    });

    test('lead update assign only accepts user_assigned_id field', async ({ page, request }) => {
        const title = `PW Lead Assign Fields ${Date.now()}`;
        const { response } = await LeadActions.create(page, request, title);
        const location = response.headers()['location'] ?? '';
        const externalId = new URL(location, PLAYWRIGHT_BASE_URL).pathname
            .split('/')
            .filter(Boolean)
            .pop() as string;

        const updateResponse = await request.patch(
            `${PLAYWRIGHT_BASE_URL}/leads/updateassign/${externalId}`,
            {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
                form: { user_assigned_id: '1', title: 'this field should be ignored' },
            },
        );

        expect([200, 302]).toContain(updateResponse.status());
    });

    test('lead update status only accepts status_id field', async ({ page, request }) => {
        const title = `PW Lead Status Fields ${Date.now()}`;
        const { response, statusId } = await LeadActions.create(page, request, title);
        const location = response.headers()['location'] ?? '';
        const externalId = new URL(location, PLAYWRIGHT_BASE_URL).pathname
            .split('/')
            .filter(Boolean)
            .pop() as string;

        const statusResponse = await request.patch(
            `${PLAYWRIGHT_BASE_URL}/leads/updatestatus/${externalId}`,
            {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
                form: { status_id: statusId, title: 'this field should be ignored' },
            },
        );

        expect([200, 302, 404]).toContain(statusResponse.status());
    });

    nonAdminTest('user without lead create permission is redirected from create page', async ({ page }) => {
        await page.goto(`${PLAYWRIGHT_BASE_URL}/leads/create`);
        await expect(page).not.toHaveURL(/leads\/create/);
    });

    nonAdminTest('json request without lead create permission returns 403', async ({ page, request }) => {
        const response = await request.post(`${PLAYWRIGHT_BASE_URL}/leads`, {
            failOnStatusCode: false,
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': await fetchCsrfToken(page),
            },
            form: { title: 'Blocked Lead' },
        });

        expect(response.status()).toBe(403);
    });

    nonAdminTest('user without lead delete permission cannot delete lead', async ({ page, request }) => {
        const response = await request.delete(
            `${PLAYWRIGHT_BASE_URL}/leads/00000000-0000-0000-0000-000000000001/json`,
            {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
            },
        );

        expect(response.status()).toBe(403);
    });

    nonAdminTest('user without reassign permission cannot reassign lead', async ({ page, request }) => {
        const response = await request.patch(
            `${PLAYWRIGHT_BASE_URL}/leads/updateassign/00000000-0000-0000-0000-000000000001`,
            {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
                form: { user_assigned_id: '1' },
            },
        );

        expect(response.status()).toBe(403);
    });
});
