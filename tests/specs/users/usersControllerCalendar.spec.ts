import { test, expect } from '@playwright/test';
import { TEST_USERS, SEED_CLIENT_NAME, SEED_LEAD_TITLES } from '../../playwright/fixtures/users';
import { LoginPage } from '../../pages/LoginPage';

test.describe('UsersControllerCalendar', () => {
  test('it can get absences within time slot', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    await page.goto('/users');

    /* Act */
    const calendarButton = page.getByRole('button', { name: /calendar|absences/i });
    await calendarButton.click();

    /* Assert */
    await expect(page.locator('.calendar-container, [data-testid="calendar"]')).toBeVisible();
  });

});
