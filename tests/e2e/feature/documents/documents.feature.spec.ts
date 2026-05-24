import { test, expect, nonAdminTest } from '../../helpers/fixtures';
import { PLAYWRIGHT_BASE_URL } from '../../helpers/config';
import { createAdminSession } from '../../helpers/session-context';
import { createClientDocumentFixture } from '../../helpers/coverage-fixtures';
import { fetchCsrfToken } from '../../helpers/csrf';

test.describe('Documents feature behavior', () => {
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
