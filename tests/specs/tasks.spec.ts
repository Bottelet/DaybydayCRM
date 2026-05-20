import { test } from '@playwright/test';
import { SEED_CLIENT_NAME } from '../../playwright/fixtures/users';
import { TasksPage } from '../pages/TasksPage';

test.describe('Tasks', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/tasks');
  });

  test('can create a task assigned to the seed client', async ({ page }) => {
    test.skip(!['owner', 'employee'].includes(test.info().project.name));
    const p = new TasksPage(page);
    const title = `PW Task ${Date.now()}`;
    await p.create({ title, client: SEED_CLIENT_NAME });
    await p.assertVisible(title);
  });

  test('can edit a task', async ({ page }) => {
    test.skip(!['owner', 'employee'].includes(test.info().project.name));
    const p = new TasksPage(page);
    const title = `PW Task Edit ${Date.now()}`;
    const updated = `${title} Updated`;
    await p.create({ title, client: SEED_CLIENT_NAME });
    await p.edit(title, { title: updated });
    await p.assertVisible(updated);
  });

  test('can mark a task as closed', async ({ page }) => {
    test.skip(!['owner', 'employee'].includes(test.info().project.name));
    const p = new TasksPage(page);
    const title = `PW Task Close ${Date.now()}`;
    await p.create({ title, client: SEED_CLIENT_NAME });
    await p.close(title);
    await p.assertTaskClosed(title);
  });

  test('can delete a task created in this test', async ({ page }) => {
    test.skip(!['owner', 'employee'].includes(test.info().project.name));
    const p = new TasksPage(page);
    const title = `PW Task Delete ${Date.now()}`;
    await p.create({ title, client: SEED_CLIENT_NAME });
    await p.delete(title);
    await p.assertNotVisible(title);
  });
});
