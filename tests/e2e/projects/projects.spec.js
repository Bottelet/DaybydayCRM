const { test, expect } = require('@playwright/test');
const {
  BASE_URL,
  loginAsAdmin,
  createClient,
  createProject,
  projectData,
  jsonHeaders,
  expectValidationError,
  uniqueValue,
  html,
  fillSummernote,
  firstOptionValue,
  expectFlashMessage,
} = require('../helpers/plain-e2e');

test('guest is redirected from projects create route', async ({ page }) => {
  await page.goto(`${BASE_URL}/projects/create`);
  await expect(page).toHaveURL(/login/);
});

test('creating a project makes it searchable in projects data feed', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const title = uniqueValue('PW Project');

  const { response } = await createProject(page, request, title);
  const dataResponse = await projectData(request, title);
  const payload = await dataResponse.json();

  expect(response.status()).toBe(200);
  expect(dataResponse.status()).toBe(200);
  expect((payload.data ?? []).some((row) => row.title === title)).toBe(true);
});

test('empty project payload returns field validation errors', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const response = await request.post(`${BASE_URL}/projects`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {},
  });

  await expectValidationError(response, 'title');
});

test('project create form shows alert when submitted empty', async ({ page }) => {
  await loginAsAdmin(page);
  await page.goto(`${BASE_URL}/projects/create`);
  await page.locator('form button[type="submit"], form input[type="submit"]').first().click();
  await expect(page.locator('.alert.alert-danger, .invalid-feedback').first()).toBeVisible();
});

test('project status update endpoint returns ajax redirect header', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { payload, statusId } = await createProject(page, request, uniqueValue('PW Project Status'));
  const projectPath = `${BASE_URL}/projects/${payload.project_external_id}`;

  const { body } = await html(request, '/projects/create');
  const statusPattern = /<select[^>]*name=["']status_id["'][^>]*>([\s\S]*?)<\/select>/i;
  const statusSection = body.match(statusPattern)?.[1] ?? '';
  const allStatusIds = [...statusSection.matchAll(/<option[^>]*value=["']([^"']*)["'][^>]*>/gi)]
    .map(m => String(m[1] ?? '').trim())
    .filter(Boolean);
  const newStatusId = allStatusIds.find((id) => id !== statusId);
  expect(newStatusId).toBeTruthy();

  const response = await request.patch(`${BASE_URL}/projects/updatestatus/${payload.project_external_id}`, {
    failOnStatusCode: false,
    maxRedirects: 0,
    headers: await jsonHeaders(page, { Referer: projectPath }),
    form: { status_id: newStatusId },
  });

  expect(response.status()).toBe(302);
  expect(response.headers()['x-redirect'] ?? '').toContain(projectPath);

  const { body: projectPageBody } = await html(request, `/projects/${payload.project_external_id}`);
  expect(projectPageBody).toContain(String(newStatusId));
});

test('project assignment endpoint accepts valid assignee', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { payload } = await createProject(page, request, uniqueValue('PW Project Assign'));

  // UpdateProjectAssignRequest requires the internal numeric id (exists:users,id).
  // User::$hidden hides "id" from JSON (usersCollection()), so scrape it from the
  // create page's <select name="user_assigned_id"> the same way real form submits do.
  const { body: createBody } = await html(request, '/projects/create');
  const userAssignedId = firstOptionValue(createBody, 'user_assigned_id');

  const response = await request.patch(`${BASE_URL}/projects/updateassign/${payload.project_external_id}`, {
    failOnStatusCode: false,
    maxRedirects: 0,
    headers: await jsonHeaders(page),
    form: { user_assigned_id: userAssignedId },
  });

  expect(response.status()).toBe(302);
});

test('browser create shows success notification and project appears on index', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  // Ensure at least one client exists for the client_external_id select
  await createClient(page, request);

  await page.goto(`${BASE_URL}/projects/create`);

  const title = uniqueValue('PW Browser Project');

  await page.locator('input[name="title"]').fill(title);
  await fillSummernote(page, 'description', 'Browser test project description');

  const statusFirst = await page.locator('select[name="status_id"] option:not([value=""])').first().getAttribute('value');
  await page.locator('select[name="status_id"]').selectOption(statusFirst);

  const userFirst = await page.locator('select[name="user_assigned_id"] option:not([value=""])').first().getAttribute('value');
  await page.locator('select[name="user_assigned_id"]').selectOption(userFirst);

  const clientFirst = await page.locator('select[name="client_external_id"] option:not([value=""])').first().getAttribute('value');
  await page.locator('select[name="client_external_id"]').selectOption(clientFirst);

  const deadlineInput = page.locator('input[name="deadline"]');
  if (!(await deadlineInput.inputValue())) {
    await deadlineInput.fill('2030-01-01');
  }

  // The create form submits via AJAX (see tasks/create.blade.php, shared JS
  // pattern); on success JS does window.location to the new project's own
  // show page, not the index.
  await Promise.all([
    page.waitForURL(/\/projects\//),
    page.locator('form [type="submit"]').first().click(),
  ]);

  await expectFlashMessage(page, 'Project created');
});

test('changing the deadline on a project show page updates it and shows a flash message', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { payload } = await createProject(page, request, uniqueValue('PW Project Deadline'));

  await page.goto(`${BASE_URL}/projects/${payload.project_external_id}`);
  const deadlineSelector = 'span[data-target="#ModalUpdateDeadline"]';
  const deadlineValueBefore = await page.locator(deadlineSelector).innerText();

  await page.locator(deadlineSelector).click();
  await page.locator('input[type="submit"][value="Update deadline"]').click();

  await expectFlashMessage(page, 'New deadline is set');
  const deadlineValueAfter = await page.locator(deadlineSelector).innerText();
  expect(deadlineValueAfter).not.toBe(deadlineValueBefore);
});

test('deleting a project removes it from projects data feed', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const title = uniqueValue('PW Project Delete');
  const { payload } = await createProject(page, request, title);

  const deleteResponse = await request.delete(`${BASE_URL}/projects/${payload.project_external_id}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    maxRedirects: 0,
  });

  const dataResponse = await projectData(request, title);
  const dataPayload = await dataResponse.json();

  expect(deleteResponse.status()).toBeLessThan(400);
  expect(dataResponse.status()).toBe(200);
  expect((dataPayload.data ?? []).some((row) => row.title === title)).toBe(false);
});

test('editing a project through the edit page persists the new title and description', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { payload } = await createProject(page, request, uniqueValue('PW Project Edit'));
  const newTitle = uniqueValue('PW Project Edited');

  await page.goto(`${BASE_URL}/projects/${payload.project_external_id}/edit`);
  await page.locator('input[name="title"]').fill(newTitle);
  await fillSummernote(page, 'description', 'Edited description');
  await page.locator('form [type="submit"]').click();

  await expect(page).toHaveURL(new RegExp(payload.project_external_id));
  await expectFlashMessage(page, 'Project successfully updated');
  await expect(page.locator('.tablet__head__color-brand .tablet__head-title', { hasText: newTitle })).toBeVisible();
});
