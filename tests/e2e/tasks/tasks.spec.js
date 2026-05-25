const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createTask, taskData, jsonHeaders, uniqueValue, expectValidationError, usersCollection } = require('../helpers/plain-e2e');

test('creating a task registers its title in the searchable task datatable payload', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const title = uniqueValue('PW Task');

  /* Act */
  const { response } = await createTask(page, request, title);
  const dataResponse = await taskData(request, title);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(response.status(), 'Task creation should return 200').toBe(200);
  expect(dataResponse.status(), 'Task data feed should return 200').toBe(200);
  const rows = dataPayload.data || [];
  expect(
    rows.some(row => row.title === title),
    `Newly created task "${title}" should appear by exact title in the datatable payload`
  ).toBe(true);
});

test('updating a task status returns the controller confirmation message', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { payload, statusId } = await createTask(page, request);
  const externalId = payload.task_external_id;

  /* Act */
  const response = await request.patch(`${BASE_URL}/tasks/updatestatus/${externalId}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {
      status_id: statusId,
    },
  });
  const payloadResponse = await response.json();

  /* Assert */
  expect(response.status(), 'Task status update should return 200').toBe(200);
  expect(
    String(payloadResponse.message ?? ''),
    'Response body should confirm the status was updated'
  ).toContain('Task status is updated');
});

test('submitting a task form without required fields returns a title field validation error', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;

  /* Act */
  const response = await request.post(`${BASE_URL}/tasks`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {},
  });

  /* Assert */
  await expectValidationError(response, 'title');
});

test('reassigning a task to a new user redirects back without triggering a login redirect', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { payload } = await createTask(page, request, uniqueValue('PW Task Assign'));
  const externalId = payload.task_external_id;
  const users = await usersCollection(request);
  const newAssignee = users[0];

  /* Act */
  const response = await request.patch(`${BASE_URL}/tasks/updateassign/${externalId}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {
      user_assigned_id: newAssignee.external_id,
    },
  });

  /* Assert */
  expect(response.status(), 'Task assignee update should return 302').toBe(302);
  expect(response.headers()['location'] ?? '', 'Redirect must not point to login').not.toContain('/login');
});

test('deleting a task removes it from the task data feed', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const title = uniqueValue('PW Task Delete');
  const { payload } = await createTask(page, request, title);
  const externalId = payload.task_external_id;

  /* Act */
  const deleteResponse = await request.delete(`${BASE_URL}/tasks/${externalId}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    maxRedirects: 0,
  });
  const dataResponse = await taskData(request, title);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(deleteResponse.status(), 'Task deletion should return a redirect').toBeLessThan(400);
  expect(dataResponse.status(), 'Task data feed should return 200 after delete').toBe(200);
  const rows = dataPayload.data || [];
  expect(
    rows.some(row => row.title === title),
    `Deleted task "${title}" must not appear in the data feed`
  ).toBe(false);
});
