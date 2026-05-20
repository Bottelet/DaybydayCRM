import { test } from '@playwright/test';
import { SEED_CLIENT_NAME } from '../../playwright/fixtures/users';
import { ClientsPage } from '../pages/ClientsPage';

test.describe('Clients', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/clients');
  });

  test('can create a new client with all fields', async ({ page }) => {
    const p = new ClientsPage(page);
    const name = `PW Client ${Date.now()}`;
    await p.create({ company: name, email: 'client@example.test', phone: '123456789', address: 'Main Street 1' });
    await p.assertVisible(name);
  });

  test('can view seeded Playwright Seed Client', async ({ page }) => {
    const p = new ClientsPage(page);
    await p.assertVisible(SEED_CLIENT_NAME);
  });

  test("can edit a client's company name", async ({ page }) => {
    const p = new ClientsPage(page);
    const original = `PW Edit ${Date.now()}`;
    const updated = `${original} Updated`;
    await p.create({ company: original, email: `${Date.now()}@example.test` });
    await p.edit(original, { company: updated });
    await p.assertVisible(updated);
  });

  test('can delete a client created in this test', async ({ page }) => {
    const p = new ClientsPage(page);
    const name = `PW Delete ${Date.now()}`;
    await p.create({ company: name, email: `${Date.now()}@example.test` });
    await p.delete(name);
  });
});
