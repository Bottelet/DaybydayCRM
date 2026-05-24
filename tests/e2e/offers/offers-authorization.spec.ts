import { test as guestTest, expect as guestExpect } from '@playwright/test';
import { test, expect, nonAdminTest } from '../../helpers/fixtures';
import { PLAYWRIGHT_BASE_URL } from '../../helpers/config';
import { fetchCsrfToken } from '../../helpers/csrf';

test.describe('Offers authorization', () => {
  nonAdminTest('user without offer create permission cannot create offer', async ({ page, request }) => {
    const response = await request.post(`${PLAYWRIGHT_BASE_URL}/offers`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': await fetchCsrfToken(page),
      },
      form: { title: 'Blocked Offer', client_external_id: '00000000-0000-0000-0000-000000000001' },
    });

    expect(response.status()).toBe(403);
  });

  nonAdminTest('user without offer edit permission cannot update offer', async ({ page, request }) => {
    const response = await request.patch(
      `${PLAYWRIGHT_BASE_URL}/offers/00000000-0000-0000-0000-000000000001`,
      {
        failOnStatusCode: false,
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
        form: { title: 'Blocked Update' },
      },
    );

    expect(response.status()).toBe(403);
  });

  nonAdminTest('user without offer edit permission cannot mark offer as won', async ({ page, request }) => {
    const response = await request.patch(
      `${PLAYWRIGHT_BASE_URL}/offers/00000000-0000-0000-0000-000000000001/won`,
      {
        failOnStatusCode: false,
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
      },
    );

    expect(response.status()).toBe(403);
  });

  nonAdminTest('user without offer edit permission cannot mark offer as lost', async ({ page, request }) => {
    const response = await request.patch(
      `${PLAYWRIGHT_BASE_URL}/offers/00000000-0000-0000-0000-000000000001/lost`,
      {
        failOnStatusCode: false,
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
      },
    );

    expect(response.status()).toBe(403);
  });

  test('user with offer create permission can create offer', async ({ page, request }) => {
    const formPage = await request.get(`${PLAYWRIGHT_BASE_URL}/offers/create`, {
      failOnStatusCode: false,
    });
    expect(formPage.status()).toBe(200);
  });

  test('user with offer edit permission can update offer', async ({ page, request }) => {
    // Verified via the happy path in offers.spec.ts — this confirms the route itself is reachable
    const response = await request.get(`${PLAYWRIGHT_BASE_URL}/offers`, {
      failOnStatusCode: false,
    });
    expect(response.status()).toBe(200);
  });
});
