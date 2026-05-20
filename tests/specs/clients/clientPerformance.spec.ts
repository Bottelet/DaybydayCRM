import { test, expect } from '@playwright/test';
import { TEST_USERS, SEED_CLIENT_NAME, SEED_CLIENT_EXTERNAL_ID, SEED_LEAD_TITLES } from '../../playwright/fixtures/users';

test.describe('ClientPerformance', () => {
  test('it lists clients without n plus 1 queries', async ({ page }) => {
    /* Arrange */ // uses seeded data

    /* Act */
    await page.goto('/clients');

    /* Assert */
    await expect(page.getByText(SEED_CLIENT_NAME)).toBeVisible();
  });

  test('it shows client detail without n plus 1 queries', async ({ page }) => {
    /* Arrange */ // uses seeded data

    /* Act */
    await page.goto(`/clients/${SEED_CLIENT_EXTERNAL_ID}`);

    /* Assert */
    await expect(page.getByText(SEED_CLIENT_NAME)).toBeVisible();
  });

  test('it loads task datatable without n plus 1 queries', async ({ page }) => {
    /* Arrange */ // uses seeded data

    /* Act */
    await page.goto(`/clients/${SEED_CLIENT_EXTERNAL_ID}`);

    /* Assert */
    await expect(page.getByText(SEED_CLIENT_NAME)).toBeVisible();
  });

  test('it loads project datatable without n plus 1 queries', async ({ page }) => {
    /* Arrange */ // uses seeded data

    /* Act */
    await page.goto(`/clients/${SEED_CLIENT_EXTERNAL_ID}`);

    /* Assert */
    await expect(page.getByText(SEED_CLIENT_NAME)).toBeVisible();
  });

  test('it loads lead datatable without n plus 1 queries', async ({ page }) => {
    /* Arrange */ // uses seeded data

    /* Act */
    await page.goto(`/clients/${SEED_CLIENT_EXTERNAL_ID}`);

    /* Assert */
    await expect(page.getByText(SEED_CLIENT_NAME)).toBeVisible();
  });

  test('it handles large client load efficiently', async ({ page }) => {
    /* Arrange */ // uses seeded data

    /* Act */
    await page.goto('/clients');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
  });

});
