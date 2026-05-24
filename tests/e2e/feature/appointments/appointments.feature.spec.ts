import { test, expect, nonAdminTest } from '../../helpers/fixtures';
import { PLAYWRIGHT_BASE_URL } from '../../helpers/config';
import { createAdminSession } from '../../helpers/session-context';
import { calendarUsers, firstAppointment } from '../../helpers/coverage-fixtures';
import { fetchCsrfToken } from '../../helpers/csrf';

test.describe('Appointments feature behavior', () => {
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
});

nonAdminTest.describe('Appointments permissions', () => {
  nonAdminTest('denies calendar view without permission', async ({ request }) => {
    const response = await request.get(`${PLAYWRIGHT_BASE_URL}/appointments/calendar`, {
      failOnStatusCode: false,
      maxRedirects: 0,
    });

    expect(response.status()).toBe(302);
  });

  nonAdminTest('denies appointment updates without permission', async ({ page, request }) => {
    const admin = await createAdminSession(page);

    try {
      const appointment = await firstAppointment(admin.request);
      const users = await calendarUsers(admin.request);
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
          group: users[0].external_id,
        },
      });

      expect(response.status()).toBe(403);
    } finally {
      await admin.dispose();
    }
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
});
