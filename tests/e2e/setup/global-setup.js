/**
 * Playwright global setup — runs once before any test worker starts.
 *
 * Writes a storageState JSON containing the tour-dismissal cookies for
 * the current PLAYWRIGHT_BASE_URL hostname. The playwright.config.ts
 * points use.storageState at this file so every browser context starts
 * with the tour already dismissed — no backdrop, no blocked clicks.
 *
 * This is the equivalent of calling dismissTourIfVisible() in a
 * beforeEach(), but applied automatically to every single test without
 * touching individual spec files.
 */

'use strict';

const fs = require('fs');
const path = require('path');

async function globalSetup() {
  const baseUrl = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost';
  let hostname;
  try {
    hostname = new URL(baseUrl).hostname;
  } catch (error) {
    throw new Error(
      `[global-setup] Invalid PLAYWRIGHT_BASE_URL="${baseUrl}". ` +
      `Must be a valid URL (e.g., http://localhost or http://localhost:8000). ` +
      `Original error: ${error.message}`
    );
  }

  const farFuture = Math.floor(Date.now() / 1000) + 10 * 365 * 24 * 3600;

  const tourCookies = [
    'step_dashboard',
    'step_client_index',
    'step_client_create',
  ].map((name) => ({
    name,
    value: '1',
    domain: hostname,
    path: '/',
    expires: farFuture,
    httpOnly: false,
    secure: false,
    sameSite: 'Lax',
  }));

  const storageState = { cookies: tourCookies, origins: [] };
  const outputPath = path.join(__dirname, 'no-tour-state.json');

  fs.mkdirSync(path.dirname(outputPath), { recursive: true });
  fs.writeFileSync(outputPath, JSON.stringify(storageState, null, 2));

  console.log(`[global-setup] Tour cookies written for domain "${hostname}" → ${outputPath}`);
}

module.exports = globalSetup;
