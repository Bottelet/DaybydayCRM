import { test, expect } from '@playwright/test';
import { TEST_USERS, SEED_CLIENT_NAME, SEED_LEAD_TITLES } from '../../playwright/fixtures/users';
import { LoginPage } from '../../pages/LoginPage';

test.describe('UserAuthorization', () => {
  test('it user with user delete permission can delete user', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    await page.goto('/users');

    /* Act */
    const userRow = page.getByRole('row').filter({ hasText: TEST_USERS.employee.email });
    const deleteButton = userRow.getByRole('button', { name: /delete/i });

    /* Assert */
    await expect(deleteButton).toBeVisible();
  });

  test('it user without user delete permission cannot delete user', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.employee.email, TEST_USERS.employee.password);

    await page.goto('/users');

    /* Act */
    const firstRow = page.getByRole('row').nth(1);
    const deleteButton = firstRow.getByRole('button', { name: /delete/i });

    /* Assert */
    await expect(deleteButton).not.toBeVisible();
  });

  test('it owner user cannot be deleted even with permission', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    await page.goto('/users');

    /* Act */
    const ownerRow = page.getByRole('row').filter({ hasText: TEST_USERS.owner.email });
    await ownerRow.getByRole('button', { name: /delete/i }).click();
    await page.getByRole('button', { name: /confirm|delete/i }).click();

    /* Assert */
    await expect(page.getByRole('alert').filter({ hasText: /cannot delete owner|owner cannot be deleted/i })).toBeVisible();
  });

});
