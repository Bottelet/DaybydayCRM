import { expect, type APIRequestContext, type Page } from '@playwright/test';
import { ClientActions, LeadActions } from './feature-domain';
import { PLAYWRIGHT_BASE_URL } from './config';
import { fetchCsrfToken } from './csrf';

const DEFAULT_OFFER_LINES = [
  {
    title: 'Playwright Offer Line',
    type: 'hours',
    price: 50,
    quantity: 1,
    comment: 'Playwright generated offer line',
  },
];

function firstMatch(value: string, pattern: RegExp, label: string) {
  const match = value.match(pattern)?.[1];
  if (!match) {
    throw new Error(`Could not find ${label}`);
  }

  return match;
}

async function html(request: APIRequestContext, path: string) {
  const response = await request.get(`${PLAYWRIGHT_BASE_URL}${path}`, {
    failOnStatusCode: false,
    maxRedirects: 0,
  });
  expect(response.status()).toBe(200);
  return response.text();
}

async function csrfHeaders(page: Page, json = true) {
  const headers: Record<string, string> = {
    'X-CSRF-TOKEN': await fetchCsrfToken(page),
  };

  if (json) {
    headers.Accept = 'application/json';
    headers['X-Requested-With'] = 'XMLHttpRequest';
  }

  return headers;
}

export async function createLeadFixture(page: Page, request: APIRequestContext) {
  const title = `PW Lead ${Date.now()}`;
  const { response } = await LeadActions.create(page, request, title);
  expect(response.status()).toBe(302);
  const location = response.headers()['location'] ?? '';
  const leadExternalId = new URL(location, PLAYWRIGHT_BASE_URL).pathname.split('/').filter(Boolean).pop();
  if (!leadExternalId) {
    throw new Error('Could not determine lead external id');
  }

  return { title, leadExternalId };
}

export async function createOfferFixture(page: Page, request: APIRequestContext) {
  const { leadExternalId } = await createLeadFixture(page, request);
  const response = await request.post(`${PLAYWRIGHT_BASE_URL}/offers/create/${leadExternalId}`, {
    failOnStatusCode: false,
    headers: await csrfHeaders(page),
    data: DEFAULT_OFFER_LINES,
  });

  expect(response.status()).toBe(200);
  const leadHtml = await html(request, `/leads/${leadExternalId}`);
  const offerExternalId = firstMatch(leadHtml, /data-offer-external_id="([^"]+)"/, 'offer external id');

  return { leadExternalId, offerExternalId };
}

export async function createInvoiceFixture(page: Page, request: APIRequestContext) {
  const { leadExternalId, offerExternalId } = await createOfferFixture(page, request);
  const wonResponse = await request.post(`${PLAYWRIGHT_BASE_URL}/offer/won`, {
    failOnStatusCode: false,
    headers: {
      'X-CSRF-TOKEN': await fetchCsrfToken(page),
    },
    form: {
      offer_external_id: offerExternalId,
    },
    maxRedirects: 0,
  });

  expect(wonResponse.status()).toBe(302);
  const interimLeadHtml = await html(request, `/leads/${leadExternalId}`);
  const invoiceExternalId = firstMatch(interimLeadHtml, /\/invoices\/([^"]+)/, 'invoice external id');

  const sentResponse = await request.post(`${PLAYWRIGHT_BASE_URL}/invoices/sentinvoice/${invoiceExternalId}`, {
    failOnStatusCode: false,
    headers: {
      'X-CSRF-TOKEN': await fetchCsrfToken(page),
    },
    form: {},
    maxRedirects: 0,
  });
  expect(sentResponse.status()).toBe(302);

  return { leadExternalId, offerExternalId, invoiceExternalId };
}

export async function addPayment(
  page: Page,
  request: APIRequestContext,
  invoiceExternalId: string,
  amount: string | number,
  overrides: Partial<Record<'payment_date' | 'source' | 'description', string>> = {},
) {
  return request.post(`${PLAYWRIGHT_BASE_URL}/payment/add-payment/${invoiceExternalId}`, {
    failOnStatusCode: false,
    headers: await csrfHeaders(page),
    form: {
      amount,
      payment_date: overrides.payment_date ?? '2020-01-01',
      source: overrides.source ?? 'bank',
      description: overrides.description ?? 'Playwright payment',
    },
  });
}

export async function paymentsData(request: APIRequestContext, invoiceExternalId: string) {
  return request.get(
    `${PLAYWRIGHT_BASE_URL}/invoices/payments-data/${invoiceExternalId}?draw=1&start=0&length=25&search[value]=`,
    {
      failOnStatusCode: false,
      headers: { Accept: 'application/json' },
    },
  );
}

export async function firstAppointment(request: APIRequestContext) {
  const response = await request.get(`${PLAYWRIGHT_BASE_URL}/appointments/data`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
  expect(response.status()).toBe(200);
  const payload = (await response.json()) as Array<Record<string, unknown>>;
  expect(payload.length).toBeGreaterThan(0);

  return payload[0] as {
    external_id: string;
    start_at: string;
    end_at: string;
    user?: { external_id?: string };
  };
}

export async function calendarUsers(request: APIRequestContext) {
  const response = await request.get(`${PLAYWRIGHT_BASE_URL}/users/users`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
  expect(response.status()).toBe(200);
  return (await response.json()) as Array<{ external_id: string }>;
}

export async function createClientDocumentFixture(page: Page, request: APIRequestContext) {
  const companyName = `PW Document Client ${Date.now()}`;
  const { response } = await ClientActions.create(page, request, companyName);
  expect(response.status()).toBe(201);
  const payload = (await response.json()) as { client: { external_id: string } };
  const clientExternalId = payload.client.external_id;

  const uploadResponse = await request.post(`${PLAYWRIGHT_BASE_URL}/clients/upload/${clientExternalId}`, {
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

  expect(uploadResponse.status()).toBe(200);
  const clientHtml = await html(request, `/clients/${clientExternalId}`);
  const documentExternalId = firstMatch(clientHtml, /\/document\/([a-f0-9-]+)/i, 'document external id');

  return { clientExternalId, documentExternalId };
}
