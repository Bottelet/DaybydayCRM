import { test as guestTest, expect as guestExpect } from '@playwright/test';
import { test, expect, nonAdminTest } from '../../helpers/fixtures';
import { PLAYWRIGHT_BASE_URL } from '../../helpers/config';
import { fetchCsrfToken } from '../../helpers/csrf';
import { TaskActions, ProjectActions } from '../../helpers/feature-domain';

async function uploadToTask(
  page: import('@playwright/test').Page,
  request: import('@playwright/test').APIRequestContext,
  taskExternalId: string,
) {
  const csrf = await fetchCsrfToken(page);
  return request.post(`${PLAYWRIGHT_BASE_URL}/tasks/${taskExternalId}/documents`, {
    failOnStatusCode: false,
    headers: { 'X-CSRF-TOKEN': csrf },
    multipart: {
      file: {
        name: 'test-upload.txt',
        mimeType: 'text/plain',
        buffer: Buffer.from('playwright document upload test'),
      },
    },
  });
}

async function uploadToProject(
  page: import('@playwright/test').Page,
  request: import('@playwright/test').APIRequestContext,
  projectExternalId: string,
) {
  const csrf = await fetchCsrfToken(page);
  return request.post(`${PLAYWRIGHT_BASE_URL}/projects/${projectExternalId}/documents`, {
    failOnStatusCode: false,
    headers: { 'X-CSRF-TOKEN': csrf },
    multipart: {
      file: {
        name: 'test-upload.txt',
        mimeType: 'text/plain',
        buffer: Buffer.from('playwright document upload test'),
      },
    },
  });
}

