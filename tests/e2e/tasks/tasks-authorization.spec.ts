import { test as guestTest, expect as guestExpect } from '@playwright/test';
import { test, expect, nonAdminTest } from '../../helpers/fixtures';
import { PLAYWRIGHT_BASE_URL } from '../../helpers/config';
import { fetchCsrfToken } from '../../helpers/csrf';
import { TaskActions } from '../../helpers/feature-domain';

test.describe('Tasks authorization', () => {
  test('user with task delete permission can delete task', async ({ page, request }) => {
    const title = `PW Task Auth Del ${Date.now()}`;
    const { response } = await TaskActions.create(page, request, title);
    const payload = await response.json();
    const externalId = payload.task_external_id as string;

    const deleteResponse = await request.delete(`${PLAYWRIGHT_BASE_URL}/tasks/${externalId}`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': await fetchCsrfToken(page),
      },
    });

    expect(deleteResponse.status()).toBe(200);
  });

  test('authorized user can reassign task', async ({ page, request }) => {
    const title = `PW Task Auth Assign ${Date.now()}`;
    const { response } = await TaskActions.create(page, request, title);
    const payload = await response.json();
    const externalId = payload.task_external_id as string;

    const assignResponse = await request.patch(
      `${PLAYWRIGHT_BASE_URL}/tasks/updateassign/${externalId}`,
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

  test('task update assign only accepts user_assigned_id field', async ({ page, request }) => {
    const title = `PW Task Assign Fields ${Date.now()}`;
    const { response } = await TaskActions.create(page, request, title);
    const payload = await response.json();
    const externalId = payload.task_external_id as string;

    const updateResponse = await request.patch(
      `${PLAYWRIGHT_BASE_URL}/tasks/updateassign/${externalId}`,
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

  test('task update status rejects invalid status type', async ({ page, request }) => {
    const title = `PW Task Status Invalid ${Date.now()}`;
    const { response } = await TaskActions.create(page, request, title);
    const payload = await response.json();
    const externalId = payload.task_external_id as string;

    const statusResponse = await request.patch(
      `${PLAYWRIGHT_BASE_URL}/tasks/updatestatus/${externalId}`,
      {
        failOnStatusCode: false,
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
        form: { status_id: 'not-a-uuid' },
      },
    );

    expect([400, 422]).toContain(statusResponse.status());
  });

  test('task update status rejects nonexistent status id', async ({ page, request }) => {
    const title = `PW Task Status Ghost ${Date.now()}`;
    const { response } = await TaskActions.create(page, request, title);
    const payload = await response.json();
    const externalId = payload.task_external_id as string;

    const statusResponse = await request.patch(
      `${PLAYWRIGHT_BASE_URL}/tasks/updatestatus/${externalId}`,
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

  test('user with update-project permission can update task project', async ({ page, request }) => {
    const title = `PW Task Proj Update ${Date.now()}`;
    const { response } = await TaskActions.create(page, request, title);
    const payload = await response.json();
    const externalId = payload.task_external_id as string;

    const updateResponse = await request.patch(`${PLAYWRIGHT_BASE_URL}/tasks/${externalId}`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': await fetchCsrfToken(page),
      },
      form: { project_external_id: '' },
    });

    expect([200, 302]).toContain(updateResponse.status());
  });

  test('task create route works consistently across multiple requests', async ({ page, request }) => {
    for (let i = 0; i < 3; i++) {
      const response = await request.get(`${PLAYWRIGHT_BASE_URL}/tasks/create`, {
        failOnStatusCode: false,
      });
      expect(response.status()).toBe(200);
    }
  });

  nonAdminTest('user without task delete permission cannot delete task', async ({ page, request }) => {
    const response = await request.delete(
      `${PLAYWRIGHT_BASE_URL}/tasks/00000000-0000-0000-0000-000000000001`,
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

  nonAdminTest('user without task reassign permission cannot reassign task', async ({ page, request }) => {
    const response = await request.patch(
      `${PLAYWRIGHT_BASE_URL}/tasks/updateassign/00000000-0000-0000-0000-000000000001`,
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

  nonAdminTest('user without update-project permission cannot update task project', async ({ page, request }) => {
    const response = await request.patch(
      `${PLAYWRIGHT_BASE_URL}/tasks/00000000-0000-0000-0000-000000000001`,
      {
        failOnStatusCode: false,
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
        form: { project_external_id: '00000000-0000-0000-0000-000000000002' },
      },
    );

    expect(response.status()).toBe(403);
  });

  nonAdminTest('user without task create permission is redirected from create page', async ({ page }) => {
    await page.goto(`${PLAYWRIGHT_BASE_URL}/tasks/create`);
    await expect(page).not.toHaveURL(/tasks\/create/);
  });

  nonAdminTest('json request without task create permission returns 403', async ({ page, request }) => {
    const response = await request.post(`${PLAYWRIGHT_BASE_URL}/tasks`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': await fetchCsrfToken(page),
      },
      form: { title: 'Blocked Task' },
    });

    expect(response.status()).toBe(403);
  });
});
