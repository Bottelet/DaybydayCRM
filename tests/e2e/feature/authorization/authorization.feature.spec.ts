import { expect } from '@playwright/test';
import { nonAdminTest } from '../../helpers/fixtures';
import { createAdminSession } from '../../helpers/session-context';
import { createLeadFixture } from '../../helpers/coverage-fixtures';
import { PLAYWRIGHT_BASE_URL } from '../../helpers/config';

nonAdminTest.describe('Authorization coverage', () => {
  nonAdminTest('denies client creation', async ({ page, request }) => {
    const response = await request.post(`${PLAYWRIGHT_BASE_URL}/clients`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      form: {},
    });

    expect(response.status()).toBe(403);
  });

  nonAdminTest('denies lead creation', async ({ request }) => {
    const response = await request.post(`${PLAYWRIGHT_BASE_URL}/leads`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      form: {},
    });

    expect(response.status()).toBe(403);
  });

  nonAdminTest('denies project creation', async ({ request }) => {
    const response = await request.post(`${PLAYWRIGHT_BASE_URL}/projects`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      form: {},
    });

    expect(response.status()).toBe(403);
  });

  nonAdminTest('denies task creation', async ({ request }) => {
    const response = await request.post(`${PLAYWRIGHT_BASE_URL}/tasks`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      form: {},
    });

    expect(response.status()).toBe(403);
  });

  nonAdminTest('denies role creation', async ({ request }) => {
    const response = await request.post(`${PLAYWRIGHT_BASE_URL}/roles`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      form: {},
    });

    expect(response.status()).toBe(403);
  });

  nonAdminTest('denies user creation', async ({ request }) => {
    const response = await request.post(`${PLAYWRIGHT_BASE_URL}/users`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      form: {},
    });

    expect(response.status()).toBe(403);
  });

  nonAdminTest('denies offer creation', async ({ page, request }) => {
    const admin = await createAdminSession(page);

    try {
      const { leadExternalId } = await createLeadFixture(admin.page, admin.request);
      const response = await request.post(`${PLAYWRIGHT_BASE_URL}/offers/create/${leadExternalId}`, {
        failOnStatusCode: false,
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        data: [
          {
            title: 'Forbidden Offer',
            type: 'hours',
            price: 50,
            quantity: 1,
          },
        ],
      });

      expect(response.status()).toBe(403);
    } finally {
      await admin.dispose();
    }
  });
});
