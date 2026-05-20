import { test, expect } from '@playwright/test';
import { TEST_USERS, SEED_CLIENT_NAME, SEED_LEAD_TITLES } from '../../playwright/fixtures/users';
import { LeadsPage } from '../../pages/LeadsPage';
import { LoginPage } from '../../pages/LoginPage';

test.describe('LeadAssignmentAuthorization', () => {
  test('it authorized user can reassign lead', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Reassign ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead to reassign' });

    /* Act */
    const row = page.getByRole('row', { name: new RegExp(leadTitle, 'i') });
    await row.getByRole('button', { name: /assign/i }).click();
    await page.getByRole('option').first().click();

    /* Assert */
    await expect(page.getByRole('alert').filter({ hasText: /assigned|updated successfully/i })).toBeVisible();
  });

  test('it unauthorized user cannot reassign lead', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.employee.email, TEST_USERS.employee.password);

    const leadsPage = new LeadsPage(page);
    await leadsPage.goto();

    /* Act */
    const firstRow = page.getByRole('row').nth(1);
    await firstRow.getByRole('button', { name: /assign/i }).click();

    /* Assert */
    await expect(page.getByRole('alert').filter({ hasText: /forbidden|unauthorized|permission denied/i })).toBeVisible();
  });

});
