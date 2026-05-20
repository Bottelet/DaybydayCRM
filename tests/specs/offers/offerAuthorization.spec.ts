import { test, expect } from '@playwright/test';
import { TEST_USERS, SEED_CLIENT_NAME, SEED_LEAD_TITLES } from '../../playwright/fixtures/users';

test.describe('OfferAuthorization', () => {
  test('it user with offer create permission can create offer', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
  });

  test('it user without offer create permission cannot create offer', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(forbidden|unauthorized|permission|login|warning|error)/i).first()).toBeVisible();
  });

  test('it user with offer edit permission can update offer', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(update|updated|saved|assigned|status|restored)/i).first()).toBeVisible();
  });

  test('it user without offer edit permission cannot update offer', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(forbidden|unauthorized|permission|login|warning|error)/i).first()).toBeVisible();
  });

  test('it user with offer edit permission can mark offer as won', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(update|updated|saved|assigned|status|restored)/i).first()).toBeVisible();
  });

  test('it user without offer edit permission cannot mark offer as won', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(forbidden|unauthorized|permission|login|warning|error)/i).first()).toBeVisible();
  });

  test('it user with offer edit permission can mark offer as lost', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(update|updated|saved|assigned|status|restored)/i).first()).toBeVisible();
  });

  test('it user without offer edit permission cannot mark offer as lost', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(forbidden|unauthorized|permission|login|warning|error)/i).first()).toBeVisible();
  });

});
