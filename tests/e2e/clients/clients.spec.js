const { test, expect } = require('@playwright/test');
const { loginAsAdmin, createClient, clientData, jsonHeaders, expectValidationError, uniqueValue, BASE_URL, usersCollection } = require('../helpers/plain-e2e');

test('client creation shows up in the searchable clients data table', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const companyName = uniqueValue('PW Client');
  const { response } = await createClient(page, request, companyName);

  /* Act */
  const dataResponse = await clientData(request, companyName);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(response.status()).toBe(201);
  expect(dataResponse.status()).toBe(200);
  const rows = dataPayload.data || [];
  expect(rows.some(row => JSON.stringify(row).includes(companyName))).toBe(true);
});

test('client validation returns a field error instead of a generic success page', async ({ page }) => {
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

test('client assignee updates are reflected in the clients data table', async ({ page }) => {
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
  const dataResponse = await clientData(request, companyName);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(updateResponse.status()).toBe(302);
  expect(updateResponse.headers()['location'] ?? '').not.toContain('/login');
  expect(dataResponse.status()).toBe(200);
  const rows = Array.isArray(dataPayload?.data) ? dataPayload.data : [];
  const createdClientRow = rows.find(row => JSON.stringify(row).includes(companyName));
  expect(createdClientRow).toBeTruthy();
  expect(JSON.stringify(createdClientRow)).toContain(assignee.name);
});
