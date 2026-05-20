import { execSync } from 'node:child_process';
import { mkdirSync } from 'node:fs';
import { chromium, type FullConfig } from '@playwright/test';
import { TEST_USERS } from '../playwright/fixtures/users';

export default async function globalSetup(config: FullConfig): Promise<void> {
  execSync('php artisan migrate:fresh --seed --seeder=DummyDatabaseSeeder', {
    stdio: 'inherit',
    env: { ...process.env, APP_ENV: 'testing' },
  });

  const baseURL = config.projects[0]?.use?.baseURL as string | undefined;
  const resolvedBaseURL = baseURL ?? process.env.APP_URL ?? 'http://localhost';

  mkdirSync('tests/fixtures/auth', { recursive: true });

  for (const [role, user] of Object.entries(TEST_USERS)) {
    const browser = await chromium.launch();

    try {
      const page = await browser.newPage({ baseURL: resolvedBaseURL });

      await page.goto('/login');
      await page.getByLabel(/email/i).fill(user.email);
      await page.getByLabel(/password/i).fill(user.password);
      await page.getByRole('button', { name: /log ?in|sign ?in/i }).click();
      await page.waitForURL((url) => !url.pathname.includes('/login'));

      await page.context().storageState({ path: `tests/fixtures/auth/${role}.json` });
    } finally {
      await browser.close();
    }
  }
}
