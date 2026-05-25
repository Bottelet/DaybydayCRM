const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createTask, taskData, jsonHeaders, uniqueValue, expectValidationError, usersCollection  } = require('../helpers/plain-e2e');
const {SEED_CLIENT_NAME} = require("../../fixtures/users");

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
    maxRedirects: 0,
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

test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);
});

test('it can create task', async ({ page }) => {
    /* Arrange */
    const tasksPage = new TasksPage(page);
    const taskTitle = `PW Task Create ${Date.now()}`;
    await tasksPage.goto();

    /* Act */
    await tasksPage.create({ title: taskTitle, description: 'New task' });

    /* Assert */
    await tasksPage.assertVisible(taskTitle);
});

test('it returns web error when task creation throws exception', async ({ page }) => {
    /* Arrange */
    const tasksPage = new TasksPage(page);
    await tasksPage.goto();

    /* Act */
    await page.getByRole('button', { name: /new task|create task/i }).click();
    await page.getByRole('button', { name: /save|create/i }).click();

    /* Assert */
    await expect(page.getByText('The title field is required')).toBeVisible();
});

test('it returns json error when task creation throws exception', async ({ page }) => {
    /* Arrange */
    const tasksPage = new TasksPage(page);
    await tasksPage.goto();

    /* Act */
    await page.getByRole('button', { name: /new task|create task/i }).click();
    await page.getByRole('button', { name: /save|create/i }).click();

    /* Assert */
    await expect(page.getByText('The title field is required')).toBeVisible();
});

test('it can add project on task', async ({ page }) => {
    /* Arrange */
    const tasksPage = new TasksPage(page);
    const taskTitle = `PW Task with Project ${Date.now()}`;
    await tasksPage.goto();

    /* Act */
    await tasksPage.create({ title: taskTitle, description: 'Task with project', project: 'Test Project' });

    /* Assert */
    await tasksPage.assertVisible(taskTitle);
});

test('it can update assignee', async ({ page }) => {
    /* Arrange */
    const tasksPage = new TasksPage(page);
    const taskTitle = `PW Task Update Assign ${Date.now()}`;
    await tasksPage.goto();
    await tasksPage.create({ title: taskTitle, description: 'Task to update assignee' });

    /* Act */
    const row = page.getByRole('row', { name: new RegExp(taskTitle, 'i') });
    await row.getByRole('button', { name: /assign/i }).click();
    await page.getByRole('option').first().click();

    /* Assert */
    await expect(page.getByText('Task updated successfully')).toBeVisible();
});

test('it can update status', async ({ page }) => {
    /* Arrange */
    const tasksPage = new TasksPage(page);
    const taskTitle = `PW Task Update Status ${Date.now()}`;
    await tasksPage.goto();
    await tasksPage.create({ title: taskTitle, description: 'Task to update status' });

    /* Act */
    await tasksPage.close(taskTitle);

    /* Assert */
    await tasksPage.assertTaskClosed(taskTitle);
});

test('it can update deadline for task', async ({ page }) => {
    /* Arrange */
    const tasksPage = new TasksPage(page);
    const taskTitle = `PW Task Deadline ${Date.now()}`;
    await tasksPage.goto();
    await tasksPage.create({ title: taskTitle, description: 'Task to update deadline' });

    /* Act */
    await tasksPage.edit(taskTitle, { deadline: '2026-12-31' });

    /* Assert */
    await expect(page.getByText('Task updated successfully')).toBeVisible();
});

test('it can list tasks', async ({ page }) => {
    /* Arrange */
    const tasksPage = new TasksPage(page);
    const taskTitle = `PW Task List ${Date.now()}`;
    await tasksPage.goto();
    await tasksPage.create({ title: taskTitle, description: 'Task for listing' });

    /* Act */
    await tasksPage.goto();

    /* Assert */
    await tasksPage.assertVisible(taskTitle);
});

test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);
});

test('it can create task', async ({ page }) => {
    /* Arrange */
    const tasksPage = new TasksPage(page);
    const taskTitle = `PW Task Create ${Date.now()}`;
    await tasksPage.goto();

    /* Act */
    await tasksPage.create({ title: taskTitle, description: 'New task' });

    /* Assert */
    await tasksPage.assertVisible(taskTitle);
});

test('it returns web error when task creation throws exception', async ({ page }) => {
    /* Arrange */
    const tasksPage = new TasksPage(page);
    await tasksPage.goto();

    /* Act */
    await page.getByRole('button', { name: /new task|create task/i }).click();
    await page.getByRole('button', { name: /save|create/i }).click();

    /* Assert */
    await expect(page.getByText('The title field is required')).toBeVisible();
});

test('it returns json error when task creation throws exception', async ({ page }) => {
    /* Arrange */
    const tasksPage = new TasksPage(page);
    await tasksPage.goto();

    /* Act */
    await page.getByRole('button', { name: /new task|create task/i }).click();
    await page.getByRole('button', { name: /save|create/i }).click();

    /* Assert */
    await expect(page.getByText('The title field is required')).toBeVisible();
});

