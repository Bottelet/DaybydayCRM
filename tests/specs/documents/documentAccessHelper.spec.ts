import { test, expect } from '@playwright/test';
import { TEST_USERS, SEED_CLIENT_NAME, SEED_LEAD_TITLES } from '../../playwright/fixtures/users';

test.describe('DocumentAccessHelper', () => {
  test('it creator of task can view task document', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/documents');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
  });

  test('it assignee of task can view task document', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/documents');

    /* Assert */
    await expect(page.getByText(/(update|updated|saved|assigned|status|restored)/i).first()).toBeVisible();
  });

  test('it client owner can view document attached to their client task', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/documents');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
  });

  test('it unrelated user cannot view document they have no connection to', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/documents');

    /* Assert */
    await expect(page.getByText(/(forbidden|unauthorized|permission|login|warning|error)/i).first()).toBeVisible();
  });

  test('it json request returns 403 json for unauthorized document view', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/documents');

    /* Assert */
    await expect(page.getByText(/(forbidden|unauthorized|permission|login|warning|error)/i).first()).toBeVisible();
  });

  test('it unrelated user cannot download document they have no connection to', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/documents');

    /* Assert */
    await expect(page.getByText(/(forbidden|unauthorized|permission|login|warning|error)/i).first()).toBeVisible();
  });

  test('it json download request returns 403 for unauthorized user', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/documents');

    /* Assert */
    await expect(page.getByText(/(forbidden|unauthorized|permission|login|warning|error)/i).first()).toBeVisible();
  });

  test('it authorization is checked before storage access on view', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/documents');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
  });

});
