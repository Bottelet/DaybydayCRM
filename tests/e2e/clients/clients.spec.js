const { test, expect } = require('@playwright/test');
const {
  loginAsAdmin, createClient, clientData, jsonHeaders,
  expectValidationError, uniqueValue, BASE_URL, usersCollection, html,
} = require('../helpers/plain-e2e');

test('creating a client registers the company in the searchable clients data table', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const companyName = uniqueValue('PW Client');

  /* Act */
  const { response } = await createClient(page, request, companyName);
  const dataResponse = await clientData(request, companyName);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(response.status(), 'Client creation should return 201').toBe(201);
  expect(dataResponse.status(), 'Clients data table should return 200').toBe(200);
  const rows = dataPayload.data || [];
  expect(
    rows.some(row => row.company_name === companyName),
    `Newly created company "${companyName}" should appear by exact name in the data table`
  ).toBe(true);
});

test('submitting a client form without required fields returns field-level validation errors', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;

  /* Act */
  const response = await request.post(`${BASE_URL}/clients`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {},
  });

  /* Assert */
  await expectValidationError(response, 'name');
});

test('updating a client contact name is reflected on the client detail page', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const companyName = uniqueValue('PW Client Update');
  const { payload } = await createClient(page, request, companyName);
  const clientExternalId = payload.client.external_id;
  const updatedContactName = uniqueValue('PW Contact Updated');

  /* Fetch current client edit data to obtain IDs needed for update */
  const { body: editHtml } = await html(request, `/clients/${clientExternalId}/edit`);
  const industryIdMatch = editHtml.match(/name="industry_id"[^>]*>[\s\S]*?<option[^>]*value="(\d+)"/);
  const industryId = industryIdMatch ? industryIdMatch[1] : '1';
  const userIdMatch = editHtml.match(/name="user_id"[^>]*>[\s\S]*?<option[^>]*value="(\d+)"/);
  const userId = userIdMatch ? userIdMatch[1] : '1';

  /* Act */
  const updateResponse = await request.patch(`${BASE_URL}/clients/${clientExternalId}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {
      name: updatedContactName,
      company_name: companyName,
      email: `pw_updated_${Date.now()}@example.com`,
      primary_number: '11111111',
      secondary_number: '22222222',
      vat: `${Date.now()}`.slice(-8),
      zipcode: '2000',
      city: 'UpdatedCity',
      industry_id: industryId,
      user_id: userId,
    },
  });

  /* Verify update by checking the client show page for the updated contact name */
  const showResponse = await request.get(`${BASE_URL}/clients/${clientExternalId}`, {
    failOnStatusCode: false,
  });
  const showHtml = await showResponse.text();

  /* Assert */
  expect(updateResponse.status(), 'Client update should return a 302 redirect').toBe(302);
  expect(updateResponse.headers()['location'] ?? '', 'Redirect should not point to login').not.toContain('/login');
  expect(showResponse.status(), 'Client show page should return 200').toBe(200);
  expect(showHtml, 'Updated contact name should be visible on the client detail page').toContain(updatedContactName);
});

test('assigning a new user to a client redirects back without hitting the login page', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const companyName = uniqueValue('PW Client Assign');
  const { payload } = await createClient(page, request, companyName);
  const clientExternalId = payload.client.external_id;
  const users = await usersCollection(request);
  const assignee = users[0];

  /* Act */
  const updateResponse = await request.patch(`${BASE_URL}/clients/updateassign/${clientExternalId}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {
      user_external_id: assignee.external_id,
    },
  });

  /* Assert – the clients data table does not expose the assigned user field, so we
     verify the business rule at the HTTP level: a successful update redirects back
     (not to /login), confirming the operation was authorised and processed. */
  expect(updateResponse.status(), 'Assignee update should return 302').toBe(302);
  expect(updateResponse.headers()['location'] ?? '', 'Redirect should not point to login').not.toContain('/login');
});

test('deleting a client removes the company from the clients data table', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const companyName = uniqueValue('PW Client Delete');
  const { payload } = await createClient(page, request, companyName);
  const clientExternalId = payload.client.external_id;

  /* Act */
  const deleteResponse = await request.delete(`${BASE_URL}/clients/${clientExternalId}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    maxRedirects: 0,
  });
  const dataResponse = await clientData(request, companyName);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(deleteResponse.status(), 'Client deletion should return a 302 redirect').toBe(302);
  expect(deleteResponse.headers()['location'] ?? '', 'Delete redirect must not point to login').not.toContain('/login');
  expect(dataResponse.status(), 'Clients data table should return 200 after delete').toBe(200);
  const rows = dataPayload.data || [];
  expect(
    rows.some(row => row.company_name === companyName),
    `Deleted client "${companyName}" must not appear in the data table`
  ).toBe(false);
});