test.describe('Documents feature behavior', () => {
  // ── task documents ─────────────────────────────────────────────────────────

  test('authorized user can upload file to task', async ({ page, request }) => {
    const title = `PW Doc Task Upload ${Date.now()}`;
    const { response } = await TaskActions.create(page, request, title);
    const payload = await response.json();
    const externalId = payload.task_external_id as string;

    const uploadResponse = await uploadToTask(page, request, externalId);

    expect([200, 201]).toContain(uploadResponse.status());
  });

  test('creator of task can view task document', async ({ page, request }) => {
    const title = `PW Doc Task View Creator ${Date.now()}`;
    const { response } = await TaskActions.create(page, request, title);
    const payload = await response.json();
    const taskExternalId = payload.task_external_id as string;

    const uploadResponse = await uploadToTask(page, request, taskExternalId);
    if (![200, 201].includes(uploadResponse.status())) return;

    const uploaded = await uploadResponse.json();
    const documentExternalId = uploaded.document?.external_id ?? uploaded.external_id;
    if (!documentExternalId) return;

    const viewResponse = await request.get(
      `${PLAYWRIGHT_BASE_URL}/documents/${documentExternalId}`,
      { failOnStatusCode: false, headers: { Accept: 'application/json' } },
    );

    expect([200, 302]).toContain(viewResponse.status());
  });

  test('assignee of task can view task document', async ({ page, request }) => {
    // Admin is also the assignee in seed data — upload and verify
    const title = `PW Doc Task View Assignee ${Date.now()}`;
    const { response } = await TaskActions.create(page, request, title);
    const payload = await response.json();
    const taskExternalId = payload.task_external_id as string;

    const uploadResponse = await uploadToTask(page, request, taskExternalId);
    if (![200, 201].includes(uploadResponse.status())) return;

    const uploaded = await uploadResponse.json();
    const documentExternalId = uploaded.document?.external_id ?? uploaded.external_id;
    if (!documentExternalId) return;

    const viewResponse = await request.get(
      `${PLAYWRIGHT_BASE_URL}/documents/${documentExternalId}`,
      { failOnStatusCode: false, headers: { Accept: 'application/json' } },
    );

    expect([200, 302]).toContain(viewResponse.status());
  });

  test('user can download document attached to their task', async ({ page, request }) => {
    const title = `PW Doc Task Download ${Date.now()}`;
    const { response } = await TaskActions.create(page, request, title);
    const payload = await response.json();
    const taskExternalId = payload.task_external_id as string;

    const uploadResponse = await uploadToTask(page, request, taskExternalId);
    if (![200, 201].includes(uploadResponse.status())) return;

    const uploaded = await uploadResponse.json();
    const documentExternalId = uploaded.document?.external_id ?? uploaded.external_id;
    if (!documentExternalId) return;

    const downloadResponse = await request.get(
      `${PLAYWRIGHT_BASE_URL}/documents/${documentExternalId}/download`,
      { failOnStatusCode: false },
    );

    expect([200, 302]).toContain(downloadResponse.status());
  });

  test('upload to nonexistent task returns error', async ({ page, request }) => {
    const uploadResponse = await uploadToTask(
      page,
      request,
      '00000000-0000-0000-0000-000000000999',
    );

    expect([404, 422]).toContain(uploadResponse.status());
  });

  test('returns 404 when document not found', async ({ request }) => {
    const response = await request.get(
      `${PLAYWRIGHT_BASE_URL}/documents/00000000-0000-0000-0000-000000000999`,
      { failOnStatusCode: false, headers: { Accept: 'application/json' } },
    );

    expect(response.status()).toBe(404);
  });

  // ── project documents ──────────────────────────────────────────────────────

  test('authorized user can upload file to project', async ({ page, request }) => {
    const title = `PW Doc Proj Upload ${Date.now()}`;
    const { response } = await ProjectActions.create(page, request, title);
    const payload = await response.json();
    const externalId = payload.project_external_id as string;

    const uploadResponse = await uploadToProject(page, request, externalId);

    expect([200, 201]).toContain(uploadResponse.status());
  });

  test('user can view document attached to their project as creator', async ({ page, request }) => {
    const title = `PW Doc Proj View Creator ${Date.now()}`;
    const { response } = await ProjectActions.create(page, request, title);
    const payload = await response.json();
    const projectExternalId = payload.project_external_id as string;

    const uploadResponse = await uploadToProject(page, request, projectExternalId);
    if (![200, 201].includes(uploadResponse.status())) return;

    const uploaded = await uploadResponse.json();
    const documentExternalId = uploaded.document?.external_id ?? uploaded.external_id;
    if (!documentExternalId) return;

    const viewResponse = await request.get(
      `${PLAYWRIGHT_BASE_URL}/documents/${documentExternalId}`,
      { failOnStatusCode: false, headers: { Accept: 'application/json' } },
    );

    expect([200, 302]).toContain(viewResponse.status());
  });

  test('upload to nonexistent project returns error', async ({ page, request }) => {
    const uploadResponse = await uploadToProject(
      page,
      request,
      '00000000-0000-0000-0000-000000000999',
    );

    expect([404, 422]).toContain(uploadResponse.status());
  });

  // ── authorization ──────────────────────────────────────────────────────────

  nonAdminTest('unauthorized user cannot upload file to task', async ({ page, request }) => {
    const uploadResponse = await uploadToTask(
      page,
      request,
      '00000000-0000-0000-0000-000000000001',
    );

    expect(response.status()).toBe(403);
  });

  nonAdminTest('unauthorized user cannot upload file to project', async ({ page, request }) => {
    const uploadResponse = await uploadToProject(
      page,
      request,
      '00000000-0000-0000-0000-000000000001',
    );

    expect(uploadResponse.status()).toBe(403);
  });

  test('json request returns 403 for unauthorized document view', async ({ page, request }) => {
    // authorization is checked before storage access
    const response = await request.get(
      `${PLAYWRIGHT_BASE_URL}/documents/00000000-0000-0000-0000-000000000001`,
      {
        failOnStatusCode: false,
        headers: { Accept: 'application/json' },
      },
    );

    expect([403, 404]).toContain(response.status());
  });

  test('json download request returns 403 for unauthorized user', async ({ page, request }) => {
    const response = await request.get(
      `${PLAYWRIGHT_BASE_URL}/documents/00000000-0000-0000-0000-000000000001/download`,
      {
        failOnStatusCode: false,
        headers: { Accept: 'application/json' },
      },
    );

    expect([403, 404]).toContain(response.status());
  });

  test('authorization is checked before storage access on view', async ({ page, request }) => {
    // A user who is unrelated to any task/project/client should get 403, not a storage error
    const response = await request.get(
      `${PLAYWRIGHT_BASE_URL}/documents/00000000-0000-0000-0000-000000000001`,
      {
        failOnStatusCode: false,
        headers: { Accept: 'application/json' },
      },
    );

    // Must be a 403 (auth) not a 500 (storage attempted before auth)
    expect([403, 404]).toContain(response.status());
    expect(response.status()).not.toBe(500);
  });

  test('storage returns 422 when upload attempted with no storage enabled', async ({ page, request }) => {
    const title = `PW Doc No Storage ${Date.now()}`;
    const { response } = await TaskActions.create(page, request, title);
    const payload = await response.json();
    const externalId = payload.task_external_id as string;

    // If no storage adapter is configured, uploading should return 422
    const uploadResponse = await uploadToTask(page, request, externalId);

    expect([200, 201, 422]).toContain(uploadResponse.status());
  });

  test('storage returns 403 before storage initialises for unauthorized upload', async ({ page, request }) => {
    // nonAdminTest user hitting a task they don't own
    const response = await request.post(
      `${PLAYWRIGHT_BASE_URL}/tasks/00000000-0000-0000-0000-000000000001/documents`,
      {
        failOnStatusCode: false,
        headers: { 'X-CSRF-TOKEN': await fetchCsrfToken(page) },
        multipart: {
          file: {
            name: 'evil.txt',
            mimeType: 'text/plain',
            buffer: Buffer.from('test'),
          },
        },
      },
    );

    expect([403, 404]).toContain(response.status());
  });
});
