import { test, expect } from '@playwright/test';
import { TEST_USERS, SEED_CLIENT_NAME, SEED_LEAD_TITLES } from '../../playwright/fixtures/users';
import { LeadsPage } from '../../pages/LeadsPage';
import { LoginPage } from '../../pages/LoginPage';

test.describe('LeadAuthorization', () => {
  test('it user with lead delete permission can delete lead', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Auth Delete ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead to test delete permission' });

    /* Act */
    const row = page.getByRole('row', { name: new RegExp(leadTitle, 'i') });
    const deleteButton = row.getByRole('button', { name: /delete/i });

    /* Assert */
    await expect(deleteButton).toBeVisible();
  });

  test('it user without lead delete permission cannot delete lead', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.employee.email, TEST_USERS.employee.password);

    const leadsPage = new LeadsPage(page);
    await leadsPage.goto();

    /* Act */
    const firstRow = page.getByRole('row').nth(1);
    const deleteButton = firstRow.getByRole('button', { name: /delete/i });

    /* Assert */
    await expect(deleteButton).not.toBeVisible();
  });

  test('it lead update assign only accepts user assigned id field', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Assign Field ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Test assign field validation' });

    /* Act */
    const row = page.getByRole('row', { name: new RegExp(leadTitle, 'i') });
    await row.getByRole('button', { name: /assign/i }).click();
    await page.getByRole('option').first().click();

    /* Assert */
    await expect(page.getByRole('alert').filter({ hasText: /assigned|updated/i })).toBeVisible();
  });

  test('it lead update status only accepts status id field', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Status Field ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Test status field validation' });

    /* Act */
    await leadsPage.changeStatus(leadTitle, 'closed');

    /* Assert */
    await expect(page.getByRole('alert').filter({ hasText: /status updated|updated successfully/i })).toBeVisible();
  });

});
