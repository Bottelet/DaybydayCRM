const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, firstAppointment, usersCollection, jsonHeaders, expectValidationError } = require('../helpers/plain-e2e');

test('the appointments calendar feed returns structured appointment records with time boundaries', async ({ page }) => {
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
  expect(response.status(), 'Appointments data endpoint should return 200').toBe(200);
  expect(Array.isArray(payload), 'Response should be an array of appointments').toBe(true);
  expect(
    payload.length,
    'At least one appointment must exist within the ±6-week window. Run the demo seeder (php artisan db:seed) if this fails.'
  ).toBeGreaterThan(0);
  expect(firstEntry).toHaveProperty('external_id');
  expect(firstEntry).toHaveProperty('start_at');
  expect(firstEntry).toHaveProperty('end_at');
});

test('rescheduling an appointment stores the new start and end times in the response', async ({ page }) => {
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
  expect(response.status(), 'Appointment update should return 200').toBe(200);
  expect(String(payload.start_at), 'Updated start_at should reflect the new date').toContain('2030-01-02');
  expect(String(payload.end_at), 'Updated end_at should reflect the new date').toContain('2030-01-02');
  expect(payload.user_id, 'Updated appointment should have an assigned user').toBeTruthy();
});

test('updating an appointment without a required assignee returns a validation error on the group field', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const appointment = await firstAppointment(request);

  /* Act */
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

  /* Assert – the group (assignee) field is required for a valid reschedule */
  await expectValidationError(response, 'group');
});

test('deleting an appointment removes it from the calendar feed', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const appointment = await firstAppointment(request);
  const appointmentExternalId = appointment.external_id;

  /* Act */
  const deleteResponse = await request.delete(`${BASE_URL}/appointments/${appointmentExternalId}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
  });
  const afterResponse = await request.get(`${BASE_URL}/appointments/data`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
  const afterPayload = await afterResponse.json();

  /* Assert */
  expect(deleteResponse.status(), 'Delete endpoint should return a success response').toBe(200);
  expect(afterResponse.status(), 'Appointments feed after delete should return 200').toBe(200);
  expect(
    Array.isArray(afterPayload) && afterPayload.some(a => a.external_id === appointmentExternalId),
    `Deleted appointment ${appointmentExternalId} should not appear in the calendar feed`
  ).toBe(false);
});
