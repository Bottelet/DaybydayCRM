import { test as guestTest, expect as guestExpect } from '@playwright/test';
import { test, expect, nonAdminTest } from '../../helpers/fixtures';
import { PLAYWRIGHT_BASE_URL } from '../../helpers/config';
import { fetchCsrfToken } from '../../helpers/csrf';
import { RoleActions, DomainAssertions } from '../../helpers/feature-domain';

test.describe('Roles authorization', () => {
  test('web error returned when role creation fails validation', async ({ page, request }) => {
    const response = await request.post(`${PLAYWRIGHT_BASE_URL}/roles`, {
      failOnStatusCode: false,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': await fetchCsrfToken(page),
      },
      form: {},
    });

    expect([302, 422]).toContain(response.status());
  });

  test('json error returned when role creation fails validation', async ({ page, request }) => {
    const response = await request.post(`${PLAYWRIGHT_BASE_URL}/roles`, {
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

  nonAdminTest('unprivileged user cannot access roles index', async ({ page }) => {
    await page.goto(`${PLAYWRIGHT_BASE_URL}/roles`);
    await expect(page).not.toHaveURL(/\/roles$/);
  });

  nonAdminTest('unprivileged user cannot update role permissions', async ({ page, request }) => {
    const response = await request.patch(
      `${PLAYWRIGHT_BASE_URL}/roles/update/00000000-0000-0000-0000-000000000001`,
      {
        failOnStatusCode: false,
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
        form: { permissions: '[]' },
      },
    );

    expect(response.status()).toBe(403);
  });

  nonAdminTest('unprivileged user cannot create a role', async ({ page, request }) => {
    const response = await request.post(`${PLAYWRIGHT_BASE_URL}/roles`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': await fetchCsrfToken(page),
      },
      form: { name: 'blocked_role', description: 'should not be created' },
    });

    expect(response.status()).toBe(403);
  });
});
