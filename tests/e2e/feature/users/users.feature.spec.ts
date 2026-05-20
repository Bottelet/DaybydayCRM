import { test as guestTest, expect as guestExpect } from '@playwright/test';
import { test, expect } from '../../helpers/fixtures';
import { PLAYWRIGHT_BASE_URL } from '../../helpers/config';
import { UserActions, DomainAssertions } from '../../helpers/feature-domain';

const malformedId = 'invalid-@@@';

test.describe('Users feature behavior', () => {
  test('store happy path creates user visible in users data', async ({ page, request }) => {
    const name = `PW User ${Date.now()}`;
    const email = `pw_user_${Date.now()}@example.com`;
    const response = await UserActions.create(page, request, name, email);

    expect(response.status()).toBe(302);
    const dataResponse = await UserActions.data(request, name);
    await DomainAssertions.expectDataContainsTitle(dataResponse, name);
  });

  test('validation failure returns required name error', async ({ page, request }) => {
    const response = await request.post(`${PLAYWRIGHT_BASE_URL}/users`, {
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
    await page.goto(`${PLAYWRIGHT_BASE_URL}/users/create`);
    await page.locator('form button[type="submit"], form input[type="submit"]').first().click();

    const errorAlert = page.locator('.col-lg-12 > .alert.alert-danger').first();
    await expect(errorAlert).toBeVisible();

    const firstChildClassName = await page.locator('.col-lg-12 > :first-child').evaluate((element) => element.className);
    expect(firstChildClassName).toContain('alert');
  });

  test('update malformed user id returns not found', async ({ page, request }) => {
    const response = await request.patch(`${PLAYWRIGHT_BASE_URL}/users/${malformedId}`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': await (await import('../../helpers/csrf')).fetchCsrfToken(page),
      },
      form: {
        name: 'Invalid Update',
        email: 'invalid@example.com',
      },
    });

    expect(response.status()).toBe(404);
  });

  test('delete malformed user id is denied with not found', async ({ page, request }) => {
    const response = await request.delete(`${PLAYWRIGHT_BASE_URL}/users/${malformedId}`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': await (await import('../../helpers/csrf')).fetchCsrfToken(page),
      },
    });

    expect(response.status()).toBe(404);
  });

  test('users data endpoint supports searching', async ({ request }) => {
    const response = await UserActions.data(request, 'User');
    expect(response.status()).toBe(200);
    const payload = await response.json();
    expect(payload).toHaveProperty('data');
  });
});

guestTest('guest is redirected from users index', async ({ page }) => {
  await page.goto(`${PLAYWRIGHT_BASE_URL}/users`);
  await guestExpect(page).toHaveURL(/login/);
});
