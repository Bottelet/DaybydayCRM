import { test as guestTest, expect as guestExpect } from '@playwright/test';
import { test, expect } from '../../helpers/fixtures';
import { PLAYWRIGHT_BASE_URL } from '../../helpers/config';
import { ProjectActions, DomainAssertions } from '../../helpers/feature-domain';

const malformedId = 'invalid-@@@';

test.describe('Projects feature behavior', () => {
  test('store happy path creates project visible in projects data', async ({ page, request }) => {
    const title = `PW Project ${Date.now()}`;
    const { response } = await ProjectActions.create(page, request, title);

    expect(response.status()).toBe(200);
    const payload = await response.json();
    expect(payload).toHaveProperty('project_external_id');

    const dataResponse = await ProjectActions.data(request, title);
    await DomainAssertions.expectDataContainsTitle(dataResponse, title);
  });

  test('validation failure returns missing title', async ({ page, request }) => {
    const response = await request.post(`${PLAYWRIGHT_BASE_URL}/projects`, {
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
    await page.goto(`${PLAYWRIGHT_BASE_URL}/projects/create`);
    await page.locator('form button[type="submit"], form input[type="submit"]').first().click();

    const errorAlert = page.locator('.col-lg-12 > .alert.alert-danger').first();
    await expect(errorAlert).toBeVisible();

    const firstChildClassName = await page.locator('.col-lg-12 > :first-child').evaluate((element) => element.className);
    expect(firstChildClassName).toContain('alert');
  });

  test('update workflow rejects malformed project id', async ({ page, request }) => {
    const response = await request.patch(`${PLAYWRIGHT_BASE_URL}/projects/updatestatus/${malformedId}`, {
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

  test('workflow status transition succeeds for valid project', async ({ page, request }) => {
    const title = `PW Project Workflow ${Date.now()}`;
    const { response, statusId } = await ProjectActions.create(page, request, title);
    const payload = await response.json();
    const externalId = payload.project_external_id as string;

    const statusResponse = await request.patch(`${PLAYWRIGHT_BASE_URL}/projects/updatestatus/${externalId}`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': await (await import('../../helpers/csrf')).fetchCsrfToken(page),
      },
      form: { status_id: statusId },
    });

    expect(statusResponse.status()).toBe(302);
  });

  test('delete workflow archives project and hides it from data search', async ({ page, request }) => {
    const title = `PW Project Delete ${Date.now()}`;
    const { response } = await ProjectActions.create(page, request, title);
    const payload = await response.json();
    const externalId = payload.project_external_id as string;

    const deleteResponse = await request.delete(`${PLAYWRIGHT_BASE_URL}/projects/${externalId}`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': await (await import('../../helpers/csrf')).fetchCsrfToken(page),
      },
    });

    expect(deleteResponse.status()).toBe(200);
    const dataResponse = await ProjectActions.data(request, title);
    const dataPayload = await dataResponse.json();
    expect(JSON.stringify(dataPayload)).not.toContain(title);
  });

  test('data endpoint supports search payload', async ({ request }) => {
    const response = await ProjectActions.data(request, 'Project');
    expect(response.status()).toBe(200);
    const payload = await response.json();
    expect(payload).toHaveProperty('data');
  });
});

guestTest('guest is redirected from projects create', async ({ page }) => {
  await page.goto(`${PLAYWRIGHT_BASE_URL}/projects/create`);
  await guestExpect(page).toHaveURL(/login/);
});
