const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createProject, projectData, jsonHeaders, uniqueValue } = require('../helpers/plain-e2e');

test('project creation appears in the searchable project data response', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const title = uniqueValue('PW Project');
  const { response } = await createProject(page, request, title);

  /* Act */
  const dataResponse = await projectData(request, title);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(response.status()).toBe(200);
  expect(JSON.stringify(dataPayload)).toContain(title);
});

test('project status updates return a redirect instead of a fake ok-only assertion', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { payload, statusId } = await createProject(page, request);
  const externalId = payload.project_external_id;

  /* Act */
  const response = await request.patch(`${BASE_URL}/projects/updatestatus/${externalId}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {
      status_id: statusId,
    },
  });

  /* Assert */
  expect(response.status()).toBe(302);
  expect(response.headers()['location'] ?? '').toBeTruthy();
});
