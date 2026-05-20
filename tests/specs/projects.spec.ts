import { test } from '@playwright/test';
import { SEED_CLIENT_NAME } from '../../playwright/fixtures/users';
import { ProjectsPage } from '../pages/ProjectsPage';

test.describe('Projects', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/projects');
  });

  test('can create a project on the seed client', async ({ page }) => {
    test.skip(!['owner', 'manager'].includes(test.info().project.name));
    const p = new ProjectsPage(page);
    const title = `PW Project ${Date.now()}`;
    await p.create({ name: title, client: SEED_CLIENT_NAME });
    await p.assertVisible(title);
  });

  test('can edit a project', async ({ page }) => {
    test.skip(!['owner', 'manager'].includes(test.info().project.name));
    const p = new ProjectsPage(page);
    const title = `PW Project Edit ${Date.now()}`;
    const updated = `${title} Updated`;
    await p.create({ name: title, client: SEED_CLIENT_NAME });
    await p.edit(title, { name: updated });
    await p.assertVisible(updated);
  });

  test("can change a project's status", async ({ page }) => {
    test.skip(!['owner', 'manager'].includes(test.info().project.name));
    const p = new ProjectsPage(page);
    const title = `PW Project Status ${Date.now()}`;
    await p.create({ name: title, client: SEED_CLIENT_NAME });
    await p.changeStatus(title, 'Done');
    await p.assertStatus(title, 'Done');
  });

  test('can delete a project created in this test', async ({ page }) => {
    test.skip(!['owner', 'manager'].includes(test.info().project.name));
    const p = new ProjectsPage(page);
    const title = `PW Project Delete ${Date.now()}`;
    await p.create({ name: title, client: SEED_CLIENT_NAME });
    await p.delete(title);
    await p.assertNotVisible(title);
  });
});
