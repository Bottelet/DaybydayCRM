import { expect, test } from '@playwright/test';
import { TEST_USERS } from '../../playwright/fixtures/users';
import { LoginPage } from '../pages/LoginPage';

test.describe('Auth', () => {
  test.use({ storageState: undefined });

  for (const [role, user] of Object.entries(TEST_USERS)) {
    test(`${role} can log in and sees dashboard`, async ({ page }) => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();
      await loginPage.login(user.email, user.password);
      await expect(page).toHaveURL(/dashboard|home/i);
    });
  }

  test('logged out user is redirected to /login', async ({ page }) => {
    await page.goto('/dashboard');
    await expect(page).toHaveURL(/\/login/);
  });

  test('wrong password shows an error', async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, 'wrong-password');
    await loginPage.assertLoginErrorVisible();
  });
});
