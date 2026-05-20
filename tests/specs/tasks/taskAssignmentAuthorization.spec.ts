import { test, expect } from '@playwright/test';
import { TEST_USERS, SEED_CLIENT_NAME, SEED_LEAD_TITLES } from '../../playwright/fixtures/users';
import { TasksPage } from '../../pages/TasksPage';
import { LoginPage } from '../../pages/LoginPage';

test.describe('TaskAssignmentAuthorization', () => {
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

});
