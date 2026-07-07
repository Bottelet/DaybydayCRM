const { expect } = require('@playwright/test');
const { execSync } = require('child_process');
const path = require('path');

const BASE_URL = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost';
const ADMIN_EMAIL = process.env.PLAYWRIGHT_ADMIN_EMAIL ?? 'admin@admin.com';
const ADMIN_PASSWORD = process.env.PLAYWRIGHT_ADMIN_PASSWORD ?? 'admin123';
const DEFAULT_PASSWORD = process.env.PLAYWRIGHT_TEST_USER_PASSWORD ?? 'amazingpassword123';

function uniqueValue(prefix) {
  return `${prefix} ${Date.now()} ${Math.random().toString(36).slice(2, 8)}`;
}

function escapeRegExp(value) {
  return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

async function loginAsAdmin(page) {
  await page.goto(`${BASE_URL}/login`);
  await page.locator('input[name="email"]').fill(ADMIN_EMAIL);
  await page.locator('input[name="password"]').fill(ADMIN_PASSWORD);
  await Promise.all([
    page.waitForURL((url) => !url.pathname.endsWith('/login')),
    page.getByRole('button', { name: /log ?in|sign ?in/i }).click(),
  ]);
  await page.waitForLoadState('networkidle');
}

/**
 * Fill a Summernote-backed description field.
 *
 * Summernote hides the original textarea (display:none) and replaces it with
 * a contenteditable ".note-editable" div inside a sibling ".note-editor"
 * container, so a plain page.locator(`textarea[name="${name}"]`).fill(...)
 * times out waiting for an invisible element.
 */
async function fillSummernote(page, name, text) {
  await page.locator(`textarea[name="${name}"] + .note-editor .note-editable`).fill(text);
}

/**
 * Safety-net: dismiss the Bootstrap tour overlay if it is visible.
 *
 * The global setup (playwright.config.ts → globalSetup) pre-sets the tour
 * dismissal cookies for every browser context, so under normal test runs the
 * tour will never appear and this function is a no-op. It stays here as a
 * fallback for environments where cookies are cleared between steps, or where
 * TOUR_DISABLED=true is not set on the server.
 */
async function dismissTourIfVisible(page) {
  try {
    const endBtn = page.locator('.popover.tour [data-role="end"]');
    if (await endBtn.isVisible({ timeout: 2000 })) {
      await endBtn.click();
      await page.waitForSelector('.popover.tour', { state: 'detached', timeout: 3000 }).catch(() => {});
    }
  } catch {
    // Tour not present — continue
  }
}

async function fetchCsrfToken(page) {
  let token = null;
  try {
    token = await page.locator('meta[name="csrf-token"]').first().getAttribute('content');
  } catch {
    token = null;
  }
  if (token) {
    return token;
  }

  await page.goto(`${BASE_URL}/dashboard`);
  token = await page.locator('meta[name="csrf-token"]').first().getAttribute('content');
  if (!token) {
    throw new Error('CSRF token not found on page');
  }

  return token;
}

async function jsonHeaders(page, extra = {}) {
  return {
    Accept: 'application/json',
    'X-CSRF-TOKEN': await fetchCsrfToken(page),
    'X-Requested-With': 'XMLHttpRequest',
    ...extra,
  };
}

async function html(request, path) {
  const response = await request.get(`${BASE_URL}${path}`, {
    failOnStatusCode: false,
    maxRedirects: 0,
  });

  return {
    response,
    body: await response.text(),
  };
}

async function parseJsonOrThrow(response, context) {
  const contentType = response.headers()['content-type'] ?? '';
  const rawBody = await response.text();

  if (!contentType.toLowerCase().includes('application/json')) {
    throw new Error(
      `${context} expected JSON but got status ${response.status()} and content-type "${contentType}". Body preview: ${rawBody.slice(0, 200)}`
    );
  }

  try {
    return JSON.parse(rawBody);
  } catch (error) {
    throw new Error(
      `${context} returned invalid JSON with status ${response.status()} and content-type "${contentType}". Body preview: ${rawBody.slice(0, 200)}`
    );
  }
}

function optionValues(markup, fieldName) {
  const selectPattern = new RegExp(`<select[^>]*name=["']${escapeRegExp(fieldName)}["'][^>]*>([\\s\\S]*?)</select>`, 'i');
  const section = markup.match(selectPattern)?.[1] ?? '';
  const values = [];

  for (const match of section.matchAll(/<option[^>]*value=["']([^"']*)["'][^>]*>/gi)) {
    const value = String(match[1] ?? '').trim();
    if (value) {
      values.push(value);
    }
  }

  return values;
}

function firstOptionValue(markup, fieldName) {
  const values = optionValues(markup, fieldName);
  if (values.length === 0) {
    throw new Error(`No option values found for ${fieldName}`);
  }

  return values[0];
}

async function createClient(page, request, companyName = uniqueValue('PW Client')) {
  const { body } = await html(request, '/clients/create');
  const industryId = firstOptionValue(body, 'industry_id');
  const userId = firstOptionValue(body, 'user_id');
  const response = await request.post(`${BASE_URL}/clients`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {
      name: `${companyName} Contact`,
      company_name: companyName,
      email: `${Date.now()}_${Math.random().toString(36).slice(2, 8)}@example.com`,
      primary_number: '12345678',
      secondary_number: '87654321',
      vat: `${Date.now()}`.slice(-8),
      zipcode: '1000',
      city: 'Copenhagen',
      industry_id: industryId,
      user_id: userId,
    },
  });

  return {
    response,
    companyName,
    payload: await parseJsonOrThrow(response, 'createClient'),
  };
}

