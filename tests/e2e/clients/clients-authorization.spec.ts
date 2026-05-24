import { test as guestTest, expect as guestExpect } from '@playwright/test';
import { test, expect, nonAdminTest } from '../../helpers/fixtures';
import { PLAYWRIGHT_BASE_URL } from '../../helpers/config';
import { fetchCsrfToken } from '../../helpers/csrf';
import { TEST_USERS } from '../../fixtures/users';
import { ClientActions } from '../../helpers/feature-domain';

test.describe('Clients authorization', () => {
  test('owner can access client create page', async ({ page }) => {
    await page.goto(`${PLAYWRIGHT_BASE_URL}/clients/create`);
    await expect(page).toHaveURL(/clients\/create/);
  });

  test('user with client delete permission can delete client', async ({ page, request }) => {
    const companyName = `PW Client Auth Del ${Date.now()}`;
    const { response } = await ClientActions.create(page, request, companyName);
    const payload = await response.json();
    const externalId = payload.client.external_id as string;

    const deleteResponse = await request.delete(`${PLAYWRIGHT_BASE_URL}/clients/${externalId}`, {
      failOnStatusCode: false,
      headers: {
        'X-CSRF-TOKEN': await fetchCsrfToken(page),
      },
    });

    expect(deleteResponse.status()).toBe(302);
  });

  nonAdminTest('user without client create permission is redirected from create page', async ({ page }) => {
    await page.goto(`${PLAYWRIGHT_BASE_URL}/clients/create`);
    await expect(page).not.toHaveURL(/clients\/create/);
  });

  nonAdminTest('json request without client create permission returns 403', async ({ page, request }) => {
    const response = await request.post(`${PLAYWRIGHT_BASE_URL}/clients`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': await fetchCsrfToken(page),
      },
      form: { name: 'Blocked Client', company_name: 'Blocked' },
    });

    expect(response.status()).toBe(403);
  });

  nonAdminTest('user without client delete permission cannot delete client', async ({ page, request }) => {
    const response = await request.delete(
      `${PLAYWRIGHT_BASE_URL}/clients/00000000-0000-0000-0000-000000000001`,
      {
        failOnStatusCode: false,
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
      },
    );

    expect(response.status()).toBe(403);
  });

  nonAdminTest('user without assignee permission cannot update client assignee', async ({ page, request }) => {
    const response = await request.patch(
      `${PLAYWRIGHT_BASE_URL}/clients/00000000-0000-0000-0000-000000000001/assign`,
      {
        failOnStatusCode: false,
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
        form: { user_id: '1' },
      },
    );

    expect(response.status()).toBe(403);
  });

  test('client create route works consistently across multiple requests', async ({ page, request }) => {
    // Verifies permission cache does not break the route on repeated hits
    for (let i = 0; i < 3; i++) {
      const response = await request.get(`${PLAYWRIGHT_BASE_URL}/clients/create`, {
        failOnStatusCode: false,
      });
      expect(response.status()).toBe(200);
    }
  });
});
