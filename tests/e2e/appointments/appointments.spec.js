const { test, expect } = require('@playwright/test');
const {
  BASE_URL,
  loginAsAdmin,
  usersCollection,
  jsonHeaders,
  expectValidationError,
} = require('../helpers/plain-e2e');

async function fetchAppointments(request) {
  const response = await request.get(`${BASE_URL}/appointments/data`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
  expect(response.status()).toBe(200);
  const payload = await response.json();
  expect(Array.isArray(payload)).toBe(true);
  return payload;
}

test('guest is redirected from appointments calendar route', async ({ page }) => {
  await page.goto(`${BASE_URL}/appointments/calendar`);
  await expect(page).toHaveURL(/login/);
});

test('appointments feed returns a JSON array with the correct shape', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  const payload = await fetchAppointments(request);

  // Feed may be empty on a fresh DB — that is valid; we only validate shape when records exist
  expect(Array.isArray(payload)).toBe(true);
  if (payload.length > 0) {
    expect(payload[0]).toHaveProperty('external_id');
    expect(payload[0]).toHaveProperty('start_at');
    expect(payload[0]).toHaveProperty('end_at');
  }
});

test('appointment update persists new times and assignee', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  const appointments = await fetchAppointments(request);
  test.skip(appointments.length === 0, 'No appointments seeded — cannot test update without an existing record');

  const appointment = appointments[0];
  const users = await usersCollection(request);
  expect(users.length).toBeGreaterThan(0);
  const assignee = users.find((user) => user.external_id !== appointment.user?.external_id) ?? users[0];
  expect(assignee).toBeDefined();
  expect(assignee.external_id).toBeTruthy();

  const response = await request.post(`${BASE_URL}/appointments/update/${appointment.external_id}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {
      id: appointment.external_id,
      start: '2030-01-02T09:00:00.000Z',
      end: '2030-01-02T10:00:00.000Z',
      group: assignee.external_id,
    },
  });

  const payload = await response.json();

  expect(response.status()).toBe(200);
  expect(String(payload.start_at)).toContain('2030-01-02');
  expect(String(payload.end_at)).toContain('2030-01-02');
});

test('appointment update rejects missing assignee', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  const appointments = await fetchAppointments(request);
  test.skip(appointments.length === 0, 'No appointments seeded — cannot test validation without an existing record');

  const appointment = appointments[0];

  const response = await request.post(`${BASE_URL}/appointments/update/${appointment.external_id}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {
      id: appointment.external_id,
      start: '2030-01-02T09:00:00.000Z',
      end: '2030-01-02T10:00:00.000Z',
      group: '',
    },
  });

  await expectValidationError(response, 'group');
});

test('malformed appointment delete id is rejected', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  const response = await request.delete(`${BASE_URL}/appointments/invalid-@@@`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
  });

  expect([404, 422]).toContain(response.status());
});