// NOTE: server-side "search[value]" is a real, confirmed no-op across every
// DataTables endpoint in this app (recordsFiltered always equals recordsTotal,
// even for a nonexistent search term) — it's not specific to any one
// controller. length is set high enough that a newly-created row is virtually
// guaranteed to be on this single "page" regardless, which is what these
// helpers actually need. The search param is left in place (harmless) since
// fixing the underlying DataTables search bug is a separate, larger issue.
async function clientData(request, search = '') {
  return request.get(`${BASE_URL}/clients/data?draw=1&start=0&length=1000&search[value]=${encodeURIComponent(search)}`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
}

async function createLead(page, request, title = uniqueValue('PW Lead')) {
  const { body } = await html(request, '/leads/create');
  const statusId = firstOptionValue(body, 'status_id');
  const userAssignedId = firstOptionValue(body, 'user_assigned_id');
  const clientExternalId = firstOptionValue(body, 'client_external_id');
  const response = await request.post(`${BASE_URL}/leads`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {
      title,
      description: 'Playwright lead description',
      status_id: statusId,
      user_assigned_id: userAssignedId,
      client_external_id: clientExternalId,
      deadline: '2030-01-01',
      contact_time: '10:30',
    },
  });

  // LeadsController@store returns JSON ({lead_external_id}, 201) for requests that
  // expectsJson() — which jsonHeaders() (Accept: application/json) always triggers.
  // It never redirects for this header combination, so parse the body directly.
  const payload = await parseJsonOrThrow(response, 'createLead');
  const leadExternalId = payload.lead_external_id;
  if (!leadExternalId) {
    throw new Error(`Lead creation did not return lead_external_id. Received status ${response.status()}. Body: ${JSON.stringify(payload)}`);
  }
  return { response, title, statusId, leadExternalId };
}

async function leadData(request, search = '') {
  return request.get(`${BASE_URL}/leads/data?draw=1&start=0&length=1000&search[value]=${encodeURIComponent(search)}`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
}

async function createProject(page, request, title = uniqueValue('PW Project')) {
  const { body } = await html(request, '/projects/create');
  const statusId = firstOptionValue(body, 'status_id');
  const userAssignedId = firstOptionValue(body, 'user_assigned_id');
  const clientExternalId = firstOptionValue(body, 'client_external_id');
  const response = await request.post(`${BASE_URL}/projects`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {
      title,
      description: 'Playwright project description',
      status_id: statusId,
      user_assigned_id: userAssignedId,
      client_external_id: clientExternalId,
      deadline: '2030-01-01',
    },
  });

  return {
    response,
    title,
    statusId,
    payload: await parseJsonOrThrow(response, 'createProject'),
  };
}

