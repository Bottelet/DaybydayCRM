import { test, expect } from '@playwright/test';
import { TEST_USERS } from '../../fixtures/users';
import { LoginPage } from '../../pages/LoginPage';

// Each test logs in fresh — do not inherit the admin storageState fixture
test.use({ storageState: undefined });

test.describe('Role-based login', () => {
    for (const [role, user] of Object.entries(TEST_USERS)) {
        test(`${role} can log in and reaches dashboard`, async ({ page }) => {
            const loginPage = new LoginPage(page);
            await loginPage.goto();
            await loginPage.login(user.email, user.password);
            await expect(page).toHaveURL(/dashboard|home/i);
        });
    }

    test('logged out user is redirected to /login when accessing dashboard', async ({ page }) => {
        await page.goto('/dashboard');
        await expect(page).toHaveURL(/\/login/);
    });

    test('wrong password shows a login error', async ({ page }) => {
        const loginPage = new LoginPage(page);
        await loginPage.goto();
        await loginPage.login(TEST_USERS.owner.email, 'wrong-password');
        await loginPage.assertLoginErrorVisible();
    });
});
