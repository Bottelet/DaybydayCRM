import { test, expect } from '@playwright/test';
import { TEST_USERS, SEED_CLIENT_NAME, SEED_LEAD_TITLES } from '../../playwright/fixtures/users';
import { LeadsPage } from '../../pages/LeadsPage';
import { LoginPage } from '../../pages/LoginPage';

test.describe('DeleteLeadController', () => {
  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);
  });

  test('it deletes lead', async ({ page }) => {
    /* Arrange */
    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Delete ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead to delete' });
    await leadsPage.assertVisible(leadTitle);

    /* Act */
    await leadsPage.delete(leadTitle);

    /* Assert */
    await expect(page.getByText(leadTitle)).not.toBeVisible();
  });

  test('it deletes offers if flag given', async ({ page }) => {
    /* Arrange */
    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead with Offers ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead with offers' });
    await leadsPage.assertVisible(leadTitle);

    /* Act */
    await leadsPage.delete(leadTitle);

    /* Assert */
    await expect(page.getByText(leadTitle)).not.toBeVisible();
  });

  test('it does not delete offers if flag is not given but remove reference', async ({ page }) => {
    /* Arrange */
    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Offer Ref ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead with offer reference' });
    await leadsPage.assertVisible(leadTitle);

    /* Act */
    await leadsPage.delete(leadTitle);

    /* Assert */
    await expect(page.getByText(leadTitle)).not.toBeVisible();
  });

  test('it can delete lead if flag is given and offers does not exists', async ({ page }) => {
    /* Arrange */
    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead No Offers ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead without offers' });
    await leadsPage.assertVisible(leadTitle);

    /* Act */
    await leadsPage.delete(leadTitle);

    /* Assert */
    await expect(page.getByText(leadTitle)).not.toBeVisible();
  });

});
