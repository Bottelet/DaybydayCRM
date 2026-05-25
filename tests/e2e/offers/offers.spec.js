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

test('offer lines can be updated and are returned by the offer invoice-line json endpoint', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { response: createResponse, offerExternalId } = await createOffer(page, request);
  const updatedTitle = `Playwright Updated Offer ${Date.now()}`;

  /* Act */
  const updateResponse = await request.post(`${BASE_URL}/offer/${offerExternalId}/update`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    data: [
      {
        type: 'hours',
        price: 75,
        quantity: 2,
        comment: 'updated line',
      },
    ],
  });
  const linesResponse = await request.get(`${BASE_URL}/offer/${offerExternalId}/invoice-lines/json`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
  const lines = await linesResponse.json();

  /* Assert */
  expect(createResponse.status()).toBe(200);
  expect(updateResponse.status()).toBe(200);
  expect(linesResponse.status()).toBe(200);
  expect(Array.isArray(lines)).toBe(true);
  expect(lines.some(line => line.title === updatedTitle)).toBe(true);
});

test('offer line updates reject missing required line fields', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { offerExternalId } = await createOffer(page, request);

  /* Act */
  const response = await request.post(`${BASE_URL}/offer/${offerExternalId}/update`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    data: [
      {
        type: 'hours',
        price: 20,
        quantity: 1,
      },
    ],
  });
  const body = await response.text();

  /* Assert */
  expect(response.status()).toBe(422);
  expect(body.toLowerCase()).toContain('missing fields');
});
