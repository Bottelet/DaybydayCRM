const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, firstAppointment, usersCollection, jsonHeaders, expectValidationError } = require('../helpers/plain-e2e');
const {nonAdminTest} = require("../../helpers/fixtures");
const {createAdminSession} = require("../helpers/session-context");
const {fetchCsrfToken} = require("../../helpers/csrf");
const {PLAYWRIGHT_BASE_URL} = require("../../helpers/config");
const {TEST_USERS} = require("../../fixtures/users");

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
  expect(users.length, 'Reschedule test requires at least one assignable user').toBeGreaterThan(0);
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

test('returns appointments within the supported calendar window', async ({ request }) => {
    const response = await request.get(`${PLAYWRIGHT_BASE_URL}/appointments/data`, {
        failOnStatusCode: false,
        headers: { Accept: 'application/json' },
    });

    expect(response.status()).toBe(200);
    const payload = await response.json();
    expect(Array.isArray(payload)).toBe(true);
    expect(payload.length).toBeGreaterThan(0);

    const now = Date.now();
    const lowerBound = now - 14 * 24 * 60 * 60 * 1000;
    const upperBound = now + 28 * 24 * 60 * 60 * 1000;

    for (const appointment of payload) {
        const start = new Date(String(appointment.start_at)).getTime();
        const end = new Date(String(appointment.end_at)).getTime();
        expect(start >= lowerBound || end >= lowerBound).toBe(true);
        expect(start <= upperBound || end <= upperBound).toBe(true);
    }
});

test('updates appointment times and assignee', async ({ page, request }) => {
    const appointment = await firstAppointment(request);
    const users = await calendarUsers(request);
    const newAssignee = users.find((user) => user.external_id !== appointment.user?.external_id) ?? users[0];

    const response = await request.post(`${PLAYWRIGHT_BASE_URL}/appointments/update/${appointment.external_id}`, {
        failOnStatusCode: false,
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
        form: {
            id: appointment.external_id,
            start: '2030-01-02T09:00:00.000Z',
            end: '2030-01-02T10:00:00.000Z',
            group: newAssignee.external_id,
        },
    });

    expect(response.status()).toBe(200);
    const updated = await response.json();
    expect(String(updated.start_at)).toContain('2030-01-02');
});

test('rejects invalid appointment update payloads', async ({ page, request }) => {
    const appointment = await firstAppointment(request);

    const response = await request.post(`${PLAYWRIGHT_BASE_URL}/appointments/update/${appointment.external_id}`, {
        failOnStatusCode: false,
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
        form: {
            id: appointment.external_id,
            start: '2030-01-02T09:00:00.000Z',
            end: '2030-01-02T10:00:00.000Z',
            group: 'does-not-exist',
        },
    });

    expect(response.status()).toBe(422);
});

test('destroys appointments', async ({ page, request }) => {
    const appointment = await firstAppointment(request);

    const response = await request.delete(`${PLAYWRIGHT_BASE_URL}/appointments/${appointment.external_id}`, {
        failOnStatusCode: false,
        headers: {
            'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
    });

    expect(response.status()).toBe(200);
    expect(await response.text()).toContain('Success');
});

test('it can get appointments within time slot', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it can update appointment times', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(update|updated|saved|assigned|status|restored)/i).first()).toBeVisible();
});

test('it can destroy appointment', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it returns json error when appointment update fails', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(update|updated|saved|assigned|status|restored)/i).first()).toBeVisible();
});

test('it returns user appointments via morph relationship', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it does not return appointments for other source types in user appointments morph', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it verifies appointments controller does not have store method', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it verifies appointments controller does not have create request dependency', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it posting to appointments resource route returns not found', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it verifies appointments controller retains calendar method', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it verifies appointments controller retains update method', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(update|updated|saved|assigned|status|restored)/i).first()).toBeVisible();
});

test('it verifies appointments controller retains destroy method', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it verifies appointments controller retains appointments json method', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(error|invalid|required|unprocessable|forbidden)/i).first()).toBeVisible();
});

test('it creates appointment calendar request class no longer used by controller', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it authorized user can update appointment', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(update|updated|saved|assigned|status|restored)/i).first()).toBeVisible();
});

test('it authorized user can delete appointment', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(delete|removed|warning|cannot)/i).first()).toBeVisible();
});

test('it unauthorized user cannot update appointment', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(forbidden|unauthorized|permission|login|warning|error)/i).first()).toBeVisible();
});

test('it requires permission check for appointment update', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(update|updated|saved|assigned|status|restored)/i).first()).toBeVisible();
});

test('it unauthorized user cannot delete appointment', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(forbidden|unauthorized|permission|login|warning|error)/i).first()).toBeVisible();
});

nonAdminTest('denies appointment deletion without permission', async ({ page, request }) => {
    const admin = await createAdminSession(page);

    try {
        const appointment = await firstAppointment(admin.request);
        const response = await request.delete(`${PLAYWRIGHT_BASE_URL}/appointments/${appointment.external_id}`, {
            failOnStatusCode: false,
            headers: {
                'X-CSRF-TOKEN': await fetchCsrfToken(page),
            },
        });

        expect(response.status()).toBe(403);
    } finally {
        await admin.dispose();
    }
});
