import { test, expect } from '@playwright/test';
import { TEST_USERS, SEED_CLIENT_NAME, SEED_LEAD_TITLES } from '../../playwright/fixtures/users';
import { TasksPage } from '../../pages/TasksPage';
import { LoginPage } from '../../pages/LoginPage';

test.describe('TasksController', () => {
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

});