test('it can add project on task', async ({ page }) => {
    /* Arrange */
    const tasksPage = new TasksPage(page);
    const taskTitle = `PW Task with Project ${Date.now()}`;
    await tasksPage.goto();

    /* Act */
    await tasksPage.create({ title: taskTitle, description: 'Task with project', project: 'Test Project' });

    /* Assert */
    await tasksPage.assertVisible(taskTitle);
});

test('it can update assignee', async ({ page }) => {
    /* Arrange */
    const tasksPage = new TasksPage(page);
    const taskTitle = `PW Task Update Assign ${Date.now()}`;
    await tasksPage.goto();
    await tasksPage.create({ title: taskTitle, description: 'Task to update assignee' });

    /* Act */
    const row = page.getByRole('row', { name: new RegExp(taskTitle, 'i') });
    await row.getByRole('button', { name: /assign/i }).click();
    await page.getByRole('option').first().click();

    /* Assert */
    await expect(page.getByText('Task updated successfully')).toBeVisible();
});

test('it can update status', async ({ page }) => {
    /* Arrange */
    const tasksPage = new TasksPage(page);
    const taskTitle = `PW Task Update Status ${Date.now()}`;
    await tasksPage.goto();
    await tasksPage.create({ title: taskTitle, description: 'Task to update status' });

    /* Act */
    await tasksPage.close(taskTitle);

    /* Assert */
    await tasksPage.assertTaskClosed(taskTitle);
});

test('it can update deadline for task', async ({ page }) => {
    /* Arrange */
    const tasksPage = new TasksPage(page);
    const taskTitle = `PW Task Deadline ${Date.now()}`;
    await tasksPage.goto();
    await tasksPage.create({ title: taskTitle, description: 'Task to update deadline' });

    /* Act */
    await tasksPage.edit(taskTitle, { deadline: '2026-12-31' });

    /* Assert */
    await expect(page.getByText('Task updated successfully')).toBeVisible();
});

test('it can list tasks', async ({ page }) => {
    /* Arrange */
    const tasksPage = new TasksPage(page);
    const taskTitle = `PW Task List ${Date.now()}`;
    await tasksPage.goto();
    await tasksPage.create({ title: taskTitle, description: 'Task for listing' });

    /* Act */
    await tasksPage.goto();

    /* Assert */
    await tasksPage.assertVisible(taskTitle);
});

test('can create a task assigned to the seed client', async ({ page }) => {
    test.skip(!['owner', 'employee'].includes(test.info().project.name));
    const p = new TasksPage(page);
    const title = `PW Task ${Date.now()}`;
    await p.create({ title, client: SEED_CLIENT_NAME });
    await p.assertVisible(title);
});

test('can edit a task', async ({ page }) => {
    test.skip(!['owner', 'employee'].includes(test.info().project.name));
    const p = new TasksPage(page);
    const title = `PW Task Edit ${Date.now()}`;
    const updated = `${title} Updated`;
    await p.create({ title, client: SEED_CLIENT_NAME });
    await p.edit(title, { title: updated });
    await p.assertVisible(updated);
});

test('can mark a task as closed', async ({ page }) => {
    test.skip(!['owner', 'employee'].includes(test.info().project.name));
    const p = new TasksPage(page);
    const title = `PW Task Close ${Date.now()}`;
    await p.create({ title, client: SEED_CLIENT_NAME });
    await p.close(title);
    await p.assertTaskClosed(title);
});

test('can delete a task created in this test', async ({ page }) => {
    test.skip(!['owner', 'employee'].includes(test.info().project.name));
    const p = new TasksPage(page);
    const title = `PW Task Delete ${Date.now()}`;
    await p.create({ title, client: SEED_CLIENT_NAME });
    await p.delete(title);
    await p.assertNotVisible(title);
});

test('it authorized user can delete task', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    const tasksPage = new TasksPage(page);
    const taskTitle = `PW Task Sec Delete ${Date.now()}`;
    await tasksPage.goto();
    await tasksPage.create({ title: taskTitle, description: 'Task security delete test' });

    /* Act */
    await tasksPage.delete(taskTitle);

    /* Assert */
    await tasksPage.assertNotVisible(taskTitle);
});

test('it unauthorized user cannot delete task', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.employee.email, TEST_USERS.employee.password);

    const tasksPage = new TasksPage(page);
    await tasksPage.goto();

    /* Act */
    const firstRow = page.getByRole('row').nth(1);

    /* Assert */
    await expect(firstRow.getByRole('button', { name: /delete/i })).not.toBeVisible();
});

