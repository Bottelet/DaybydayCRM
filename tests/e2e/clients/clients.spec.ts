import { test, expect } from '@playwright/test';
import { PLAYWRIGHT_BASE_URL } from '../helpers/config';

test.describe('Clients module', () => {
  test('authenticated user can open clients index page', async ({ page }) => {
    await page.goto(`${PLAYWRIGHT_BASE_URL}/clients`);
    await expect(page).toHaveURL(/\/clients/);
  });

  test('authenticated user can create client from UI when form is available', async ({ page }) => {
    const clientName = `PW Client ${Date.now()}`;

    await page.goto(`${PLAYWRIGHT_BASE_URL}/clients`);

    const createClientButton = page.getByRole('button', { name: /create client/i });
    await expect(createClientButton).toBeVisible();
    await createClientButton.click();

    await page.getByPlaceholder(/client name/i).fill(clientName);

    await Promise.all([
      page.waitForResponse((response) =>
        response.url().includes('/clients') &&
        response.request().method() === 'POST' &&
        [200, 201, 302].includes(response.status())
      ),
      page.getByRole('button', { name: /create client/i }).last().click(),
    ]);

    await expect(page.getByText(clientName)).toBeVisible();
  });
});
