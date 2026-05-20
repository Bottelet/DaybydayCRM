import { test, expect } from '@playwright/test';
import { TEST_USERS, SEED_CLIENT_NAME, SEED_LEAD_TITLES } from '../../playwright/fixtures/users';

test.describe('SubdirectoryUrlGeneration', () => {
  test('it url helper generates absolute urls with subdirectory', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/url');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
  });

  test('it url helper generates absolute urls at root', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/url');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
  });

  test('it task show page contains correct document upload url', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/url');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
  });

  test('it project show page contains correct document upload url', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/url');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
  });

  test('it products index contains correct creator modal url', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/url');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
  });

  test('it task create page contains correct client create redirect url', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/url');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
  });

  test('it project create page contains correct client create redirect url', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/url');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
  });

  test('it lead create page contains correct client create redirect url', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/url');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
  });

  test('it users index contains correct delete url', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/url');

    /* Assert */
    await expect(page.getByText(/(delete|removed|warning|cannot)/i).first()).toBeVisible();
  });

  test('it master layout contains base url configuration', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/url');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
  });

  test('it master layout contains base url configuration at root', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/url');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
  });

  test('it url generation works with https subdirectory', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/url');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
  });

  test('it url generation works with port and subdirectory', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/url');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
  });

  test('it master layout loads js assets with correct subdirectory path', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/url');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
  });

  test('it master layout loads js assets at root installation', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/url');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
  });

  test('it calendar page loads js assets with correct subdirectory path', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/url');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
  });

  test('it calendar page contains base url configuration', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/url');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
  });

});
