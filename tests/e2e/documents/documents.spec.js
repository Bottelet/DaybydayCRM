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
  expect(body).toContain('playwright client document');
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
  expect(await response.text()).toContain('playwright client document');
});

test('document upload modal route renders file input markup', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  const { payload } = await createClient(page, request);
  const response = await request.get(`${BASE_URL}/add-documents/${payload.client.external_id}/client`, {
    failOnStatusCode: false,
  });

  expect(response.status()).toBe(200);
  expect((await response.text()).toLowerCase()).toContain('file');
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
