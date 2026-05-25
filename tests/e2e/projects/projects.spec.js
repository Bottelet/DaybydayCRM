const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createProject, projectData, jsonHeaders, uniqueValue, expectValidationError, usersCollection } = require('../helpers/plain-e2e');

test('creating a project registers its title in the searchable project data response', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const title = uniqueValue('PW Project');

  /* Act */
  const { response } = await createProject(page, request, title);
  const dataResponse = await projectData(request, title);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(response.status(), 'Project creation should return 200').toBe(200);
  expect(dataResponse.status(), 'Project data feed should return 200').toBe(200);
  const rows = Array.isArray(dataPayload?.data) ? dataPayload.data : [];
  expect(
    rows.some(row => row.title === title),
    `Newly created project "${title}" should appear by exact title in the data response`
  ).toBe(true);
});

test('updating a project status redirects to the project page, not to login', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { payload, statusId } = await createProject(page, request);
  const externalId = payload.project_external_id;

  /* Act */
  const response = await request.patch(`${BASE_URL}/projects/updatestatus/${externalId}`, {
    failOnStatusCode: false,
    maxRedirects: 0,
    headers: await jsonHeaders(page),
    form: {
      status_id: statusId,
    },
  });

  /* Assert */
  expect(response.status(), 'Project status update should return 302').toBe(302);
  const location = response.headers()['location'] ?? '';
  expect(location, 'Redirect should target the project page').toContain(`/projects/${externalId}`);
  expect(location, 'Redirect must not point to login').not.toContain('/login');
});

test('submitting a project form without required fields returns a title field validation error', async ({ page }) => {
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

test('reassigning a project to a new user redirects back without triggering a login redirect', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { payload } = await createProject(page, request, uniqueValue('PW Project Assign'));
  const externalId = payload.project_external_id;
  const users = await usersCollection(request);
  const newAssignee = users[0];

  /* Act */
  const response = await request.patch(`${BASE_URL}/projects/updateassign/${externalId}`, {
    failOnStatusCode: false,
    maxRedirects: 0,
    headers: await jsonHeaders(page),
    form: {
      user_assigned_id: newAssignee.external_id,
    },
  });

  /* Assert */
  expect(response.status(), 'Project assignee update should return 302').toBe(302);
  expect(response.headers()['location'] ?? '', 'Redirect must not point to login').not.toContain('/login');
});

test('deleting a project removes it from the project data feed', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const title = uniqueValue('PW Project Delete');
  const { payload } = await createProject(page, request, title);
  const externalId = payload.project_external_id;

  /* Act */
  const deleteResponse = await request.delete(`${BASE_URL}/projects/${externalId}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    maxRedirects: 0,
  });
  const dataResponse = await projectData(request, title);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(deleteResponse.status(), 'Project deletion should return a redirect').toBeLessThan(400);
  expect(dataResponse.status(), 'Project data feed should return 200 after delete').toBe(200);
  const rows = Array.isArray(dataPayload?.data) ? dataPayload.data : [];
  expect(
    rows.some(row => row.title === title),
    `Deleted project "${title}" must not appear in the data feed`
  ).toBe(false);
});