test('it updates status only accepts status id field', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    const tasksPage = new TasksPage(page);
    const taskTitle = `PW Task Sec Status ${Date.now()}`;
    await tasksPage.goto();
    await tasksPage.create({ title: taskTitle, description: 'Task security status test' });

    /* Act */
    await tasksPage.close(taskTitle);

    /* Assert */
    await tasksPage.assertTaskClosed(taskTitle);
});

test('it updates status with invalid status external id returns error', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    const tasksPage = new TasksPage(page);
    await tasksPage.goto();

    /* Act */
    const response = await page.request.patch('/tasks/1/status', {
        data: { external_id: 'invalid_external_id' }
    });

    /* Assert */
    expect(response.status()).toBe(422);
});

test('it updates status via ajax with valid external id', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    const tasksPage = new TasksPage(page);
    const taskTitle = `PW Task Ajax Status ${Date.now()}`;
    await tasksPage.goto();
    await tasksPage.create({ title: taskTitle, description: 'Task ajax status test' });

    /* Act */
    await tasksPage.close(taskTitle);

    /* Assert */
    await tasksPage.assertTaskClosed(taskTitle);
});

test('it updates status rejects invalid status type', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    const tasksPage = new TasksPage(page);
    await tasksPage.goto();

    /* Act */
    const response = await page.request.patch('/tasks/1/status', {
        data: { status: 'invalid_status_type' }
    });

    /* Assert */
    expect(response.status()).toBe(422);
});

test('it updates status rejects nonexistent status id', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    const tasksPage = new TasksPage(page);
    await tasksPage.goto();

    /* Act */
    const response = await page.request.patch('/tasks/1/status', {
        data: { status_id: 99999 }
    });

    /* Assert */
    expect(response.status()).toBe(422);
});

test('it user with task delete permission can delete task', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    const tasksPage = new TasksPage(page);
    const taskTitle = `PW Task Auth Delete ${Date.now()}`;
    await tasksPage.goto();
    await tasksPage.create({ title: taskTitle, description: 'Task to test delete permission' });

    /* Act */
    const row = page.getByRole('row', { name: new RegExp(taskTitle, 'i') });
    const deleteButton = row.getByRole('button', { name: /delete/i });

    /* Assert */
    await expect(deleteButton).toBeVisible();
});

test('it user without task delete permission cannot delete task', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.employee.email, TEST_USERS.employee.password);

    const tasksPage = new TasksPage(page);
    await tasksPage.goto();

    /* Act */
    const firstRow = page.getByRole('row').nth(1);
    const deleteButton = firstRow.getByRole('button', { name: /delete/i });

    /* Assert */
    await expect(deleteButton).not.toBeVisible();
});

test('it user with update project permission can update task project', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    const tasksPage = new TasksPage(page);
    const taskTitle = `PW Task Update Project ${Date.now()}`;
    await tasksPage.goto();
    await tasksPage.create({ title: taskTitle, description: 'Task to test project update' });

    /* Act */
    await tasksPage.edit(taskTitle, { project: 'Test Project' });

    /* Assert */
    await expect(page.getByRole('alert').filter({ hasText: /updated successfully/i })).toBeVisible();
});

test('it user without update project permission cannot update task project', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.employee.email, TEST_USERS.employee.password);

    const tasksPage = new TasksPage(page);
    await tasksPage.goto();

    /* Act */
    const firstRow = page.getByRole('row').nth(1);
    await firstRow.getByRole('link', { name: /edit/i }).click();

    /* Assert */
    await expect(page.getByLabel(/project/i)).toBeDisabled();
});

test('it task update status only accepts status id field', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    const tasksPage = new TasksPage(page);
    const taskTitle = `PW Task Status Field ${Date.now()}`;
    await tasksPage.goto();
    await tasksPage.create({ title: taskTitle, description: 'Task to test status field' });

    /* Act */
    await tasksPage.close(taskTitle);

    /* Assert */
    await tasksPage.assertTaskClosed(taskTitle);
});

test('it authorized user can reassign task', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    const tasksPage = new TasksPage(page);
    const taskTitle = `PW Task Reassign ${Date.now()}`;
    await tasksPage.goto();
    await tasksPage.create({ title: taskTitle, description: 'Task to reassign' });

    /* Act */
    const row = page.getByRole('row', { name: new RegExp(taskTitle, 'i') });
    await row.getByRole('button', { name: /assign/i }).click();
    await page.getByRole('option').first().click();

    /* Assert */
    await expect(page.getByRole('alert').filter({ hasText: /assigned|updated successfully/i })).toBeVisible();
});

test('it unauthorized user cannot reassign task', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.employee.email, TEST_USERS.employee.password);

    const tasksPage = new TasksPage(page);
    await tasksPage.goto();

    /* Act */
    const firstRow = page.getByRole('row').nth(1);
    await firstRow.getByRole('button', { name: /assign/i }).click();

    /* Assert */
    await expect(page.getByRole('alert').filter({ hasText: /forbidden|unauthorized|permission denied/i })).toBeVisible();
});
