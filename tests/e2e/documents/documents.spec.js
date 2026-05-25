const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createClient, uploadClientDocument } = require('../helpers/plain-e2e');
const {createAdminSession} = require("../helpers/session-context");
const {nonAdminTest} = require("../../helpers/fixtures");
const {PLAYWRIGHT_BASE_URL} = require("../../helpers/config");
const {createClientDocumentFixture} = require("../helpers/coverage-fixtures");

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

test('uploads, views, and downloads a client document', async ({ page, request }) => {
    const { documentExternalId } = await createClientDocumentFixture(page, request);

    const viewResponse = await request.get(`${PLAYWRIGHT_BASE_URL}/document/${documentExternalId}`, {
        failOnStatusCode: false,
    });
    expect(viewResponse.status()).toBe(200);
    expect(viewResponse.headers()['content-disposition'] ?? '').toContain('inline');

    const downloadResponse = await request.get(`${PLAYWRIGHT_BASE_URL}/document/download/${documentExternalId}`, {
        failOnStatusCode: false,
    });
    expect(downloadResponse.status()).toBe(200);
    expect(downloadResponse.headers()['content-disposition'] ?? '').toContain('attachment');
});

test('returns 404 for unknown documents', async ({ request }) => {
    const response = await request.get(`${PLAYWRIGHT_BASE_URL}/document/00000000-0000-0000-0000-000000000000`, {
        failOnStatusCode: false,
    });

    expect(response.status()).toBe(404);
});

nonAdminTest.describe('Documents permissions', () => {
    nonAdminTest('denies client uploads without permission', async ({ page, request }) => {
        const admin = await createAdminSession(page);

        try {
            const { clientExternalId } = await createClientDocumentFixture(admin.page, admin.request);
            const response = await request.post(`${PLAYWRIGHT_BASE_URL}/clients/upload/${clientExternalId}`, {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
                multipart: {
                    file: {
                        name: 'forbidden-client-upload.txt',
                        mimeType: 'text/plain',
                        buffer: Buffer.from('forbidden'),
                    },
                },
                maxRedirects: 0,
            });

            expect(response.status()).toBe(403);
        } finally {
            await admin.dispose();
        }
    });

    nonAdminTest('denies task uploads without permission', async ({ page, request }) => {
        const response = await request.post(`${PLAYWRIGHT_BASE_URL}/uploaToTask/invalid-task`, {
            failOnStatusCode: false,
            headers: {
                'X-CSRF-TOKEN': await fetchCsrfToken(page),
            },
            multipart: {
                files: {
                    name: 'forbidden-task-upload.txt',
                    mimeType: 'text/plain',
                    buffer: Buffer.from('forbidden'),
                },
            },
            maxRedirects: 0,
        });
        expect(response.status()).toBe(302);
    });

    nonAdminTest('denies viewing and downloading another users document', async ({ page, request }) => {
        const admin = await createAdminSession(page);

        try {
            const { documentExternalId } = await createClientDocumentFixture(admin.page, admin.request);
            const viewResponse = await request.get(`${PLAYWRIGHT_BASE_URL}/document/${documentExternalId}`, {
                failOnStatusCode: false,
                maxRedirects: 0,
            });
            expect(viewResponse.status()).toBe(302);

            const downloadResponse = await request.get(`${PLAYWRIGHT_BASE_URL}/document/download/${documentExternalId}`, {
                failOnStatusCode: false,
                maxRedirects: 0,
            });
            expect(downloadResponse.status()).toBe(302);
        } finally {
            await admin.dispose();
        }
    });
});
