import { defineConfig, devices } from '@playwright/test';
import * as path from 'path';

// Path where global-setup.js writes the pre-dismissed tour cookie state.
// Every browser context starts with these cookies so the Bootstrap tour
// never fires — equivalent to a global beforeEach that sets the cookies.
const noTourStatePath = path.join(__dirname, 'tests/e2e/setup/no-tour-state.json');

export default defineConfig({
  testDir: './tests/e2e',
  // Runs tests/e2e/setup/global-setup.js once before any worker starts.
  // It generates no-tour-state.json for the current PLAYWRIGHT_BASE_URL host.
  globalSetup: './tests/e2e/setup/global-setup.js',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  workers: process.env.CI ? 2 : 4,
  reporter: process.env.CI
    ? [['blob'], ['json', { outputFile: 'storage/logs/e2e-results.json' }], ['./tests/helpers/e2e-file-logger.ts']]
    : [['html'], ['list'], ['./tests/helpers/e2e-file-logger.ts']],
  use: {
    baseURL: process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost',
    trace: process.env.CI ? 'on-first-retry' : 'on',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    // Pre-load tour dismissal cookies into every browser context.
    // This is the global beforeEach equivalent: the Bootstrap tour will
    // never show, so no backdrop can block UI interactions.
    storageState: noTourStatePath,
  },
  timeout: 30 * 1000,
  expect: {
    timeout: 10 * 1000,
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
    ...(process.env.CI
      ? [
          {
            name: 'firefox',
            use: { ...devices['Desktop Firefox'] },
          },
        ]
      : []),
  ],
});
