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
  expect(JSON.stringify(dataPayload)).toContain(title);
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
      'X-CSRF-TOKEN': await require('../helpers/plain-e2e').jsonHeaders(page).then((headers) => headers['X-CSRF-TOKEN']),
    },
  });
  const dataResponse = await leadData(request, title);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(deleteResponse.status()).toBe(200);
  expect(JSON.stringify(dataPayload)).not.toContain(title);
});
