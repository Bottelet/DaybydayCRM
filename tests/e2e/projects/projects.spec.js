const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createProject, projectData, jsonHeaders, uniqueValue, expectValidationError } = require('../helpers/plain-e2e');

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
  expect(dataResponse.status()).toBe(200);
  const rows = Array.isArray(dataPayload?.data) ? dataPayload.data : [];
  expect(rows.some(row => row.title === title)).toBe(true);
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
  const location = response.headers()['location'] ?? '';
  expect(location).toContain(`/projects/${externalId}`);
  expect(location).not.toContain('/login');
});

test('project validation returns a required-field error when title is missing', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;

  /* Act */
  const response = await request.post(`${BASE_URL}/projects`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {},
  });

  /* Assert */
  await expectValidationError(response, 'title');
});