async function projectData(request, search = '') {
  return request.get(`${BASE_URL}/projects/data?draw=1&start=0&length=1000&search[value]=${encodeURIComponent(search)}`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
}

async function createTask(page, request, title = uniqueValue('PW Task')) {
  const { body } = await html(request, '/tasks/create');
  const statusId = firstOptionValue(body, 'status_id');
  const userAssignedId = firstOptionValue(body, 'user_assigned_id');
  const clientExternalId = firstOptionValue(body, 'client_external_id');
  const response = await request.post(`${BASE_URL}/tasks`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {
      title,
      description: 'Playwright task description',
      status_id: statusId,
      user_assigned_id: userAssignedId,
      client_external_id: clientExternalId,
      deadline: '2030-01-01',
    },
  });

  return {
    response,
    title,
    statusId,
    payload: await parseJsonOrThrow(response, 'createTask'),
  };
}

async function taskData(request, search = '') {
  return request.get(`${BASE_URL}/tasks/data?draw=1&start=0&length=1000&search[value]=${encodeURIComponent(search)}`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
}

async function createRole(page, request, name = uniqueValue('pw_role')) {
  const response = await request.post(`${BASE_URL}/roles`, {
    failOnStatusCode: false,
    maxRedirects: 0,
    headers: await jsonHeaders(page),
    form: {
      name,
      description: `${name} description`,
    },
  });

  return { response, name };
}

async function roleData(request, search = '') {
  return request.get(`${BASE_URL}/roles/data?draw=1&start=0&length=1000&search[value]=${encodeURIComponent(search)}`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
}

async function createUser(page, request, name = uniqueValue('PW User')) {
  const { body } = await html(request, '/users/create');
  const roleId = firstOptionValue(body, 'role');
  const departmentId = firstOptionValue(body, 'department');
  const email = `${Date.now()}_${Math.random().toString(36).slice(2, 8)}@example.com`;
  const response = await request.post(`${BASE_URL}/users`, {
    failOnStatusCode: false,
    maxRedirects: 0,
    headers: await jsonHeaders(page),
    form: {
      name,
      email,
      password: DEFAULT_PASSWORD,
      password_confirmation: DEFAULT_PASSWORD,
      // Backend expects plural keys on create (see StoreUserRequest/UsersController@store).
      roles: roleId,
      departments: departmentId,
      // Also send the singular keys used by the HTML form so either implementation works.
      role: roleId,
      department: departmentId,
    },
  });

  return { response, name, email };
}

async function userData(request, search = '') {
  return request.get(`${BASE_URL}/users/data?draw=1&start=0&length=1000&search[value]=${encodeURIComponent(search)}`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
}

async function createDepartment(page, request, name = uniqueValue('PW Department')) {
  const response = await request.post(`${BASE_URL}/departments`, {
    failOnStatusCode: false,
    maxRedirects: 0,
    headers: await jsonHeaders(page),
    form: {
      name,
      description: `${name} description`,
    },
  });

  return { response, name };
}

async function departmentData(request, search = '') {
  return request.get(`${BASE_URL}/departments/indexData?draw=1&start=0&length=1000&search[value]=${encodeURIComponent(search)}`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
}

async function createAbsence(page, request) {
  const { body } = await html(request, '/absences/create?management=true');
  const userExternalId = firstOptionValue(body, 'user_external_id');
  const reason = firstOptionValue(body, 'reason');
  const response = await request.post(`${BASE_URL}/absences`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {
      user_external_id: userExternalId,
      reason,
      start_date: '2030/01/01',
      end_date: '2030/01/02',
      radio: 'irrelevant',
      comment: 'Playwright absence comment',
    },
  });

  const payload = response.status() === 200 ? await parseJsonOrThrow(response, 'createAbsence') : {};
  const externalId = payload.external_id ?? null;

  return { response, reason, externalId };
}

async function absenceData(request, search = '') {
  // Fetch a generous page size so tests can scan all records without pagination issues.
  // Note: DataTables server-side search on the absences endpoint filters the raw user_id
  // integer column – not the transformed display name – so searching by user name will not
  // narrow results server-side.  Callers that need to locate a specific row should filter
  // client-side on the returned data array instead.
  return request.get(`${BASE_URL}/absences/data?draw=1&start=0&length=100&search[value]=${encodeURIComponent(search)}`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
}

async function createOffer(page, request) {
  const { leadExternalId } = await createLead(page, request, uniqueValue('PW Offer Lead'));
  const response = await request.post(`${BASE_URL}/offers/create/${leadExternalId}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    data: [
      {
        title: 'Playwright Offer Line',
        type: 'hours',
        price: 50,
        quantity: 1,
        comment: 'Playwright offer line',
      },
    ],
  });

  const { body } = await html(request, `/leads/${leadExternalId}`);
  const offerExternalId = body.match(/data-offer-external_id="([^"]+)"/)?.[1];
  if (!offerExternalId) {
    throw new Error(`Offer external id not found on lead page for lead ${leadExternalId}`);
  }

  return { response, leadExternalId, offerExternalId };
}

async function createInvoice(page, request) {
  const { leadExternalId, offerExternalId } = await createOffer(page, request);
  const winResponse = await request.post(`${BASE_URL}/offer/won`, {
    failOnStatusCode: false,
    headers: {
      'X-CSRF-TOKEN': await fetchCsrfToken(page),
    },
    form: {
      offer_external_id: offerExternalId,
    },
    maxRedirects: 0,
  });
  expect(winResponse.status()).toBe(302);

  const { body } = await html(request, `/leads/${leadExternalId}`);
  const invoiceExternalId = body.match(/\/invoices\/([a-f0-9-]+)/i)?.[1];
  if (!invoiceExternalId) {
    throw new Error(`Invoice external id not found on lead page for lead ${leadExternalId}`);
  }

  const sentResponse = await request.post(`${BASE_URL}/invoices/sentinvoice/${invoiceExternalId}`, {
    failOnStatusCode: false,
    headers: {
      'X-CSRF-TOKEN': await fetchCsrfToken(page),
    },
    form: {},
    maxRedirects: 0,
  });
  expect(sentResponse.status()).toBe(302);

  return { winResponse, sentResponse, invoiceExternalId };
}

async function addPayment(page, request, invoiceExternalId, overrides = {}) {
  return request.post(`${BASE_URL}/payment/add-payment/${invoiceExternalId}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {
      amount: 100,
      payment_date: '2030-01-01',
      source: 'bank',
      description: 'Playwright payment',
      ...overrides,
    },
  });
}

async function paymentsData(request, invoiceExternalId) {
  return request.get(`${BASE_URL}/invoices/payments-data/${invoiceExternalId}?draw=1&start=0&length=1000&search[value]=`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
}

async function uploadClientDocument(page, request, clientExternalId) {
  const uploadResponse = await request.post(`${BASE_URL}/clients/upload/${clientExternalId}`, {
    failOnStatusCode: false,
    headers: {
      'X-CSRF-TOKEN': await fetchCsrfToken(page),
    },
    multipart: {
      file: {
        name: 'playwright-client-document.txt',
        mimeType: 'text/plain',
        buffer: Buffer.from('playwright client document'),
      },
    },
    maxRedirects: 0,
  });

  const { body } = await html(request, `/clients/${clientExternalId}`);
  const documentExternalId = body.match(/\/document\/([a-f0-9-]+)/i)?.[1];
  if (!documentExternalId) {
    throw new Error(`Document external id not found on client page for client ${clientExternalId}`);
  }

  return { uploadResponse, documentExternalId };
}

/**
 * Appointments have no HTTP create endpoint anywhere in the app -
 * AppointmentsController only has calendar/data/update/destroy. The only
 * place one is ever created is Database\Seeders\Concerns\WorldBuilder,
 * which isn't part of the seeder path this environment actually runs
 * (see DatabaseSeeder), so a fresh DB has zero appointments and any test
 * needing one has no way to get it through the running application.
 *
 * This shells out to the same `Appointment::factory()` logic WorldBuilder
 * already uses, via `php artisan tinker` - the one exception to this file's
 * otherwise all-HTTP pattern, justified by there being no HTTP path to
 * reach for. DB_HOST is overridden to 127.0.0.1 because this runs on the
 * host (not inside the app's docker container), where the "mariadb"
 * hostname from .env doesn't resolve, but mariadb's port is published to
 * the host directly.
 */
function createAppointment() {
  const projectRoot = path.join(__dirname, '../../..');
  // Piped to tinker's stdin rather than passed via --execute="...": the
  // script is plain PHP with no special handling needed, whereas
  // --execute="$foo" runs through a shell that expands "$foo" as a shell
  // variable (to empty, since it's never actually set) before PHP ever
  // sees it - passing it as stdin sidesteps that entirely.
  const script = `
$task = \\App\\Models\\Task::first() ?? \\App\\Models\\Task::factory()->create();
$user = \\App\\Models\\User::first();
$appointment = \\App\\Models\\Appointment::factory()->create([
    'user_id' => $user->id,
    'source_type' => \\App\\Models\\Task::class,
    'source_id' => $task->id,
    'client_id' => $task->client_id,
]);
echo $appointment->external_id . '|' . $user->external_id;
`;

  const output = execSync('php artisan tinker', {
    cwd: projectRoot,
    encoding: 'utf8',
    env: { ...process.env, DB_HOST: '127.0.0.1' },
    input: script,
  }).trim();
  const [externalId, userExternalId] = output.split('|');

  if (!externalId || !userExternalId) {
    throw new Error(`createAppointment: tinker did not return the expected output, got: "${output}"`);
  }

  return { appointmentExternalId: externalId, userExternalId };
}

async function firstAppointment(request) {
  const response = await request.get(`${BASE_URL}/appointments/data`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
  expect(response.status()).toBe(200);
  const payload = await response.json();
  expect(Array.isArray(payload)).toBe(true);
  expect(payload.length).toBeGreaterThan(0);
  return payload[0];
}

async function usersCollection(request) {
  const response = await request.get(`${BASE_URL}/users/users`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
  expect(response.status()).toBe(200);
  return response.json();
}

async function expectValidationError(response, field) {
  expect(response.status()).toBe(422);
  const payload = await response.json();
  expect(payload.errors).toBeTruthy();
  expect(Object.keys(payload.errors)).toContain(field);
}

/**
 * Assert a success flash message was set for this request.
 *
 * Flashed session messages render as a plain, visible Bootstrap alert
 * (see layouts/master.blade.php's ".flash-message" block) — no Vue involved.
 */
async function expectFlashMessage(page, text) {
  await expect(page.locator('.alert.alert-success.flash-message').filter({ hasText: text })).toBeVisible();
}

module.exports = {
  BASE_URL,
  loginAsAdmin,
  dismissTourIfVisible,
  fillSummernote,
  jsonHeaders,
  uniqueValue,
  html,
  firstOptionValue,
  createAbsence,
  absenceData,
  createClient,
  clientData,
  createDepartment,
  departmentData,
  uploadClientDocument,
  createInvoice,
  addPayment,
  paymentsData,
  createProject,
  projectData,
  createRole,
  roleData,
  createLead,
  leadData,
  createTask,
  taskData,
  createUser,
  userData,
  createOffer,
  createAppointment,
  firstAppointment,
  usersCollection,
  expectValidationError,
  expectFlashMessage,
};
