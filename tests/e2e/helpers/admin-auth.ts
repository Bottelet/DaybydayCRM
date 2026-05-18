import type { Page } from '@playwright/test';
import { PLAYWRIGHT_BASE_URL } from './config';

const ADMIN_EMAIL = process.env.PLAYWRIGHT_ADMIN_EMAIL ?? 'admin@admin.com';
const ADMIN_PASSWORD = process.env.PLAYWRIGHT_ADMIN_PASSWORD ?? 'admin123';

export async function loginAsSeededAdmin(page: Page) {
  await page.goto(`${PLAYWRIGHT_BASE_URL}/login`);
  await page.getByLabel(/email/i).fill(ADMIN_EMAIL);
  await page.getByLabel(/password/i).fill(ADMIN_PASSWORD);
  await page.getByRole('button', { name: /log ?in|sign ?in/i }).click();
  await page.waitForURL((url) => !url.pathname.endsWith('/login'));
}
