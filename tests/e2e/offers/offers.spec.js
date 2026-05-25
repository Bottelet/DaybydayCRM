const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createOffer, jsonHeaders } = require('../helpers/plain-e2e');

test('creating offer lines returns success for authenticated admin', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { response } = await createOffer(page, request);

  expect(response.status()).toBe(200);
});

test('updating an offer line is reflected in offer invoice-lines json', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { response: createResponse, offerExternalId } = await createOffer(page, request);
  const updatedTitle = `Playwright Updated Offer ${Date.now()}`;

  const updateResponse = await request.post(`${BASE_URL}/offer/${offerExternalId}/update`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    data: [{ title: updatedTitle, type: 'hours', price: 75, quantity: 2, comment: 'updated line' }],
  });

  const linesResponse = await request.get(`${BASE_URL}/offer/${offerExternalId}/invoice-lines/json`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
  const lines = await linesResponse.json();

  expect(createResponse.status()).toBe(200);
  expect(updateResponse.status()).toBe(200);
  expect(linesResponse.status()).toBe(200);
  expect(Array.isArray(lines)).toBe(true);
  expect(lines.some((line) => line.title === updatedTitle)).toBe(true);
});

test('marking an offer as won creates invoice linkage on lead page', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { leadExternalId, offerExternalId } = await createOffer(page, request);

  const wonResponse = await request.post(`${BASE_URL}/offer/won`, {
    failOnStatusCode: false,
    headers: { 'X-CSRF-TOKEN': (await jsonHeaders(page))['X-CSRF-TOKEN'] },
    form: { offer_external_id: offerExternalId },
    maxRedirects: 0,
  });

  const leadResponse = await request.get(`${BASE_URL}/leads/${leadExternalId}`, { failOnStatusCode: false });
  const leadHtml = await leadResponse.text();

  expect(wonResponse.status()).toBe(302);
  expect(leadResponse.status()).toBe(200);
  expect(leadHtml).toContain('/invoices/');
});

test('marking an offer as lost changes lead view state to lost', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { leadExternalId, offerExternalId } = await createOffer(page, request);

  const lostResponse = await request.post(`${BASE_URL}/offer/lost`, {
    failOnStatusCode: false,
    headers: { 'X-CSRF-TOKEN': (await jsonHeaders(page))['X-CSRF-TOKEN'] },
    form: { offer_external_id: offerExternalId },
    maxRedirects: 0,
  });

  const leadResponse = await request.get(`${BASE_URL}/leads/${leadExternalId}`, { failOnStatusCode: false });
  const leadHtml = await leadResponse.text();

  expect(lostResponse.status()).toBe(302);
  expect(leadResponse.status()).toBe(200);
  expect(leadHtml.toLowerCase()).toContain('lost');
});

test('offer line update rejects missing title', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { offerExternalId } = await createOffer(page, request);

  const response = await request.post(`${BASE_URL}/offer/${offerExternalId}/update`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    data: [{ type: 'hours', price: 20, quantity: 1 }],
  });

  expect(response.status()).toBe(422);
  expect((await response.text()).toLowerCase()).toContain('missing fields');
});
