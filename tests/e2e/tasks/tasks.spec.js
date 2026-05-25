const { test, expect } = require('@playwright/test');
const {
  BASE_URL,
  loginAsAdmin,
  createTask,
  taskData,
  jsonHeaders,
  expectValidationError,
  uniqueValue,
  usersCollection,
} = require('../helpers/plain-e2e');

test('guest is redirected from tasks create route', async ({ page }) => {
  await page.goto(`${BASE_URL}/tasks/create`);
  await expect(page).toHaveURL(/login/);
});

test('creating a task makes it searchable in tasks data feed', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const title = uniqueValue('PW Task');

  const { response } = await createTask(page, request, title);
  const dataResponse = await taskData(request, title);
  const payload = await dataResponse.json();

  expect(response.status()).toBe(200);
  expect(dataResponse.status()).toBe(200);
  expect((payload.data ?? []).some((row) => row.title === title)).toBe(true);
});

test('empty task payload returns field validation errors', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const response = await request.post(`${BASE_URL}/tasks`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {},
  });

  await expectValidationError(response, 'title');
});

test('task create form shows alert when submitted empty', async ({ page }) => {
  await loginAsAdmin(page);
  await page.goto(`${BASE_URL}/tasks/create`);
  await page.locator('form button[type="submit"], form input[type="submit"]').first().click();
  await expect(page.locator('.alert.alert-danger, .invalid-feedback').first()).toBeVisible();
});

test('task status update endpoint redirects to task detail', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { payload, statusId } = await createTask(page, request, uniqueValue('PW Task Status'));

  const response = await request.patch(`${BASE_URL}/tasks/updatestatus/${payload.task_external_id}`, {
    failOnStatusCode: false,
    maxRedirects: 0,
    headers: await jsonHeaders(page),
    form: { status_id: statusId },
  });

  expect(response.status()).toBe(302);
  expect(response.headers().location ?? '').toContain(`/tasks/${payload.task_external_id}`);
});

test('task assignment endpoint accepts valid assignee', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { payload } = await createTask(page, request, uniqueValue('PW Task Assign'));
  const users = await usersCollection(request);
  expect(users.length).toBeGreaterThan(0);

  const response = await request.patch(`${BASE_URL}/tasks/updateassign/${payload.task_external_id}`, {
    failOnStatusCode: false,
    maxRedirects: 0,
    headers: await jsonHeaders(page),
    form: { user_assigned_id: users[0].external_id },
  });

  expect(response.status()).toBe(302);
});

test('deleting a task removes it from tasks data feed', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const title = uniqueValue('PW Task Delete');
  const { payload } = await createTask(page, request, title);

  const deleteResponse = await request.delete(`${BASE_URL}/tasks/${payload.task_external_id}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    maxRedirects: 0,
  });

  const dataResponse = await taskData(request, title);
  const dataPayload = await dataResponse.json();

  expect(deleteResponse.status()).toBeLessThan(400);
  expect(dataResponse.status()).toBe(200);
  expect((dataPayload.data ?? []).some((row) => row.title === title)).toBe(false);
});
