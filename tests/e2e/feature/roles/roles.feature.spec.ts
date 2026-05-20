import { test as guestTest, expect as guestExpect } from '@playwright/test';
import { test, expect } from '../../helpers/fixtures';
import { PLAYWRIGHT_BASE_URL } from '../../helpers/config';
import { RoleActions, DomainAssertions } from '../../helpers/feature-domain';

const malformedId = 'invalid-@@@';

test.describe('Roles feature behavior', () => {
  test('store happy path creates role visible in roles data', async ({ page, request }) => {
    const roleName = `pw_role_${Date.now()}`;
    const response = await RoleActions.create(page, request, roleName);

    expect(response.status()).toBe(200);
    const dataResponse = await RoleActions.data(request, roleName);
    await DomainAssertions.expectDataContainsTitle(dataResponse, roleName);
  });

  test('validation failure returns required name field error', async ({ page, request }) => {
    const response = await request.post(`${PLAYWRIGHT_BASE_URL}/roles`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': await (await import('../../helpers/csrf')).fetchCsrfToken(page),
      },
      form: {},
    });

    await DomainAssertions.expectValidationError(response, 'name');
  });

  test('create form validation alert is rendered at top of page content', async ({ page }) => {
    await page.goto(`${PLAYWRIGHT_BASE_URL}/roles/create`);
    await page.locator('form button[type="submit"], form input[type="submit"]').first().click();

    const errorAlert = page.locator('.col-lg-12 > .alert.alert-danger').first();
    await expect(errorAlert).toBeVisible();

    const firstChildClassName = await page.locator('.col-lg-12 > :first-child').evaluate((element) => element.className);
    expect(firstChildClassName).toContain('alert');
  });

  test('update workflow on malformed id returns not found', async ({ page, request }) => {
    const response = await request.patch(`${PLAYWRIGHT_BASE_URL}/roles/update/${malformedId}`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': await (await import('../../helpers/csrf')).fetchCsrfToken(page),
      },
      form: { permissions: [] },
    });

    expect(response.status()).toBe(404);
  });

  test('data endpoint supports role search filtering', async ({ request }) => {
    const response = await RoleActions.data(request, 'role');
    expect(response.status()).toBe(200);
    const payload = await response.json();
    expect(payload).toHaveProperty('data');
  });

  test('delete/archive behavior prevents deleting malformed role id', async ({ page, request }) => {
    const response = await request.delete(`${PLAYWRIGHT_BASE_URL}/roles/${malformedId}`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': await (await import('../../helpers/csrf')).fetchCsrfToken(page),
      },
    });

    expect(response.status()).toBe(404);
  });
});

guestTest('guest is redirected from roles page', async ({ page }) => {
  await page.goto(`${PLAYWRIGHT_BASE_URL}/roles`);
  await guestExpect(page).toHaveURL(/login/);
});
