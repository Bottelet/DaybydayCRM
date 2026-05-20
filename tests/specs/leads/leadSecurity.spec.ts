import { test, expect } from '@playwright/test';
import { TEST_USERS, SEED_CLIENT_NAME, SEED_LEAD_TITLES } from '../../playwright/fixtures/users';
import { LeadsPage } from '../../pages/LeadsPage';
import { LoginPage } from '../../pages/LoginPage';

test.describe('LeadSecurity', () => {
  test('it authorized user can delete lead', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Sec Delete ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead security delete test' });

    /* Act */
    await leadsPage.delete(leadTitle);

    /* Assert */
    await expect(page.getByRole('alert').filter({ hasText: /deleted successfully|removed/i })).toBeVisible();
  });

  test('it unauthorized user cannot delete lead', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.employee.email, TEST_USERS.employee.password);

    const leadsPage = new LeadsPage(page);
    await leadsPage.goto();

    /* Act */
    const firstRow = page.getByRole('row').nth(1);

    /* Assert */
    await expect(firstRow.getByRole('button', { name: /delete/i })).not.toBeVisible();
  });

  test('it unauthorized user cannot delete lead via json', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.employee.email, TEST_USERS.employee.password);

    const leadsPage = new LeadsPage(page);
    await leadsPage.goto();

    /* Act */
    const response = await page.request.delete('/leads/1');

    /* Assert */
    expect(response.status()).toBe(403);
  });

  test('it updates assign only accepts user assigned id field', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Sec Assign ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead security assign test' });

    /* Act */
    const row = page.getByRole('row', { name: new RegExp(leadTitle, 'i') });
    await row.getByRole('button', { name: /assign/i }).click();
    await page.getByRole('option').first().click();

    /* Assert */
    await expect(page.getByRole('alert').filter({ hasText: /assigned|updated successfully/i })).toBeVisible();
  });

  test('it updates status only accepts status id field', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Sec Status ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead security status test' });

    /* Act */
    await leadsPage.changeStatus(leadTitle, 'closed');

    /* Assert */
    await expect(page.getByRole('alert').filter({ hasText: /status updated|updated successfully/i })).toBeVisible();
  });

  test('it updates status rejects invalid status type', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    const leadsPage = new LeadsPage(page);
    await leadsPage.goto();

    /* Act */
    const response = await page.request.patch('/leads/1/status', {
      data: { status: 'invalid_status_type' }
    });

    /* Assert */
    expect(response.status()).toBe(422);
  });

  test('it updates status rejects nonexistent status id', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    const leadsPage = new LeadsPage(page);
    await leadsPage.goto();

    /* Act */
    const response = await page.request.patch('/leads/1/status', {
      data: { status_id: 99999 }
    });

    /* Assert */
    expect(response.status()).toBe(422);
  });

});
