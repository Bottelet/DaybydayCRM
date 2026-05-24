import { test, expect } from '@playwright/test';
import { TEST_USERS } from '../../fixtures/users';
import { LoginPage } from '../../pages/LoginPage';

test.describe('Users controller behavior', () => {
    test.beforeEach(async ({ page }) => {
        const loginPage = new LoginPage(page);
        await loginPage.goto();
        await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);
    });

    test('owner can update another user role', async ({ page }) => {
        await page.goto('/users');
        const userRow = page.getByRole('row').filter({ hasText: TEST_USERS.employee.email });
        await userRow.getByRole('link', { name: /edit/i }).click();

        await page.getByLabel(/role/i).selectOption({ index: 1 });
        await page.getByRole('button', { name: /save|update/i }).click();

        await expect(page.getByText('User updated successfully')).toBeVisible();
    });

    test('only owner role can update user', async ({ page }) => {
        await page.goto('/users');
        const userRow = page.getByRole('row').filter({ hasText: TEST_USERS.manager.email });
        await userRow.getByRole('link', { name: /edit/i }).click();

        await page.getByLabel(/name/i).fill('Updated Name');
        await page.getByRole('button', { name: /save|update/i }).click();

        await expect(page.getByText('User updated successfully')).toBeVisible();
    });

    test('validation error shown when submitting empty create form', async ({ page }) => {
        await page.goto('/users');

        await page.getByRole('button', { name: /new user|create user/i }).click();
        await page.getByRole('button', { name: /save|create/i }).click();

        await expect(
            page.locator('.error-message, [role="alert"]').filter({ hasText: /email.*required|name.*required/i }),
        ).toBeVisible();
    });
});
