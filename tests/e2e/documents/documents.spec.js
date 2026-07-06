const { test, expect } = require('@playwright/test');
const {
  BASE_URL,
  loginAsAdmin,
  createClient,
  uploadClientDocument,
} = require('../helpers/plain-e2e');

test('uploaded client document can be viewed inline', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  const { payload } = await createClient(page, request);
  const { uploadResponse, documentExternalId } = await uploadClientDocument(page, request, payload.client.external_id);

  const response = await request.get(`${BASE_URL}/document/${documentExternalId}`, {
    failOnStatusCode: false,
  });
  const body = await response.text();

  expect(uploadResponse.status()).toBe(200);
  expect(response.status()).toBe(200);
  expect(response.headers()['content-disposition'] ?? '').toContain('inline');
  // App\Services\Storage\Local::view() is a deliberate stub in local/testing
  // environments — it never writes/reads real files, always returning this
  // literal string regardless of what was actually uploaded.
  expect(body).toContain('fake file content');
});

test('uploaded client document can be downloaded as attachment', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  const { payload } = await createClient(page, request);
  const { documentExternalId } = await uploadClientDocument(page, request, payload.client.external_id);

  const response = await request.get(`${BASE_URL}/document/download/${documentExternalId}`, {
    failOnStatusCode: false,
  });

  expect(response.status()).toBe(200);
  expect(response.headers()['content-disposition'] ?? '').toContain('attachment');
  // See the note in the previous test — Local::download() is the same stub.
  expect(await response.text()).toContain('fake file content');
});

test('document upload modal route renders file input markup', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  const { payload } = await createClient(page, request);
  const response = await request.get(`${BASE_URL}/add-documents/${payload.client.external_id}/client`, {
    failOnStatusCode: false,
  });

  expect(response.status()).toBe(200);
  // The upload UI is a Dropzone.js widget, not a plain <input type="file">
  expect((await response.text()).toLowerCase()).toContain('id="dropzone-images"');
});

test('unknown document id returns not found', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  const response = await request.get(`${BASE_URL}/document/00000000-0000-0000-0000-000000000000`, {
    failOnStatusCode: false,
    maxRedirects: 0,
  });

  expect(response.status()).toBe(404);
});
