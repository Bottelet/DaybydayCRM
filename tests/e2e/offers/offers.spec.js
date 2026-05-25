const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createOffer, jsonHeaders } = require('../helpers/plain-e2e');

test('marking an offer as lost leaves a lost state on the owning lead page', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { leadExternalId, offerExternalId } = await createOffer(page, request);

  /* Act */
  const response = await request.post(`${BASE_URL}/offer/lost`, {
    failOnStatusCode: false,
    headers: {
      'X-CSRF-TOKEN': (await jsonHeaders(page))['X-CSRF-TOKEN'],
    },
    form: {
      offer_external_id: offerExternalId,
    },
    maxRedirects: 0,
  });
  const leadResponse = await request.get(`${BASE_URL}/leads/${leadExternalId}`, {
    failOnStatusCode: false,
  });
  const leadHtml = await leadResponse.text();

  /* Assert */
  expect(response.status()).toBe(302);
  expect(leadResponse.status()).toBe(200);
  expect(leadHtml.toLowerCase()).toContain('lost');
});

test('marking an offer as won adds an invoice link to the owning lead page', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { leadExternalId, offerExternalId } = await createOffer(page, request);

  /* Act */
  const response = await request.post(`${BASE_URL}/offer/won`, {
    failOnStatusCode: false,
    headers: {
      'X-CSRF-TOKEN': (await jsonHeaders(page))['X-CSRF-TOKEN'],
    },
    form: {
      offer_external_id: offerExternalId,
    },
    maxRedirects: 0,
  });
  const leadResponse = await request.get(`${BASE_URL}/leads/${leadExternalId}`, {
    failOnStatusCode: false,
  });
  const leadHtml = await leadResponse.text();

  /* Assert */
  expect(response.status()).toBe(302);
  expect(leadResponse.status()).toBe(200);
  expect(leadHtml).toContain('/invoices/');
});
