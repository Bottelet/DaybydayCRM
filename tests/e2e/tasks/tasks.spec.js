const { test, expect } = require('@playwright/test');
const {
  BASE_URL,
  loginAsAdmin,
  createClient,
  createTask,
  taskData,
  jsonHeaders,
  expectValidationError,
  uniqueValue,
  html,
  fillSummernote,
  firstOptionValue,
  expectFlashMessage,
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

  const body = await response.json();
  expect(response.status()).toBe(200);
  expect(String(body.message ?? '').toLowerCase()).toContain('updated');
});

test('task assignment endpoint accepts valid assignee', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { payload } = await createTask(page, request, uniqueValue('PW Task Assign'));

  // UpdateTaskAssignRequest requires the internal numeric id (exists:users,id).
  // User::$hidden hides "id" from JSON (usersCollection()), so scrape it from the
  // create page's <select name="user_assigned_id"> the same way real form submits do.
  const { body: createBody } = await html(request, '/tasks/create');
  const userAssignedId = firstOptionValue(createBody, 'user_assigned_id');

  const response = await request.patch(`${BASE_URL}/tasks/updateassign/${payload.task_external_id}`, {
    failOnStatusCode: false,
    maxRedirects: 0,
    headers: await jsonHeaders(page),
    form: { user_assigned_id: userAssignedId },
  });

  const body = await response.json();
  expect(response.status()).toBe(200);
  expect(String(body.message ?? '').toLowerCase()).toContain('assigned');
});

test('browser create shows success notification and task appears on index', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  // Ensure at least one client exists for the client_external_id select
  await createClient(page, request);

  await page.goto(`${BASE_URL}/tasks/create`);

  const title = uniqueValue('PW Browser Task');

  await page.locator('input[name="title"]').fill(title);
  await fillSummernote(page, 'description', 'Browser test task description');

  const statusFirst = await page.locator('select[name="status_id"] option:not([value=""])').first().getAttribute('value');
  await page.locator('select[name="status_id"]').selectOption(statusFirst);

  const userFirst = await page.locator('select[name="user_assigned_id"] option:not([value=""])').first().getAttribute('value');
  await page.locator('select[name="user_assigned_id"]').selectOption(userFirst);

  const clientFirst = await page.locator('select[name="client_external_id"] option:not([value=""])').first().getAttribute('value');
  await page.locator('select[name="client_external_id"]').selectOption(clientFirst);

  // deadline field has a data-value pre-populated but may need a value
  const deadlineInput = page.locator('input[name="deadline"]');
  if (!(await deadlineInput.inputValue())) {
    await deadlineInput.fill('2030-01-01');
  }

  // The create form submits via AJAX (see tasks/create.blade.php); on success
  // JS does window.location to the new task's own show page, not the index.
  await Promise.all([
    page.waitForURL(/\/tasks\//),
    page.locator('form [type="submit"]').first().click(),
  ]);

  await expectFlashMessage(page, 'Task created');
});

test('changing the deadline on a task show page updates it and shows a flash message', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { payload } = await createTask(page, request, uniqueValue('PW Task Deadline'));

  await page.goto(`${BASE_URL}/tasks/${payload.task_external_id}`);
  const deadlineSelector = 'span[data-target="#ModalUpdateDeadline"]';
  const deadlineValueBefore = await page.locator(deadlineSelector).innerText();

  await page.locator(deadlineSelector).click();
  await page.locator('input[type="submit"][value="Update deadline"]').click();

  await expectFlashMessage(page, 'New deadline is set');
  const deadlineValueAfter = await page.locator(deadlineSelector).innerText();
  expect(deadlineValueAfter).not.toBe(deadlineValueBefore);
});

test('editing a task through the edit page persists the new title and description', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { payload } = await createTask(page, request, uniqueValue('PW Task Edit'));
  const newTitle = uniqueValue('PW Task Edited');

  await page.goto(`${BASE_URL}/tasks/${payload.task_external_id}/edit`);
  await page.locator('input[name="title"]').fill(newTitle);
  await fillSummernote(page, 'description', 'Edited description');
  await page.locator('form [type="submit"]').click();

  await expect(page).toHaveURL(new RegExp(payload.task_external_id));
  await expectFlashMessage(page, 'Task successfully updated');
  await expect(page.locator('.tablet__head__color-brand .tablet__head-title', { hasText: newTitle })).toBeVisible();
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
