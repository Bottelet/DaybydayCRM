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
  usersCollection,
  html,
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
  await expect(page.locator('form .alert.alert-danger, form .invalid-feedback').first()).toBeVisible();
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
  const users = await usersCollection(request);
  expect(users.length).toBeGreaterThan(0);

  const response = await request.patch(`${BASE_URL}/projects/updateassign/${payload.project_external_id}`, {
    failOnStatusCode: false,
    maxRedirects: 0,
    headers: await jsonHeaders(page),
    form: { user_assigned_id: users[0].external_id },
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
  await page.locator('textarea[name="description"]').fill('Browser test project description');

  const statusFirst = await page.locator('select[name="status_id"] option[value!=""]').first().getAttribute('value');
  await page.locator('select[name="status_id"]').selectOption(statusFirst);

  const userFirst = await page.locator('select[name="user_assigned_id"] option[value!=""]').first().getAttribute('value');
  await page.locator('select[name="user_assigned_id"]').selectOption(userFirst);

  const clientFirst = await page.locator('select[name="client_external_id"] option[value!=""]').first().getAttribute('value');
  await page.locator('select[name="client_external_id"]').selectOption(clientFirst);

  const deadlineInput = page.locator('input[name="deadline"]');
  if (!(await deadlineInput.inputValue())) {
    await deadlineInput.fill('2030-01-01');
  }

  await Promise.all([
    page.waitForURL(`${BASE_URL}/projects`),
    page.locator('form [type="submit"]').first().click(),
  ]);

  // Element UI success toast
  await expect(page.locator('.el-message--success')).toBeVisible();
  await expect(page.locator('.el-message__content')).toContainText('Project created');

  // Project title appears in the DataTables list
  await page.waitForLoadState('networkidle');
  await expect(page.locator('table')).toContainText(title);
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
