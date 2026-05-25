const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createClient, uploadClientDocument } = require('../helpers/plain-e2e');

test('an uploaded client document can be opened inline and returns the original file content', async ({ page }) => {
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
  expect(uploadResponse.status(), 'Document upload should return 200').toBe(200);
  expect(response.status(), 'Document view endpoint should return 200').toBe(200);
  expect(
    response.headers()['content-disposition'] ?? '',
    'Inline view should set content-disposition to inline'
  ).toContain('inline');
  expect(body, 'Response body should contain the uploaded file content').toContain('playwright client document');
});

test('an uploaded client document can be downloaded with an attachment disposition', async ({ page }) => {
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
  expect(response.status(), 'Document download endpoint should return 200').toBe(200);
  expect(
    response.headers()['content-disposition'] ?? '',
    'Download should set content-disposition to attachment'
  ).toContain('attachment');
  expect(body, 'Downloaded file body should contain the original content').toContain('playwright client document');
});

test('the document upload modal route renders a file input for clients', async ({ page }) => {
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
  expect(response.status(), 'Document upload modal route should return 200').toBe(200);
  expect(body.toLowerCase(), 'Modal response should contain a file input field').toContain('file');
});

test('requesting a document with an unknown external_id returns a 404 response', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const nonExistentId = '00000000-0000-0000-0000-000000000000';

  /* Act */
  const response = await request.get(`${BASE_URL}/document/${nonExistentId}`, {
    failOnStatusCode: false,
    maxRedirects: 0,
  });

  /* Assert – the server must not silently return 200 for a missing document */
  expect(response.status(), 'Non-existent document external_id should return 404').toBe(404);
});
