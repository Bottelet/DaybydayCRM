import { test as guestTest, expect as guestExpect } from '@playwright/test';
import { test, expect } from '../../helpers/fixtures';
import { PLAYWRIGHT_BASE_URL } from '../../helpers/config';
import { LeadActions, DomainAssertions } from '../../helpers/feature-domain';

const malformedId = 'invalid-@@@';

test.describe('Leads feature behavior', () => {
  test('store happy path creates a lead and redirects to lead page', async ({ page, request }) => {
    const title = `PW Lead ${Date.now()}`;
    const { response } = await LeadActions.create(page, request, title);

    expect(response.status()).toBe(302);
    expect(response.headers()['location'] ?? '').toContain('/leads/');

    const dataResponse = await LeadActions.data(request, title);
    await DomainAssertions.expectDataContainsTitle(dataResponse, title);
  });

  test('validation failure returns required-field error', async ({ page, request }) => {
    const response = await request.post(`${PLAYWRIGHT_BASE_URL}/leads`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': await (await import('../../helpers/csrf')).fetchCsrfToken(page),
      },
      form: {},
    });

    await DomainAssertions.expectValidationError(response, 'title');
  });

  test('create form validation alert is rendered at top of page content', async ({ page }) => {
    await page.goto(`${PLAYWRIGHT_BASE_URL}/leads/create`);
    await page.locator('form button[type="submit"], form input[type="submit"]').first().click();

    const errorAlert = page.locator('.col-lg-12 > .alert.alert-danger').first();
    await expect(errorAlert).toBeVisible();

    const firstChildClassName = await page.locator('.col-lg-12 > :first-child').evaluate((element) => element.className);
    expect(firstChildClassName).toContain('alert');
  });

  test('workflow status mutation on malformed input returns not found', async ({ page, request }) => {
    const response = await request.patch(`${PLAYWRIGHT_BASE_URL}/leads/updatestatus/${malformedId}`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': await (await import('../../helpers/csrf')).fetchCsrfToken(page),
      },
      form: { status_id: 1 },
    });

    expect(response.status()).toBe(404);
  });

  test('delete workflow removes lead from data endpoint', async ({ page, request }) => {
    const title = `PW Lead Delete ${Date.now()}`;
    const { response } = await LeadActions.create(page, request, title);
    const leadPath = response.headers()['location'] ?? '';
    const leadUrl = new URL(leadPath, PLAYWRIGHT_BASE_URL);
    const externalId = leadUrl.pathname.split('/').filter(Boolean).pop() as string;

    const deleteResponse = await request.delete(`${PLAYWRIGHT_BASE_URL}/leads/${externalId}/json`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': await (await import('../../helpers/csrf')).fetchCsrfToken(page),
      },
    });

    expect(deleteResponse.status()).toBe(200);
    const dataResponse = await LeadActions.data(request, title);
    const payload = await dataResponse.json();
    expect(JSON.stringify(payload)).not.toContain(title);
  });

  test('data and search endpoint returns lead collections', async ({ request }) => {
    const response = await LeadActions.data(request, 'Lead');
    expect(response.status()).toBe(200);
    const payload = await response.json();
    expect(payload).toHaveProperty('data');
  });
});

guestTest('guest is redirected from leads index', async ({ page }) => {
  await page.goto(`${PLAYWRIGHT_BASE_URL}/leads`);
  await guestExpect(page).toHaveURL(/login/);
});
