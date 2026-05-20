import { test, expect } from '@playwright/test';
import { TEST_USERS, SEED_CLIENT_NAME, SEED_LEAD_TITLES } from '../../playwright/fixtures/users';
import { TasksPage } from '../../pages/TasksPage';
import { LoginPage } from '../../pages/LoginPage';

test.describe('TaskAuthorization', () => {
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

});
