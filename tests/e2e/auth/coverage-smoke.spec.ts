import { test, expect } from '../../helpers/fixtures';
import { PLAYWRIGHT_BASE_URL } from '../../helpers/config';

const domainPages = ['/roles', '/users', '/tasks', '/projects', '/leads'];

test.describe('Core domain smoke — authenticated access', () => {
    for (const path of domainPages) {
        test(`authenticated user receives 200 on ${path}`, async ({ page }) => {
            const response = await page.goto(`${PLAYWRIGHT_BASE_URL}${path}`);
            await expect(page).toHaveURL(new RegExp(path.replace('/', '\\/')));
            expect(response).not.toBeNull();
            expect(response!.status()).toBe(200);
        });
    }
});
