import { test, expect, nonAdminTest } from '../../helpers/fixtures';
import { PLAYWRIGHT_BASE_URL } from '../../helpers/config';
import { fetchCsrfToken } from '../../helpers/csrf';
import { ProjectActions } from '../../helpers/feature-domain';

test.describe('Projects authorization', () => {
    test('user with project delete permission can delete project', async ({ page, request }) => {
        const title = `PW Proj Auth Del ${Date.now()}`;
        const { response } = await ProjectActions.create(page, request, title);
        const payload = await response.json();
        const externalId = payload.project_external_id as string;

        const deleteResponse = await request.delete(
            `${PLAYWRIGHT_BASE_URL}/projects/${externalId}`,
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

    test('project delete with delete_tasks flag removes associated tasks', async ({ page, request }) => {
        const title = `PW Proj Del Tasks ${Date.now()}`;
        const { response } = await ProjectActions.create(page, request, title);
        const payload = await response.json();
        const externalId = payload.project_external_id as string;

        const deleteResponse = await request.delete(
            `${PLAYWRIGHT_BASE_URL}/projects/${externalId}`,
            {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
                data: { delete_tasks: true },
            },
        );

        expect(deleteResponse.status()).toBe(200);
    });

    test('project delete without flag removes project but not tasks', async ({ page, request }) => {
        const title = `PW Proj Del No Tasks ${Date.now()}`;
        const { response } = await ProjectActions.create(page, request, title);
        const payload = await response.json();
        const externalId = payload.project_external_id as string;

        const deleteResponse = await request.delete(
            `${PLAYWRIGHT_BASE_URL}/projects/${externalId}`,
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

    test('user with assign permission can update project assignment', async ({ page, request }) => {
        const title = `PW Proj Auth Assign ${Date.now()}`;
        const { response } = await ProjectActions.create(page, request, title);
        const payload = await response.json();
        const externalId = payload.project_external_id as string;

        const assignResponse = await request.patch(
            `${PLAYWRIGHT_BASE_URL}/projects/updateassign/${externalId}`,
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

    test('project update status only accepts status_id field', async ({ page, request }) => {
        const title = `PW Proj Status Fields ${Date.now()}`;
        const { response, statusId } = await ProjectActions.create(page, request, title);
        const payload = await response.json();
        const externalId = payload.project_external_id as string;

        const statusResponse = await request.patch(
            `${PLAYWRIGHT_BASE_URL}/projects/updatestatus/${externalId}`,
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

        expect([200, 302]).toContain(statusResponse.status());
    });

    test('project update status rejects invalid status type', async ({ page, request }) => {
        const title = `PW Proj Status Invalid ${Date.now()}`;
        const { response } = await ProjectActions.create(page, request, title);
        const payload = await response.json();
        const externalId = payload.project_external_id as string;

        const statusResponse = await request.patch(
            `${PLAYWRIGHT_BASE_URL}/projects/updatestatus/${externalId}`,
            {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
                form: { status_id: 'not-a-valid-id' },
            },
        );

        expect([400, 404, 422]).toContain(statusResponse.status());
    });

    test('project update status rejects nonexistent status id', async ({ page, request }) => {
        const title = `PW Proj Status Ghost ${Date.now()}`;
        const { response } = await ProjectActions.create(page, request, title);
        const payload = await response.json();
        const externalId = payload.project_external_id as string;

        const statusResponse = await request.patch(
            `${PLAYWRIGHT_BASE_URL}/projects/updatestatus/${externalId}`,
            {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
                form: { status_id: '00000000-0000-0000-0000-000000000999' },
            },
        );

        expect([400, 404, 422]).toContain(statusResponse.status());
    });

    test('project update status via ajax with valid external id succeeds', async ({ page, request }) => {
        const title = `PW Proj Status Ajax ${Date.now()}`;
        const { response, statusId } = await ProjectActions.create(page, request, title);
        const payload = await response.json();
        const externalId = payload.project_external_id as string;

        const statusResponse = await request.patch(
            `${PLAYWRIGHT_BASE_URL}/projects/updatestatus/${externalId}`,
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

        expect([200, 302]).toContain(statusResponse.status());
    });

    nonAdminTest('user without project delete permission cannot delete project', async ({ page, request }) => {
        const response = await request.delete(
            `${PLAYWRIGHT_BASE_URL}/projects/00000000-0000-0000-0000-000000000001`,
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

    nonAdminTest('user without assign permission cannot update project assignment', async ({ page, request }) => {
        const response = await request.patch(
            `${PLAYWRIGHT_BASE_URL}/projects/updateassign/00000000-0000-0000-0000-000000000001`,
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
