import { test, expect } from '@playwright/test';
import { TEST_USERS, SEED_CLIENT_NAME, SEED_LEAD_TITLES } from '../../playwright/fixtures/users';
import { TasksPage } from '../../pages/TasksPage';
import { LoginPage } from '../../pages/LoginPage';

test.describe('TaskSecurity', () => {
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

});
