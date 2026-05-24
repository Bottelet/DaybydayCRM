import type { Page } from '@playwright/test';
import { PLAYWRIGHT_BASE_URL } from './config';

export async function fetchCsrfToken(page: Page): Promise<string> {
  await page.goto(`${PLAYWRIGHT_BASE_URL}/login`);
  const token = await page.locator('meta[name="csrf-token"]').first().getAttribute('content');
  if (!token) {
    throw new Error('CSRF token not found on login page');
  }

  return token;
}
