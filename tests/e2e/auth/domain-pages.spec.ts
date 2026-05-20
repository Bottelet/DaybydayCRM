import { test, expect } from '../helpers/fixtures';
import { PLAYWRIGHT_BASE_URL } from '../helpers/config';

const domainPages = ['/roles', '/users', '/tasks', '/projects', '/leads'];

test.describe('Core domain browser coverage', () => {
  for (const path of domainPages) {
    test(`authenticated user can open ${path}`, async ({ page }) => {
      const response = await page.goto(`${PLAYWRIGHT_BASE_URL}${path}`);
      await expect(page).toHaveURL(new RegExp(path.replace('/', '\\/')));
      expect(response).not.toBeNull();
      expect(response!.status()).toBeLessThan(500);
    });
  }
});
