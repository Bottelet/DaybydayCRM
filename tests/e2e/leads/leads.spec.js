const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createLead, leadData, jsonHeaders, uniqueValue } = require('../helpers/plain-e2e');

test('lead creation appears in the searchable lead data response', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const title = uniqueValue('PW Lead');
  const { response } = await createLead(page, request, title);

  /* Act */
  const dataResponse = await leadData(request, title);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(response.status()).toBe(302);
  expect(response.headers()['location'] ?? '').toContain('/leads/');
  expect(dataResponse.status()).toBe(200);
  const rows = dataPayload.data || [];
  expect(rows.some(row => row.title === title)).toBe(true);
});

test('deleting a lead removes it from the lead data response', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const title = uniqueValue('PW Lead Delete');
  const { leadExternalId } = await createLead(page, request, title);

  /* Act */
  const deleteResponse = await request.delete(`${BASE_URL}/leads/${leadExternalId}/json`, {
    failOnStatusCode: false,
    headers: {
      Accept: 'application/json',
      'X-CSRF-TOKEN': (await jsonHeaders(page))['X-CSRF-TOKEN'],
    },
  });
  const dataResponse = await leadData(request, title);
  const dataResponse = await leadData(request, title);
  expect(dataResponse.status()).toBe(200);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(deleteResponse.status()).toBe(200);
  const rows = Array.isArray(dataPayload?.data) ? dataPayload.data : [];
  expect(JSON.stringify(rows)).not.toContain(title);
});
