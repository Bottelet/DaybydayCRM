/**
 * Route-exposure tests: verify that internal/unused route prefixes are not
 * accidentally exposed. These are negative tests — a 404 is the correct outcome.
 */

const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin } = require('../helpers/plain-e2e');

test('/journeys index is not a registered route', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  const response = await request.get(`${BASE_URL}/journeys`, {
    failOnStatusCode: false,
    maxRedirects: 0,
  });

  expect(response.status()).toBe(404);
});

test('/journeys/create is not a registered route', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  const response = await request.get(`${BASE_URL}/journeys/create`, {
    failOnStatusCode: false,
    maxRedirects: 0,
  });

  expect(response.status()).toBe(404);
});

/**
 * Regression guard: Route::resource() registers all 7 RESTful routes
 * regardless of whether the controller implements all 7 methods. Several
 * controllers here only ever needed a subset (their real UI uses narrower,
 * purpose-specific endpoints instead - update-status, update-assignee,
 * update-deadline, etc.) but the full resource() routes were still being
 * registered, so hitting one of the never-implemented ones directly threw
 * "Call to undefined method" as an uncaught 500, instead of a clean 404.
 * Fixed via ['except' => [...]] on each Route::resource() call, matching the
 * pattern already used for 'roles'. These routes never had any link pointing
 * to them anywhere in the UI, so this is unreachable-surface cleanup, not a
 * behavior change for real users - but should 404, not 500, if ever hit.
 */
const deadResourceRoutes = [
  ['GET', '/tasks/00000000-0000-0000-0000-000000000000/edit'],
  ['GET', '/leads/00000000-0000-0000-0000-000000000000/edit'],
  ['GET', '/projects/00000000-0000-0000-0000-000000000000/edit'],
  ['GET', '/integrations/create'],
  ['GET', '/integrations/1'],
  ['GET', '/integrations/1/edit'],
];

for (const [method, path] of deadResourceRoutes) {
  test(`${method} ${path} is not a registered route (unimplemented resource method)`, async ({ page }) => {
    await loginAsAdmin(page);
    const request = page.context().request;

    const response = await request.fetch(`${BASE_URL}${path}`, {
      method,
      failOnStatusCode: false,
      maxRedirects: 0,
    });

    expect(response.status()).toBe(404);
  });
}

test('GET /departments/{id} is not a registered route (unimplemented resource method)', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  // departments.destroy (DELETE) is still registered for this same URI, so
  // Laravel correctly returns 405 (method not allowed) rather than 404 here.
  const response = await request.get(`${BASE_URL}/departments/00000000-0000-0000-0000-000000000000`, {
    failOnStatusCode: false,
    maxRedirects: 0,
  });

  expect(response.status()).toBe(405);
});
