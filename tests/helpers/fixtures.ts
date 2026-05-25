import { test as base, expect } from '@playwright/test';
import { loginAsSeededAdmin } from './admin-auth';
import { registerAndLoginNewUser } from '../e2e/helpers/user-auth';

export const test = base.extend({
  page: async ({ page }, use) => {
    await loginAsSeededAdmin(page);
    await expect(page).not.toHaveURL(/login/);
    await use(page);
  },
});

export const nonAdminTest = base.extend({
  page: async ({ page }, use) => {
    await registerAndLoginNewUser(page);
    await use(page);
  },
});

export { expect };
