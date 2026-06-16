const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, dismissTourIfVisible } = require('../helpers/plain-e2e');

test('tasks index page has a create button', async ({ page }) => {
  await loginAsAdmin(page);
  await page.goto(`${BASE_URL}/tasks`);
  await dismissTourIfVisible(page);
  await expect(page.locator('a[href*="/tasks/create"]')).toBeVisible();
});
