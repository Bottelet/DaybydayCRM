const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createLead, leadData, jsonHeaders, uniqueValue, expectValidationError, usersCollection } = require('../helpers/plain-e2e');

test('creating a lead registers the title in the searchable lead data response', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const title = uniqueValue('PW Lead');

  /* Act */
  const { response } = await createLead(page, request, title);
  const dataResponse = await leadData(request, title);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(response.status(), 'Lead creation should return 302 redirect to the new lead').toBe(302);
  expect(response.headers()['location'] ?? '', 'Redirect should point to /leads/').toContain('/leads/');
  expect(dataResponse.status(), 'Lead data feed should return 200').toBe(200);
  const rows = Array.isArray(dataPayload?.data) ? dataPayload.data : [];
  expect(
    rows.some(row => row.title === title),
    `Newly created lead "${title}" should appear by exact title in the data response`
  ).toBe(true);
});

test('deleting a lead removes its title from the lead data response', async ({ page }) => {
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
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(deleteResponse.status(), 'Lead delete endpoint should return 200').toBe(200);
  expect(dataResponse.status(), 'Lead data feed should return 200 after delete').toBe(200);
  const rows = Array.isArray(dataPayload?.data) ? dataPayload.data : [];
  expect(
    rows.some(row => row.title === title),
    `Deleted lead "${title}" must not appear in the lead data response`
  ).toBe(false);
});

test('submitting a lead form without required fields returns a title field validation error', async ({ page }) => {
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

test('updating a lead status redirects back to the lead without triggering a login redirect', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { leadExternalId, statusId } = await createLead(page, request, uniqueValue('PW Lead Status'));

  /* Act */
  const response = await request.patch(`${BASE_URL}/leads/updatestatus/${leadExternalId}`, {
    failOnStatusCode: false,
    maxRedirects: 0,
    headers: await jsonHeaders(page),
    form: {
      status_id: statusId,
    },
  });

  /* Assert */
  expect(response.status(), 'Lead status update should return 302').toBe(302);
  expect(response.headers()['location'] ?? '', 'Redirect must not send the user back to login').not.toContain('/login');
});

test('reassigning a lead to a new user redirects back without triggering a login redirect', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { leadExternalId } = await createLead(page, request, uniqueValue('PW Lead Assign'));
  const users = await usersCollection(request);
  const newAssignee = users[0];

  /* Act */
  const response = await request.patch(`${BASE_URL}/leads/updateassign/${leadExternalId}`, {
    failOnStatusCode: false,
    maxRedirects: 0,
    headers: await jsonHeaders(page),
    form: {
      user_assigned_id: newAssignee.external_id,
    },
  });

  /* Assert */
  expect(response.status(), 'Lead assignee update should return 302').toBe(302);
  expect(response.headers()['location'] ?? '', 'Redirect must not send the user to login').not.toContain('/login');
});
