import type { APIRequestContext, BrowserContext, Page } from '@playwright/test';
import { loginAsSeededAdmin } from './admin-auth';
import { registerAndLoginNewUser } from './user-auth';

export type AuthenticatedSession = {
  context: BrowserContext;
  page: Page;
  request: APIRequestContext;
  dispose: () => Promise<void>;
};

function requireBrowser(page: Page) {
  const browser = page.context().browser();
  if (!browser) {
    throw new Error('Browser instance is unavailable');
  }

  return browser;
}

export async function createAdminSession(page: Page): Promise<AuthenticatedSession> {
  const context = await requireBrowser(page).newContext();
  const adminPage = await context.newPage();
  await loginAsSeededAdmin(adminPage);

  return {
    context,
    page: adminPage,
    request: context.request,
    dispose: async () => {
      await context.close();
    },
  };
}

export async function createNonAdminSession(page: Page): Promise<AuthenticatedSession> {
  const context = await requireBrowser(page).newContext();
  const userPage = await context.newPage();
  await registerAndLoginNewUser(userPage);

  return {
    context,
    page: userPage,
    request: context.request,
    dispose: async () => {
      await context.close();
    },
  };
}
