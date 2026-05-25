const { test, expect } = require('@playwright/test');
const {
  BASE_URL,
  loginAsAdmin,
  createClient,
  clientData,
  jsonHeaders,
  expectValidationError,
  uniqueValue,
  usersCollection,
  html,
} = require('../helpers/plain-e2e');

test('guest is redirected from clients create route', async ({ page }) => {
  await page.goto(`${BASE_URL}/clients/create`);
  await expect(page).toHaveURL(/login/);
});

test('creating a client makes it searchable in clients data feed', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const companyName = uniqueValue('PW Client');

  const { response } = await createClient(page, request, companyName);
  const dataResponse = await clientData(request, companyName);
  const payload = await dataResponse.json();

  expect(response.status()).toBe(201);
  expect(dataResponse.status()).toBe(200);
  expect((payload.data ?? []).some((row) => row.company_name === companyName)).toBe(true);
});

test('empty client payload returns field validation errors', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const response = await request.post(`${BASE_URL}/clients`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {},
  });

  await expectValidationError(response, 'name');
});

test('client create form shows alert when submitted empty', async ({ page }) => {
  await loginAsAdmin(page);
  await page.goto(`${BASE_URL}/clients/create`);
  await page.locator('form button[type="submit"], form input[type="submit"]').first().click();
  await expect(page.locator('.alert.alert-danger, .invalid-feedback').first()).toBeVisible();
});

test('updating a client persists new contact name on detail page', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const companyName = uniqueValue('PW Client Update');
  const { payload } = await createClient(page, request, companyName);
  const clientExternalId = payload.client.external_id;
  const updatedContactName = uniqueValue('PW Contact Updated');

  const { body: editHtml } = await html(request, `/clients/${clientExternalId}/edit`);
  const industryId = editHtml.match(/name="industry_id"[^>]*>[\s\S]*?<option[^>]*value="(\d+)"/)?.[1];
  const userId = editHtml.match(/name="user_id"[^>]*>[\s\S]*?<option[^>]*value="(\d+)"/)?.[1];
  expect(industryId).toBeTruthy();
  expect(userId).toBeTruthy();

  const updateResponse = await request.patch(`${BASE_URL}/clients/${clientExternalId}`, {
    failOnStatusCode: false,
    maxRedirects: 0,
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

  const showResponse = await request.get(`${BASE_URL}/clients/${clientExternalId}`, { failOnStatusCode: false });
  const showHtml = await showResponse.text();

  expect(updateResponse.status()).toBe(302);
  expect(showResponse.status()).toBe(200);
  expect(showHtml).toContain(updatedContactName);
});

test('deleting a client removes it from clients data feed', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const companyName = uniqueValue('PW Client Delete');
  const { payload } = await createClient(page, request, companyName);
  const clientExternalId = payload.client.external_id;

  const deleteResponse = await request.delete(`${BASE_URL}/clients/${clientExternalId}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    maxRedirects: 0,
  });

  const dataResponse = await clientData(request, companyName);
  const dataPayload = await dataResponse.json();

  expect(deleteResponse.status()).toBe(302);
  expect(dataResponse.status()).toBe(200);
  expect((dataPayload.data ?? []).some((row) => row.company_name === companyName)).toBe(false);
});

test('assigning a client to a user succeeds', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { payload } = await createClient(page, request, uniqueValue('PW Client Assign'));
  const users = await usersCollection(request);
  expect(users.length).toBeGreaterThan(0);

  const response = await request.patch(`${BASE_URL}/clients/updateassign/${payload.client.external_id}`, {
    failOnStatusCode: false,
    maxRedirects: 0,
    headers: await jsonHeaders(page),
    form: { user_external_id: users[0].external_id },
  });

  expect(response.status()).toBe(302);
});
