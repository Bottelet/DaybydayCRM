const { test, expect } = require('@playwright/test');
const { loginAsAdmin, firstAppointment, usersCollection, jsonHeaders } = require('../helpers/plain-e2e');
const { BASE_URL } = require('../helpers/plain-e2e');

test('appointment calendar data returns concrete appointment details', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;

  /* Act */
  const response = await request.get(`${BASE_URL}/appointments/data`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
  const payload = await response.json();
  const firstEntry = payload[0];

  /* Assert */
  expect(response.status()).toBe(200);
  expect(Array.isArray(payload)).toBe(true);
  expect(payload.length).toBeGreaterThan(0);
  expect(firstEntry).toHaveProperty('external_id');
  expect(firstEntry).toHaveProperty('start_at');
  expect(firstEntry).toHaveProperty('end_at');
});

test('appointment updates return the changed schedule in the response body', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const appointment = await firstAppointment(request);
  const users = await usersCollection(request);
  const assignee = users.find((user) => user.external_id !== appointment.user?.external_id) ?? users[0];

  /* Act */
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

  /* Assert */
  expect(response.status()).toBe(200);
  expect(String(payload.start_at)).toContain('2030-01-02');
  expect(String(payload.end_at)).toContain('2030-01-02');
  expect(payload.user_id).toBeTruthy();
});
