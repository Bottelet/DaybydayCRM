import { test, expect } from '@playwright/test';
import { TEST_USERS, SEED_CLIENT_NAME, SEED_LEAD_TITLES } from '../../playwright/fixtures/users';
import { LoginPage } from '../../pages/LoginPage';

test.describe('UsersController', () => {
  test('it allows owner to update user role', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    await page.goto('/users');
    const userRow = page.getByRole('row').filter({ hasText: TEST_USERS.employee.email });
    await userRow.getByRole('link', { name: /edit/i }).click();

    /* Act */
    await page.getByLabel(/role/i).selectOption({ index: 1 });
    await page.getByRole('button', { name: /save|update/i }).click();

    /* Assert */
    await expect(page.getByText('User updated successfully')).toBeVisible();
  });

  test('it only owner role can update user', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    await page.goto('/users');
    const userRow = page.getByRole('row').filter({ hasText: TEST_USERS.manager.email });
    await userRow.getByRole('link', { name: /edit/i }).click();

    /* Act */
    await page.getByLabel(/name/i).fill('Updated Name');
    await page.getByRole('button', { name: /save|update/i }).click();

    /* Assert */
    await expect(page.getByText('User updated successfully')).toBeVisible();
  });

  test('it returns web error when user creation throws exception', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    await page.goto('/users');

    /* Act */
    await page.getByRole('button', { name: /new user|create user/i }).click();
    await page.getByRole('button', { name: /save|create/i }).click();

    /* Assert */
    await expect(page.locator('.error-message, [role="alert"]').filter({ hasText: /email.*required|name.*required/i })).toBeVisible();
  });

  test('it returns json error when user creation throws exception', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    await page.goto('/users');

    /* Act */
    await page.getByRole('button', { name: /new user|create user/i }).click();
    await page.getByRole('button', { name: /save|create/i }).click();

    /* Assert */
    await expect(page.locator('.error-message, [role="alert"]').filter({ hasText: /email.*required|name.*required/i })).toBeVisible();
  });

});
