const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createClient, uploadClientDocument } = require('../helpers/plain-e2e');

test('uploaded client documents can be opened inline with the stored file content', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { payload } = await createClient(page, request);
  const clientExternalId = payload.client.external_id;
  const { uploadResponse, documentExternalId } = await uploadClientDocument(page, request, clientExternalId);

  /* Act */
  const response = await request.get(`${BASE_URL}/document/${documentExternalId}`, {
    failOnStatusCode: false,
  });
  const body = await response.text();

  /* Assert */
  expect(uploadResponse.status()).toBe(200);
  expect(response.status()).toBe(200);
  expect(response.headers()['content-disposition'] ?? '').toContain('inline');
  expect(body).toContain('playwright client document');
});

test('uploaded client documents can be downloaded as attachments', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { payload } = await createClient(page, request);
  const clientExternalId = payload.client.external_id;
  const { documentExternalId } = await uploadClientDocument(page, request, clientExternalId);

  /* Act */
  const response = await request.get(`${BASE_URL}/document/download/${documentExternalId}`, {
    failOnStatusCode: false,
  });
  const body = await response.text();

  /* Assert */
  expect(response.status()).toBe(200);
  expect(response.headers()['content-disposition'] ?? '').toContain('attachment');
  expect(body).toContain('playwright client document');
});

test('document upload modal route returns a renderable response for clients', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { payload } = await createClient(page, request);
  const clientExternalId = payload.client.external_id;

  /* Act */
  const response = await request.get(`${BASE_URL}/add-documents/${clientExternalId}/client`, {
    failOnStatusCode: false,
  });
  const body = await response.text();

  /* Assert */
  expect(response.status()).toBe(200);
  expect(body.toLowerCase()).toContain('file');
});

test('viewing an unknown document id returns a 404 response', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;

  /* Act */
  const response = await request.get(`${BASE_URL}/document/00000000-0000-0000-0000-000000000000`, {
    failOnStatusCode: false,
    maxRedirects: 0,
  });

  /* Assert */
  expect(response.status()).toBe(404);
});
