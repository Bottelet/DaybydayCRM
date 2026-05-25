const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createLead, leadData, jsonHeaders, uniqueValue, expectValidationError } = require('../helpers/plain-e2e');

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
  const rows = Array.isArray(dataPayload?.data) ? dataPayload.data : [];
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
  expect(dataResponse.status()).toBe(200);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(deleteResponse.status()).toBe(200);
  const rows = Array.isArray(dataPayload?.data) ? dataPayload.data : [];
  expect(JSON.stringify(rows)).not.toContain(title);
});

test('lead validation reports a missing required title field', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;

  /* Act */
  const response = await request.post(`${BASE_URL}/leads`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {},
  });

  /* Assert */
  await expectValidationError(response, 'title');
});

test('lead status updates return a redirect for an existing lead', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { leadExternalId, statusId } = await createLead(page, request, uniqueValue('PW Lead Status'));

  /* Act */
  const response = await request.patch(`${BASE_URL}/leads/updatestatus/${leadExternalId}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {
      status_id: statusId,
    },
  });

  /* Assert */
  expect(response.status()).toBe(302);
  expect(response.headers()['location'] ?? '').not.toContain('/login');
});
