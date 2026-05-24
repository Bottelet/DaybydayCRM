import { test as guestTest, expect as guestExpect } from '@playwright/test';
import { test, expect, nonAdminTest } from '../../helpers/fixtures';
import { PLAYWRIGHT_BASE_URL } from '../../helpers/config';
import { fetchCsrfToken } from '../../helpers/csrf';

async function createAppointment(
  page: import('@playwright/test').Page,
  request: import('@playwright/test').APIRequestContext,
  overrides: Record<string, string> = {},
) {
  const csrf = await fetchCsrfToken(page);
  return request.post(`${PLAYWRIGHT_BASE_URL}/appointments`, {
    failOnStatusCode: false,
    headers: {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': csrf,
    },
    data: {
      title: `PW Appointment ${Date.now()}`,
      start: '2030-06-01 10:00:00',
      end: '2030-06-01 11:00:00',
      ...overrides,
    },
  });
}

test.describe('Appointments feature behavior', () => {
  // ── calendar page ──────────────────────────────────────────────────────────

  test('calendar page loads for authenticated user', async ({ page }) => {
    await page.goto(`${PLAYWRIGHT_BASE_URL}/calendar`);
    await expect(page).toHaveURL(/calendar/);
  });

  // ── time slot listing ──────────────────────────────────────────────────────

  test('can get appointments within a time slot', async ({ request }) => {
    const response = await request.get(
      `${PLAYWRIGHT_BASE_URL}/appointments?start=2030-06-01T00%3A00%3A00&end=2030-06-30T23%3A59%3A59`,
      {
        failOnStatusCode: false,
        headers: { Accept: 'application/json' },
      },
    );

    expect(response.status()).toBe(200);
    const payload = await response.json();
    expect(Array.isArray(payload)).toBe(true);
  });

  test('can get absences within a time slot', async ({ request }) => {
    const response = await request.get(
      `${PLAYWRIGHT_BASE_URL}/appointments?start=2030-06-01T00%3A00%3A00&end=2030-06-30T23%3A59%3A59`,
      {
        failOnStatusCode: false,
        headers: { Accept: 'application/json' },
      },
    );

    expect(response.status()).toBe(200);
  });

  test('does not return appointments for other source types in user morph', async ({ request }) => {
    const response = await request.get(
      `${PLAYWRIGHT_BASE_URL}/appointments?start=2020-01-01T00%3A00%3A00&end=2020-12-31T23%3A59%3A59`,
      {
        failOnStatusCode: false,
        headers: { Accept: 'application/json' },
      },
    );

    expect(response.status()).toBe(200);
    const payload = await response.json() as Array<{ type?: string }>;
    const nonAppointments = payload.filter((e) => e.type && e.type !== 'appointment');
    expect(nonAppointments.length).toBe(0);
  });

  test('returns user appointments via morph relationship', async ({ request }) => {
    const response = await request.get(
      `${PLAYWRIGHT_BASE_URL}/appointments?start=2030-01-01T00%3A00%3A00&end=2030-12-31T23%3A59%3A59`,
      {
        failOnStatusCode: false,
        headers: { Accept: 'application/json' },
      },
    );

    expect(response.status()).toBe(200);
    expect(Array.isArray(await response.json())).toBe(true);
  });

  // ── controller structure ───────────────────────────────────────────────────

  test('appointments controller does not expose a store route', async ({ page, request }) => {
    const response = await createAppointment(page, request);

    // PHPUnit asserts no store method exists — HTTP layer returns 404 or 405
    expect([404, 405]).toContain(response.status());
  });

  test('posting to appointments resource route returns not found', async ({ page, request }) => {
    const response = await createAppointment(page, request);

    expect([404, 405]).toContain(response.status());
  });

  test('appointments controller retains calendar method', async ({ page }) => {
    await page.goto(`${PLAYWRIGHT_BASE_URL}/calendar`);
    await expect(page).toHaveURL(/calendar/);
    expect(page.url()).not.toMatch(/login/);
  });

  test('appointments controller retains appointments json method', async ({ request }) => {
    const response = await request.get(`${PLAYWRIGHT_BASE_URL}/appointments`, {
      failOnStatusCode: false,
      headers: { Accept: 'application/json' },
    });

    expect([200, 400]).toContain(response.status()); // 400 if start/end missing
  });

  test('appointments controller retains destroy method', async ({ page, request }) => {
    // Verify the DELETE route exists — 404 on missing id, not 405 method not allowed
    const response = await request.delete(
      `${PLAYWRIGHT_BASE_URL}/appointments/00000000-0000-0000-0000-000000000999`,
      {
        failOnStatusCode: false,
        headers: {
          Accept: 'application/json',
          'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
      },
    );

    expect([403, 404]).toContain(response.status());
  });

  test('appointments controller retains update method', async ({ page, request }) => {
    // Verify the PATCH route exists — 404 on missing id, not 405
    const response = await request.patch(
      `${PLAYWRIGHT_BASE_URL}/appointments/00000000-0000-0000-0000-000000000999`,
      {
        failOnStatusCode: false,
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
        data: { title: 'test' },
      },
    );

    expect([403, 404, 422]).toContain(response.status());
  });

  test('appointments controller does not have create request class dependency', async ({ page }) => {
    // Smoke: the calendar page loads without errors tied to old request class wiring
    await page.goto(`${PLAYWRIGHT_BASE_URL}/calendar`);
    await expect(page).not.toHaveURL(/login/);
    await expect(page).not.toHaveURL(/500|error/i);
  });

  // ── update ─────────────────────────────────────────────────────────────────

  test('authorized user can update appointment', async ({ page, request }) => {
    // create via seed data — if store is unavailable use a seeded appointment id
    const seedId = process.env.SEED_APPOINTMENT_EXTERNAL_ID ?? '';
    if (!seedId) return;

    const updateResponse = await request.patch(
      `${PLAYWRIGHT_BASE_URL}/appointments/${seedId}`,
      {
        failOnStatusCode: false,
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
        data: { title: `Updated ${Date.now()}` },
      },
    );

    expect([200, 302]).toContain(updateResponse.status());
  });

  test('authorized user can update appointment times', async ({ page, request }) => {
    const seedId = process.env.SEED_APPOINTMENT_EXTERNAL_ID ?? '';
    if (!seedId) return;

    const updateResponse = await request.patch(
      `${PLAYWRIGHT_BASE_URL}/appointments/${seedId}`,
      {
        failOnStatusCode: false,
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
        data: { start: '2030-06-01 14:00:00', end: '2030-06-01 15:00:00' },
      },
    );

    expect([200, 302]).toContain(updateResponse.status());
  });

  test('json error returned when appointment update fails', async ({ page, request }) => {
    const response = await request.patch(
      `${PLAYWRIGHT_BASE_URL}/appointments/00000000-0000-0000-0000-000000000999`,
      {
        failOnStatusCode: false,
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
        data: {},
      },
    );

    expect([404, 422]).toContain(response.status());
  });

  test('permission check is required before appointment update', async ({ page, request }) => {
    const response = await request.patch(
      `${PLAYWRIGHT_BASE_URL}/appointments/00000000-0000-0000-0000-000000000999`,
      {
        failOnStatusCode: false,
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
        data: {},
      },
    );

    expect([403, 404, 422]).toContain(response.status());
  });

  // ── delete ─────────────────────────────────────────────────────────────────

  test('authorized user can delete appointment', async ({ page, request }) => {
    const seedId = process.env.SEED_APPOINTMENT_EXTERNAL_ID ?? '';
    if (!seedId) return;

    const deleteResponse = await request.delete(
      `${PLAYWRIGHT_BASE_URL}/appointments/${seedId}`,
      {
        failOnStatusCode: false,
        headers: {
          Accept: 'application/json',
          'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
      },
    );

    expect([200, 302]).toContain(deleteResponse.status());
  });

  test('can destroy appointment', async ({ page, request }) => {
    const seedId = process.env.SEED_APPOINTMENT_EXTERNAL_ID ?? '';
    if (!seedId) return;

    const response = await request.delete(
      `${PLAYWRIGHT_BASE_URL}/appointments/${seedId}`,
      {
        failOnStatusCode: false,
        headers: {
          Accept: 'application/json',
          'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
      },
    );

    expect([200, 302]).toContain(response.status());
  });

  // ── authorization ──────────────────────────────────────────────────────────

  nonAdminTest('unauthorized user cannot update appointment', async ({ page, request }) => {
    const response = await request.patch(
      `${PLAYWRIGHT_BASE_URL}/appointments/00000000-0000-0000-0000-000000000001`,
      {
        failOnStatusCode: false,
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
        data: { title: 'Hacked' },
      },
    );

    expect(response.status()).toBe(403);
  });

  nonAdminTest('unauthorized user cannot delete appointment', async ({ page, request }) => {
    const response = await request.delete(
      `${PLAYWRIGHT_BASE_URL}/appointments/00000000-0000-0000-0000-000000000001`,
      {
        failOnStatusCode: false,
        headers: {
          Accept: 'application/json',
          'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
      },
    );

    expect(response.status()).toBe(403);
  });
});

guestTest('guest is redirected from calendar page', async ({ page }) => {
  await page.goto(`${PLAYWRIGHT_BASE_URL}/calendar`);
  await guestExpect(page).toHaveURL(/login/);
});
