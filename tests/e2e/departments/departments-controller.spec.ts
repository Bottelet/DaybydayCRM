import { test, expect } from '@playwright/test';
import { TEST_USERS } from '../../fixtures/users';
import { LoginPage } from '../../pages/LoginPage';
import { DepartmentsPage } from '../../pages/DepartmentsPage';

test.describe('Departments feature behavior', () => {
    test.beforeEach(async ({ page }) => {
        const loginPage = new LoginPage(page);
        await loginPage.goto();
        await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);
    });

    test('can create a department', async ({ page }) => {
        const departmentsPage = new DepartmentsPage(page);
        const name = `PW Department Create ${Date.now()}`;
        await departmentsPage.goto();

        await departmentsPage.create({ name, description: 'Created by Playwright' });

        await departmentsPage.assertRowVisible(name);
    });

    test('can delete a department', async ({ page }) => {
        const departmentsPage = new DepartmentsPage(page);
        const name = `PW Department Delete ${Date.now()}`;
        await departmentsPage.goto();
        await departmentsPage.create({ name, description: 'Delete me' });
        await departmentsPage.assertRowVisible(name);

        await departmentsPage.delete(name);

        await departmentsPage.assertRowNotVisible(name);
    });

    test('cannot delete department when a user is associated with it', async ({ page }) => {
        const departmentsPage = new DepartmentsPage(page);
        const name = `PW Department With User ${Date.now()}`;
        await departmentsPage.goto();
        await departmentsPage.create({ name, description: 'Department to test constraint' });
        await departmentsPage.assertRowVisible(name);

        await page.goto('/users');
        const firstUserRow = page.getByRole('row').nth(1);
        await firstUserRow.getByRole('link', { name: /edit/i }).click();
        await page.getByLabel(/department/i).selectOption({ label: name });
        await page.getByRole('button', { name: /save|update/i }).click();

        await departmentsPage.goto();
        await departmentsPage.delete(name);

        await expect(page.getByText(/cannot|associated|warning/i)).toBeVisible();
        await departmentsPage.assertRowVisible(name);

        // Cleanup
        await page.goto('/users');
        await firstUserRow.getByRole('link', { name: /edit/i }).click();
        await page.getByLabel(/department/i).selectOption({ index: 0 });
        await page.getByRole('button', { name: /save|update/i }).click();
        await departmentsPage.goto();
        await departmentsPage.delete(name);
        await departmentsPage.assertRowNotVisible(name);
    });
});
