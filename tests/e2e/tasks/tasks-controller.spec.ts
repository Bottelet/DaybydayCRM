import { test, expect } from '@playwright/test';
import { TEST_USERS } from '../../fixtures/users';
import { TasksPage } from '../../pages/TasksPage';
import { LoginPage } from '../../pages/LoginPage';

test.describe('Tasks controller behavior', () => {
    test.beforeEach(async ({ page }) => {
        const loginPage = new LoginPage(page);
        await loginPage.goto();
        await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);
    });

    test('can create task', async ({ page }) => {
        const tasksPage = new TasksPage(page);
        const taskTitle = `PW Task Create ${Date.now()}`;
        await tasksPage.goto();

        await tasksPage.create({ title: taskTitle, description: 'New task' });

        await tasksPage.assertVisible(taskTitle);
    });

    test('validation error shown when submitting empty create form', async ({ page }) => {
        const tasksPage = new TasksPage(page);
        await tasksPage.goto();

        await page.getByRole('button', { name: /new task|create task/i }).click();
        await page.getByRole('button', { name: /save|create/i }).click();

        await expect(page.getByText('The title field is required')).toBeVisible();
    });

    test('can add project on task', async ({ page }) => {
        const tasksPage = new TasksPage(page);
        const taskTitle = `PW Task With Project ${Date.now()}`;
        await tasksPage.goto();

        await tasksPage.create({ title: taskTitle, description: 'Task with project', project: 'Test Project' });

        await tasksPage.assertVisible(taskTitle);
    });

    test('can update task assignee', async ({ page }) => {
        const tasksPage = new TasksPage(page);
        const taskTitle = `PW Task Update Assign ${Date.now()}`;
        await tasksPage.goto();
        await tasksPage.create({ title: taskTitle, description: 'Task to update assignee' });

        const row = page.getByRole('row', { name: new RegExp(taskTitle, 'i') });
        await row.getByRole('button', { name: /assign/i }).click();
        await page.getByRole('option').first().click();

        await expect(page.getByText('Task updated successfully')).toBeVisible();
    });

    test('can update task status', async ({ page }) => {
        const tasksPage = new TasksPage(page);
        const taskTitle = `PW Task Update Status ${Date.now()}`;
        await tasksPage.goto();
        await tasksPage.create({ title: taskTitle, description: 'Task to update status' });

        await tasksPage.close(taskTitle);

        await tasksPage.assertTaskClosed(taskTitle);
    });

    test('can update deadline for task', async ({ page }) => {
        const tasksPage = new TasksPage(page);
        const taskTitle = `PW Task Deadline ${Date.now()}`;
        await tasksPage.goto();
        await tasksPage.create({ title: taskTitle, description: 'Task to update deadline' });

        await tasksPage.edit(taskTitle, { deadline: '2026-12-31' });

        await expect(page.getByText('Task updated successfully')).toBeVisible();
    });

    test('can list tasks', async ({ page }) => {
        const tasksPage = new TasksPage(page);
        const taskTitle = `PW Task List ${Date.now()}`;
        await tasksPage.goto();
        await tasksPage.create({ title: taskTitle, description: 'Task for listing' });

        await tasksPage.goto();

        await tasksPage.assertVisible(taskTitle);
    });
});
