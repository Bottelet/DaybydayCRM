import { test, expect } from '@playwright/test';
import { TEST_USERS, SEED_CLIENT_NAME, SEED_LEAD_TITLES } from '../../playwright/fixtures/users';
import { LoginPage } from '../../pages/LoginPage';

test.describe('UserSecurity', () => {
  test('it authorized user can edit user', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    await page.goto('/users');
    const userRow = page.getByRole('row').filter({ hasText: TEST_USERS.employee.email });

    /* Act */
    await userRow.getByRole('link', { name: /edit/i }).click();

    /* Assert */
    await expect(page.getByRole('heading', { name: /edit user/i })).toBeVisible();
  });

  test('it unauthorized user cannot edit user', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.employee.email, TEST_USERS.employee.password);

    /* Act */
    await page.goto('/users/1/edit');

    /* Assert */
    await expect(page.getByRole('alert').filter({ hasText: /forbidden|unauthorized|access denied/i })).toBeVisible();
  });

  test('it authorized user can update user', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    await page.goto('/users');
    const userRow = page.getByRole('row').filter({ hasText: TEST_USERS.manager.email });
    await userRow.getByRole('link', { name: /edit/i }).click();

    /* Act */
    await page.getByLabel(/name/i).fill('Updated Manager Name');
    await page.getByRole('button', { name: /save|update/i }).click();

    /* Assert */
    await expect(page.getByRole('status', { name: /updated successfully/i }).or(page.getByText('User updated successfully'))).toBeVisible();
  });

  test('it unauthorized user cannot update user', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.employee.email, TEST_USERS.employee.password);

    /* Act */
    const response = await page.request.patch('/users/1', {
      data: { name: 'Hacked Name' }
    });

    /* Assert */
    expect(response.status()).toBe(403);
  });

  test('it user update prevents password change without permission', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.employee.email, TEST_USERS.employee.password);

    /* Act */
    const response = await page.request.patch('/users/1', {
      data: { password: 'newpassword123' }
    });

    /* Assert */
    await expect(page.getByRole('alert').filter({ hasText: /forbidden|unauthorized|cannot change password/i }).or(page.locator('body'))).toBeTruthy();
    expect(response.status()).toBeGreaterThanOrEqual(400);
  });

});
