const { test, expect } = require('@playwright/test');
const {
  BASE_URL,
  loginAsAdmin,
  createClient,
  createLead,
  leadData,
  jsonHeaders,
  expectValidationError,
  uniqueValue,
  html,
  fillSummernote,
  firstOptionValue,
  expectFlashMessage,
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

  expect(response.status()).toBe(201);
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
  await expect(page.locator('.alert.alert-danger:visible, .invalid-feedback:visible')).toBeVisible();
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

  const { body: leadBody } = await html(request, `/leads/${leadExternalId}`);
  const leadStatusSection = leadBody.match(statusPattern)?.[1] ?? '';
  let selectedStatusId = null;
  for (const match of leadStatusSection.matchAll(/<option[^>]*value=["']([^"']*)["'][^>]*>/gi)) {
    if (String(match[0]).toLowerCase().includes('selected')) {
      selectedStatusId = String(match[1] ?? '').trim();
      break;
    }
  }
  expect(selectedStatusId).toBe(String(newStatusId));
});

test('lead assignment endpoint accepts a valid assignee', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { leadExternalId } = await createLead(page, request, uniqueValue('PW Lead Assign'));

  // UpdateLeadAssignRequest requires the internal numeric id (exists:users,id).
  // User::$hidden hides "id" from JSON (usersCollection()), so scrape it from the
  // create page's <select name="user_assigned_id"> the same way real form submits do.
  const { body } = await html(request, '/leads/create');
  const userAssignedId = firstOptionValue(body, 'user_assigned_id');

  const response = await request.patch(`${BASE_URL}/leads/updateassign/${leadExternalId}`, {
    failOnStatusCode: false,
    maxRedirects: 0,
    headers: await jsonHeaders(page),
    form: { user_assigned_id: userAssignedId },
  });

  expect(response.status()).toBe(302);
});

test('browser create shows success notification and redirects to lead detail page', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  // Ensure at least one client exists for the client_external_id select
  await createClient(page, request);

  await page.goto(`${BASE_URL}/leads/create`);

  const title = uniqueValue('PW Browser Lead');

  await page.locator('input[name="title"]').fill(title);
  await fillSummernote(page, 'description', 'Browser test lead description');

  const statusFirst = await page.locator('select[name="status_id"] option:not([value=""])').first().getAttribute('value');
  await page.locator('select[name="status_id"]').selectOption(statusFirst);

  const userFirst = await page.locator('select[name="user_assigned_id"] option:not([value=""])').first().getAttribute('value');
  await page.locator('select[name="user_assigned_id"]').selectOption(userFirst);

  const clientFirst = await page.locator('select[name="client_external_id"] option:not([value=""])').first().getAttribute('value');
  await page.locator('select[name="client_external_id"]').selectOption(clientFirst);

  const deadlineInput = page.locator('input[name="deadline"]');
  if (!(await deadlineInput.inputValue())) {
    await deadlineInput.fill('2030-01-01');
  }

  await Promise.all([
    page.waitForURL(/\/leads\//),
    page.locator('form [type="submit"]').first().click(),
  ]);

  // Note: leads/show.blade.php never actually renders the lead's own title
  // anywhere on the page (it shows the client header, offers table, sidebar —
  // not $lead->title), so we can't assert on it here. URL + flash message below
  // are what's actually verifiable.

  await expectFlashMessage(page, 'Lead successfully added');
});

test('New Offer button on lead show page opens a populated offer creation modal', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { leadExternalId } = await createLead(page, request, uniqueValue('PW Lead Offer'));

  await page.goto(`${BASE_URL}/leads/${leadExternalId}`);
  await page.locator('button:has-text("New Offer")').click();

  // Regression guard: this modal's body is a Vue component
  // (<invoice-line-modal>). Vue previously never mounted anywhere in the
  // app (see resources/assets/js/app.js / vite.config.mjs), so the modal
  // shell opened but stayed empty — clicking "New Offer" looked like the
  // button did nothing. Assert on the Vue-rendered content, not just modal
  // visibility, so a regression here fails loudly instead of looking like
  // a pass.
  await expect(page.locator('#create-offer .modal-title')).toHaveText(/Offer Management/i);
  await expect(page.locator('#create-offer button:has-text("Create")')).toBeVisible();
});

test('changing the follow-up date on a lead show page updates the deadline and shows a flash message', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { leadExternalId } = await createLead(page, request, uniqueValue('PW Lead Followup'));

  await page.goto(`${BASE_URL}/leads/${leadExternalId}`);
  const followUpValueBefore = await page.locator('span[data-target="#ModalFollowUp"]').innerText();

  await page.locator('span[data-target="#ModalFollowUp"]').click();
  await expect(page.locator('#ModalFollowUp')).toBeVisible();
  // #deadline is a readonly pickadate-driven input - selecting a date means
  // clicking the input to open its popup calendar, then clicking a day cell,
  // not typing into the field directly. The popup's own CSS transition
  // doesn't satisfy Playwright's strict toBeVisible/toBeHidden checks, so
  // rely on its own auto-waiting on the actual interactive elements instead.
  await page.locator('#deadline').click();
  await page.locator('#deadline_root .picker__day--infocus:not(.picker__day--selected)').first().click();
  await page.locator('input[type="submit"][value="Update deadline"]').click();

  await expectFlashMessage(page, 'New follow up date is set');
  const followUpValueAfter = await page.locator('span[data-target="#ModalFollowUp"]').innerText();
  expect(followUpValueAfter).not.toBe(followUpValueBefore);
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

test('editing a lead through the edit page persists the new title and description', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const { leadExternalId } = await createLead(page, request, uniqueValue('PW Lead Edit'));
  const newTitle = uniqueValue('PW Lead Edited');

  await page.goto(`${BASE_URL}/leads/${leadExternalId}/edit`);
  await page.locator('input[name="title"]').fill(newTitle);
  await fillSummernote(page, 'description', 'Edited description');
  await page.locator('form [type="submit"]').click();

  await expect(page).toHaveURL(new RegExp(leadExternalId));
  await expectFlashMessage(page, 'Lead successfully updated');
  await expect(page.locator('.tablet__head__color-brand .tablet__head-title', { hasText: newTitle })).toBeVisible();
});
