const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createTask, taskData, jsonHeaders, uniqueValue } = require('../helpers/plain-e2e');

test('task creation appears in the searchable task datatable payload', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const title = uniqueValue('PW Task');
  const { response } = await createTask(page, request, title);

  /* Act */
  const dataResponse = await taskData(request, title);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(response.status()).toBe(200);
  expect(dataResponse.status()).toBe(200);
  const rows = dataPayload.data || [];
  expect(rows.some(row => row.title === title)).toBe(true);
});

test('task status updates return the controller message payload', async ({ page }) => {
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
  expect(response.status()).toBe(200);
  expect(payloadResponse.message).toContain('Task status is updated');
});
