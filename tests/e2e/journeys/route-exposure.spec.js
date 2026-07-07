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
 * regardless of whether the controller implements all 7 methods. tasks,
 * leads, projects, and departments have since had real edit/update (and,
 * for departments, show) methods implemented - see
 * tests/e2e/{tasks,leads,projects}/*.spec.js and
 * tests/e2e/departments/departments.spec.js for the positive coverage.
 *
 * integrations is different: its store() already upserts by api_type (one
 * billing integration, one filesystem integration) and disconnecting has
 * its own dedicated route (integration.revoke-access) - a generic
 * create/show/edit/update/destroy set doesn't map onto that domain, so
 * those methods were deliberately left unimplemented and excluded via
 * Route::resource(..., ['except' => [...]]) rather than built out. These
 * should 404, not throw "Call to undefined method" as an uncaught 500.
 */
const deadIntegrationsRoutes = [
  ['GET', '/integrations/create'],
  ['GET', '/integrations/1'],
  ['GET', '/integrations/1/edit'],
];

for (const [method, path] of deadIntegrationsRoutes) {
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
