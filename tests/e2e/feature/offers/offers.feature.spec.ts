import { test, expect, nonAdminTest } from '../../helpers/fixtures';
import { PLAYWRIGHT_BASE_URL } from '../../helpers/config';
import { createAdminSession } from '../../helpers/session-context';
import { createOfferFixture } from '../../helpers/coverage-fixtures';
import { fetchCsrfToken } from '../../helpers/csrf';

test.describe('Offers feature behavior', () => {
  test('creates, updates, wins, and loses offers', async ({ page, request }) => {
    const { leadExternalId, offerExternalId } = await createOfferFixture(page, request);

    const updateResponse = await request.post(`${PLAYWRIGHT_BASE_URL}/offer/${offerExternalId}/update`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': await fetchCsrfToken(page),
      },
      data: [
        {
          title: 'Updated Offer Line',
          type: 'hours',
          price: 200,
          quantity: 2,
          comment: 'Updated by Playwright',
        },
      ],
    });
    expect(updateResponse.status()).toBe(200);

    const lostResponse = await request.post(`${PLAYWRIGHT_BASE_URL}/offer/lost`, {
      failOnStatusCode: false,
      headers: {
        'X-CSRF-TOKEN': await fetchCsrfToken(page),
      },
      form: {
        offer_external_id: offerExternalId,
      },
      maxRedirects: 0,
    });
    expect(lostResponse.status()).toBe(302);

    const lostLeadResponse = await request.get(`${PLAYWRIGHT_BASE_URL}/leads/${leadExternalId}`, {
      failOnStatusCode: false,
    });
    const lostLeadHtml = await lostLeadResponse.text();
    expect(lostLeadHtml.toLowerCase()).toContain('lost');

    const freshOffer = await createOfferFixture(page, request);
    const wonResponse = await request.post(`${PLAYWRIGHT_BASE_URL}/offer/won`, {
      failOnStatusCode: false,
      headers: {
        'X-CSRF-TOKEN': await fetchCsrfToken(page),
      },
      form: {
        offer_external_id: freshOffer.offerExternalId,
      },
      maxRedirects: 0,
    });
    expect(wonResponse.status()).toBe(302);

    const wonLeadHtml = await (
      await request.get(`${PLAYWRIGHT_BASE_URL}/leads/${freshOffer.leadExternalId}`, {
        failOnStatusCode: false,
      })
    ).text();
    expect(wonLeadHtml).toContain('/invoices/');
  });
});

nonAdminTest.describe('Offers permissions', () => {
  nonAdminTest('denies offer create, update, win, and lose actions', async ({ page, request }) => {
    const admin = await createAdminSession(page);

    try {
      const { leadExternalId, offerExternalId } = await createOfferFixture(admin.page, admin.request);

      const createResponse = await request.post(`${PLAYWRIGHT_BASE_URL}/offers/create/${leadExternalId}`, {
        failOnStatusCode: false,
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
        data: [{ title: 'Forbidden Offer', type: 'hours', price: 10, quantity: 1 }],
      });
      expect(createResponse.status()).toBe(403);

      const updateResponse = await request.post(`${PLAYWRIGHT_BASE_URL}/offer/${offerExternalId}/update`, {
        failOnStatusCode: false,
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
        data: [{ title: 'Forbidden Update', type: 'hours', price: 10, quantity: 1 }],
      });
      expect(updateResponse.status()).toBe(403);

      const wonResponse = await request.post(`${PLAYWRIGHT_BASE_URL}/offer/won`, {
        failOnStatusCode: false,
        headers: {
          'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
        form: { offer_external_id: offerExternalId },
        maxRedirects: 0,
      });
      expect(wonResponse.status()).toBe(403);

      const lostResponse = await request.post(`${PLAYWRIGHT_BASE_URL}/offer/lost`, {
        failOnStatusCode: false,
        headers: {
          'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
        form: { offer_external_id: offerExternalId },
        maxRedirects: 0,
      });
      expect(lostResponse.status()).toBe(403);
    } finally {
      await admin.dispose();
    }
  });
});
