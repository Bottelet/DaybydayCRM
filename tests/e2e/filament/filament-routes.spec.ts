import { test as authTest, expect } from '../helpers/fixtures';
import { PLAYWRIGHT_BASE_URL } from '../helpers/config';
import { loadWebRouteCases } from '../helpers/route-cases';
import { interpolateRoutePath } from '../helpers/route-paths';

let cachedFilamentGetRoutes: ReturnType<typeof loadWebRouteCases> | null = null;

function getFilamentGetRoutes() {
  if (cachedFilamentGetRoutes === null) {
    cachedFilamentGetRoutes = loadWebRouteCases().filter(
      (routeCase) => routeCase.method === 'GET' && routeCase.path.startsWith('/admin')
    );
  }

  return cachedFilamentGetRoutes;
}

const filamentGetRoutes = getFilamentGetRoutes();

authTest.describe('Filament page and resource coverage', () => {
  for (const routeCase of filamentGetRoutes) {
    authTest(`filament get route loads: ${routeCase.path}`, async ({ page }) => {
      /* Arrange */
      const routePath = interpolateRoutePath(routeCase.path);

      /* Act */
      const response = await page.goto(`${PLAYWRIGHT_BASE_URL}${routePath}`);

      /* Assert */
      expect(response).not.toBeNull();
      expect(response!.status()).toBeLessThan(500);
    });
  }
});
