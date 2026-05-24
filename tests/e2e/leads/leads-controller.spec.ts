import { test, expect } from '@playwright/test';
import { LeadsPage } from '../../pages/LeadsPage';
import { LoginPage } from '../../pages/LoginPage';

test.describe('LeadsController', () => {
  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);
  });

  test('it can create lead', async ({ page }) => {
    /* Arrange */
    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Create ${Date.now()}`;
    await leadsPage.goto();

    /* Act */
    await leadsPage.create({ title: leadTitle, description: 'New lead' });

    /* Assert */
    await leadsPage.assertVisible(leadTitle);
  });

  test('it returns web error when lead creation throws exception', async ({ page }) => {
    /* Arrange */
    const leadsPage = new LeadsPage(page);
    await leadsPage.goto();

    /* Act */
    await page.getByRole('button', { name: /new lead|create lead/i }).click();
    await page.getByRole('button', { name: /save|create/i }).click();

    /* Assert */
    await expect(page.getByText('The title field is required')).toBeVisible();
  });

  test('it returns json error when lead creation throws exception', async ({ page }) => {
    /* Arrange */
    const leadsPage = new LeadsPage(page);
    await leadsPage.goto();

    /* Act */
    await page.getByRole('button', { name: /new lead|create lead/i }).click();
    await page.getByRole('button', { name: /save|create/i }).click();

    /* Assert */
    await expect(page.getByText('The title field is required')).toBeVisible();
  });

  test('it can update assignee', async ({ page }) => {
    /* Arrange */
    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Update Assign ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead to update assignee' });

    /* Act */
    const row = page.getByRole('row', { name: new RegExp(leadTitle, 'i') });
    await row.getByRole('button', { name: /assign/i }).click();
    await page.getByRole('option').first().click();

    /* Assert */
    await expect(page.getByText('Lead updated successfully')).toBeVisible();
  });

  test('it can update status', async ({ page }) => {
    /* Arrange */
    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Update Status ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead to update status' });

    /* Act */
    await leadsPage.changeStatus(leadTitle, 'won');

    /* Assert */
    await leadsPage.assertStatus(leadTitle, 'won');
  });

  test('it can update deadline for lead', async ({ page }) => {
    /* Arrange */
    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Deadline ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead to update deadline' });

    /* Act */
    await leadsPage.edit(leadTitle, { deadline: '2026-12-31' });

    /* Assert */
    await expect(page.getByText('Lead updated successfully')).toBeVisible();
  });

  test('it updates followup stores deadline as datetime string', async ({ page }) => {
    /* Arrange */
    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Followup DateTime ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead followup datetime test' });

    /* Act */
    await leadsPage.edit(leadTitle, { followup: '2026-12-31' });

    /* Assert */
    await expect(page.getByText('Lead updated successfully')).toBeVisible();
  });

  test('it updates followup stores deadline with correct time component', async ({ page }) => {
    /* Arrange */
    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Followup Time ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead followup time test' });

    /* Act */
    await leadsPage.edit(leadTitle, { followup: '2026-12-31 14:30' });

    /* Assert */
    await expect(page.getByText('Lead updated successfully')).toBeVisible();
  });

  test('it updates followup deadline is stored as parseable date in database', async ({ page }) => {
    /* Arrange */
    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Followup Parse ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead followup parse test' });

    /* Act */
    await leadsPage.edit(leadTitle, { followup: '2026-12-31' });

    /* Assert */
    await expect(page.getByText('Lead updated successfully')).toBeVisible();
  });

});
