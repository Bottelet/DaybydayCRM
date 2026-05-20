import { test, expect } from '@playwright/test';
import { TEST_USERS, SEED_CLIENT_NAME, SEED_LEAD_TITLES } from '../../playwright/fixtures/users';
import { LoginPage } from '../../pages/LoginPage';

test.describe('UserRestore', () => {
  test('it user can be restored after soft delete', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    await page.goto('/users');
    const userRow = page.getByRole('row').filter({ hasText: TEST_USERS.manager.email });
    await userRow.getByRole('button', { name: /delete/i }).click();
    await page.getByRole('button', { name: /confirm|delete/i }).click();

    /* Act */
    await page.getByRole('button', { name: /show deleted|trash|archived/i }).click();
    const deletedUserRow = page.getByRole('row').filter({ hasText: TEST_USERS.manager.email });
    await deletedUserRow.getByRole('button', { name: /restore/i }).click();

    /* Assert */
    await expect(page.getByRole('alert').filter({ hasText: /restored successfully|user restored/i })).toBeVisible();
  });

});
