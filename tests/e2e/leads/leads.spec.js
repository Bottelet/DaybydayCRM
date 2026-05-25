const { test, expect } = require('@playwright/test');
const {
  BASE_URL,
  loginAsAdmin,
  createLead,
  leadData,
  jsonHeaders,
  expectValidationError,
  uniqueValue,
  usersCollection,
  html,
} = require('../helpers/plain-e2e');

test('guest is redirected from leads create route', async ({ page }) => {
  await page.goto(`${BASE_URL}/leads/create`);
  await expect(page).toHaveURL(/login/);
});

test('creating a lead makes it searchable in leads data feed', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const title = uniqueValue('PW Lead');

  const { response } = await createLead(page, request, title);
  const dataResponse = await leadData(request, title);
  const payload = await dataResponse.json();

  expect(response.status()).toBe(302);
  expect(dataResponse.status()).toBe(200);
  expect((payload.data ?? []).some((row) => row.title === title)).toBe(true);
});

test('empty lead payload returns field validation errors', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const response = await request.post(`${BASE_URL}/leads`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {},
  });

  await expectValidationError(response, 'title');
});

test('lead create form shows alert when submitted empty', async ({ page }) => {
  await loginAsAdmin(page);
  await page.goto(`${BASE_URL}/leads/create`);
  await page.locator('form button[type="submit"], form input[type="submit"]').first().click();
  await expect(page.locator('form >> .alert.alert-danger:visible, form >> .invalid-feedback:visible')).toBeVisible();
});

test('lead status update endpoint accepts a valid status transition', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { leadExternalId, statusId } = await createLead(page, request, uniqueValue('PW Lead Status'));

  const { body } = await html(request, '/leads/create');
  const statusPattern = /<select[^>]*name=["']status_id["'][^>]*>([\s\S]*?)<\/select>/i;
  const statusSection = body.match(statusPattern)?.[1] ?? '';
  const allStatuses = [];
  for (const match of statusSection.matchAll(/<option[^>]*value=["']([^"']*)["'][^>]*>/gi)) {
    const value = String(match[1] ?? '').trim();
    if (value) {
      allStatuses.push(value);
    }
  }
  expect(allStatuses.length).toBeGreaterThan(1);
  const newStatusId = allStatuses.find((id) => id !== statusId) ?? allStatuses[1];
  const leadPath = `${BASE_URL}/leads/${leadExternalId}`;

  const response = await request.patch(`${BASE_URL}/leads/updatestatus/${leadExternalId}`, {
    failOnStatusCode: false,
    maxRedirects: 0,
    headers: await jsonHeaders(page, { Referer: leadPath }),
    form: { status_id: newStatusId },
  });

  expect(response.status()).toBe(302);
  expect(response.headers().location ?? '').toContain(leadPath);
});

test('lead assignment endpoint accepts a valid assignee', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { leadExternalId } = await createLead(page, request, uniqueValue('PW Lead Assign'));
  const users = await usersCollection(request);
  expect(users.length).toBeGreaterThan(0);

  const response = await request.patch(`${BASE_URL}/leads/updateassign/${leadExternalId}`, {
    failOnStatusCode: false,
    maxRedirects: 0,
    headers: await jsonHeaders(page),
    form: { user_assigned_id: users[0].external_id },
  });

  expect(response.status()).toBe(302);
});

test('deleting a lead through json endpoint removes it from lead feed', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const title = uniqueValue('PW Lead Delete');
  const { leadExternalId } = await createLead(page, request, title);

  const deleteResponse = await request.delete(`${BASE_URL}/leads/${leadExternalId}/json`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
  });

  const dataResponse = await leadData(request, title);
  const payload = await dataResponse.json();

  expect(deleteResponse.status()).toBe(200);
  expect(dataResponse.status()).toBe(200);
  expect((payload.data ?? []).some((row) => row.title === title)).toBe(false);
});
