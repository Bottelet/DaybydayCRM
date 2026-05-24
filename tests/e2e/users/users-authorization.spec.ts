import { test, expect, nonAdminTest } from '../../helpers/fixtures';
import { PLAYWRIGHT_BASE_URL } from '../../helpers/config';
import { fetchCsrfToken } from '../../helpers/csrf';
import { TEST_USERS } from '../../fixtures/users';
import { UserActions } from '../../helpers/feature-domain';

test.describe('Users authorization', () => {
    test('user create route works consistently across multiple requests', async ({ page, request }) => {
        for (let i = 0; i < 3; i++) {
            const response = await request.get(`${PLAYWRIGHT_BASE_URL}/users/create`, {
                failOnStatusCode: false,
            });
            expect(response.status()).toBe(200);
        }
    });

    test('owner user cannot be deleted even with delete permission', async ({ page, request }) => {
        const dataResponse = await request.get(
            `${PLAYWRIGHT_BASE_URL}/users/data?draw=1&start=0&length=100`,
            { failOnStatusCode: false, headers: { Accept: 'application/json' } },
        );
        const data = await dataResponse.json();
        const ownerRow = (data.data as Array<Record<string, string>>).find((row) =>
            JSON.stringify(row).includes(TEST_USERS.owner.email),
        );

        if (!ownerRow) return;

        const externalId = (ownerRow['external_id'] ?? ownerRow['DT_RowId'] ?? '') as string;

        const deleteResponse = await request.delete(
            `${PLAYWRIGHT_BASE_URL}/users/${externalId}`,
            {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
            },
        );

        expect([403, 422]).toContain(deleteResponse.status());
    });

    test('user update prevents password change without permission', async ({ page, request }) => {
        const response = await request.patch(
            `${PLAYWRIGHT_BASE_URL}/users/00000000-0000-0000-0000-000000000002`,
            {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
                form: { password: 'newpassword', password_confirmation: 'newpassword' },
            },
        );

        expect([403, 422]).toContain(response.status());
    });

    nonAdminTest('user without user create permission is redirected from create page', async ({ page }) => {
        await page.goto(`${PLAYWRIGHT_BASE_URL}/users/create`);
        await expect(page).not.toHaveURL(/users\/create/);
    });

    nonAdminTest('json request without user create permission returns 403', async ({ page, request }) => {
        const response = await request.post(`${PLAYWRIGHT_BASE_URL}/users`, {
            failOnStatusCode: false,
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': await fetchCsrfToken(page),
            },
            form: { name: 'Blocked', email: 'blocked@example.com' },
        });

        expect(response.status()).toBe(403);
    });

    nonAdminTest('unauthorized user cannot edit another user', async ({ page, request }) => {
        const response = await request.patch(
            `${PLAYWRIGHT_BASE_URL}/users/00000000-0000-0000-0000-000000000001`,
            {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
                form: { name: 'Hacker' },
            },
        );

        expect(response.status()).toBe(403);
    });

    nonAdminTest('user without user delete permission cannot delete user', async ({ page, request }) => {
        const response = await request.delete(
            `${PLAYWRIGHT_BASE_URL}/users/00000000-0000-0000-0000-000000000001`,
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
});
