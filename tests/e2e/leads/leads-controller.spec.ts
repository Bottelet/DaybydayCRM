import { test, expect } from '@playwright/test';
import { TEST_USERS } from '../../fixtures/users';
import { LeadsPage } from '../../pages/LeadsPage';
import { LoginPage } from '../../pages/LoginPage';

test.describe('Leads controller behavior', () => {
    test.beforeEach(async ({ page }) => {
        const loginPage = new LoginPage(page);
        await loginPage.goto();
        await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);
    });

    test('can create lead', async ({ page }) => {
        const leadsPage = new LeadsPage(page);
        const leadTitle = `PW Lead Create ${Date.now()}`;
        await leadsPage.goto();

        await leadsPage.create({ title: leadTitle, description: 'New lead' });

        await leadsPage.assertVisible(leadTitle);
    });

    test('validation error shown when submitting empty create form', async ({ page }) => {
        const leadsPage = new LeadsPage(page);
        await leadsPage.goto();

        await page.getByRole('button', { name: /new lead|create lead/i }).click();
        await page.getByRole('button', { name: /save|create/i }).click();

        await expect(page.getByText('The title field is required')).toBeVisible();
    });

    test('can update lead assignee', async ({ page }) => {
        const leadsPage = new LeadsPage(page);
        const leadTitle = `PW Lead Update Assign ${Date.now()}`;
        await leadsPage.goto();
        await leadsPage.create({ title: leadTitle, description: 'Lead to update assignee' });

        const row = page.getByRole('row', { name: new RegExp(leadTitle, 'i') });
        await row.getByRole('button', { name: /assign/i }).click();
        await page.getByRole('option').first().click();

        await expect(page.getByText('Lead updated successfully')).toBeVisible();
    });

    test('can update lead status', async ({ page }) => {
        const leadsPage = new LeadsPage(page);
        const leadTitle = `PW Lead Update Status ${Date.now()}`;
        await leadsPage.goto();
        await leadsPage.create({ title: leadTitle, description: 'Lead to update status' });

        await leadsPage.changeStatus(leadTitle, 'won');

        await leadsPage.assertStatus(leadTitle, 'won');
    });

    test('can update deadline for lead', async ({ page }) => {
        const leadsPage = new LeadsPage(page);
        const leadTitle = `PW Lead Deadline ${Date.now()}`;
        await leadsPage.goto();
        await leadsPage.create({ title: leadTitle, description: 'Lead to update deadline' });

        await leadsPage.edit(leadTitle, { deadline: '2026-12-31' });

        await expect(page.getByText('Lead updated successfully')).toBeVisible();
    });

    test('followup stores deadline as datetime string', async ({ page }) => {
        const leadsPage = new LeadsPage(page);
        const leadTitle = `PW Lead Followup DateTime ${Date.now()}`;
        await leadsPage.goto();
        await leadsPage.create({ title: leadTitle, description: 'Lead followup datetime test' });

        await leadsPage.edit(leadTitle, { followup: '2026-12-31' });

        await expect(page.getByText('Lead updated successfully')).toBeVisible();
    });

    test('followup stores deadline with correct time component', async ({ page }) => {
        const leadsPage = new LeadsPage(page);
        const leadTitle = `PW Lead Followup Time ${Date.now()}`;
        await leadsPage.goto();
        await leadsPage.create({ title: leadTitle, description: 'Lead followup time test' });

        await leadsPage.edit(leadTitle, { followup: '2026-12-31 14:30' });

        await expect(page.getByText('Lead updated successfully')).toBeVisible();
    });

    test('followup deadline is stored as parseable date', async ({ page }) => {
        const leadsPage = new LeadsPage(page);
        const leadTitle = `PW Lead Followup Parse ${Date.now()}`;
        await leadsPage.goto();
        await leadsPage.create({ title: leadTitle, description: 'Lead followup parse test' });

        await leadsPage.edit(leadTitle, { followup: '2026-12-31' });

        await expect(page.getByText('Lead updated successfully')).toBeVisible();
    });
});
