import { test } from '@playwright/test';
import { SEED_CLIENT_NAME, SEED_LEAD_TITLES } from '../../playwright/fixtures/users';
import { LeadsPage } from '../pages/LeadsPage';

test.describe('Leads', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/leads');
  });

  test('can create a lead on the seed client', async ({ page }) => {
    test.skip(!['owner', 'manager'].includes(test.info().project.name));
    const p = new LeadsPage(page);
    const title = `PW Lead ${Date.now()}`;
    await p.create({ title, client: SEED_CLIENT_NAME });
    await p.assertVisible(title);
  });

  test('can view the three seeded leads by title', async ({ page }) => {
    test.skip(!['owner', 'manager'].includes(test.info().project.name));
    const p = new LeadsPage(page);

    for (const title of SEED_LEAD_TITLES) {
      await p.assertVisible(title);
    }
  });

  test("can edit a lead's title", async ({ page }) => {
    test.skip(!['owner', 'manager'].includes(test.info().project.name));
    const p = new LeadsPage(page);
    const title = `PW Lead Edit ${Date.now()}`;
    const updated = `${title} Updated`;
    await p.create({ title, client: SEED_CLIENT_NAME });
    await p.edit(title, { title: updated });
    await p.assertVisible(updated);
  });

  test("can change a lead's status", async ({ page }) => {
    test.skip(!['owner', 'manager'].includes(test.info().project.name));
    const p = new LeadsPage(page);
    const title = `PW Lead Status ${Date.now()}`;
    await p.create({ title, client: SEED_CLIENT_NAME });
    await p.changeStatus(title, 'Won');
    await p.assertStatus(title, 'Won');
  });

  test('can delete a lead created in this test', async ({ page }) => {
    test.skip(!['owner', 'manager'].includes(test.info().project.name));
    const p = new LeadsPage(page);
    const title = `PW Lead Delete ${Date.now()}`;
    await p.create({ title, client: SEED_CLIENT_NAME });
    await p.delete(title);
  });
});
