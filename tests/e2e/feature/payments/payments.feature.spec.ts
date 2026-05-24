import { test, expect, nonAdminTest } from '../../helpers/fixtures';
import { createAdminSession } from '../../helpers/session-context';
import { addPayment, createInvoiceFixture, paymentsData } from '../../helpers/coverage-fixtures';
import { PLAYWRIGHT_BASE_URL } from '../../helpers/config';
import { fetchCsrfToken } from '../../helpers/csrf';

test.describe('Payments feature behavior', () => {
  test('adds payments with decimal formats and updates invoice status', async ({ page, request }) => {
    const { invoiceExternalId } = await createInvoiceFixture(page, request);

    const first = await addPayment(page, request, invoiceExternalId, 50.234, {
      description: 'dot separator payment',
    });
    expect(first.status()).toBe(201);

    const second = await addPayment(page, request, invoiceExternalId, '10,50', {
      description: 'comma separator payment',
    });
    expect(second.status()).toBe(201);

    const invoiceResponse = await request.get(`${PLAYWRIGHT_BASE_URL}/invoices/${invoiceExternalId}`, {
      failOnStatusCode: false,
    });
    expect(invoiceResponse.status()).toBe(200);

    const invoiceHtml = (await invoiceResponse.text()).toLowerCase();
    expect(invoiceHtml).toContain('paid');

    const dataResponse = await paymentsData(request, invoiceExternalId);
    const payload = await dataResponse.json();
    expect(JSON.stringify(payload)).toContain('dot separator payment');
    expect(JSON.stringify(payload)).toContain('comma separator payment');
  });

  test('rejects invalid payment payloads', async ({ page, request }) => {
    const { invoiceExternalId } = await createInvoiceFixture(page, request);

    const invalidAmount = await addPayment(page, request, invoiceExternalId, 'not-a-number');
    expect(invalidAmount.status()).toBe(422);

    const invalidSource = await addPayment(page, request, invoiceExternalId, 50, {
      source: 'invalid_source',
    });
    expect(invalidSource.status()).toBe(422);

    const invalidDate = await addPayment(page, request, invoiceExternalId, 50, {
      payment_date: '2020-15-15',
    });
    expect(invalidDate.status()).toBe(422);

    const zeroAmount = await addPayment(page, request, invoiceExternalId, 0);
    expect(zeroAmount.status()).toBe(422);
  });

  test('deletes payments', async ({ page, request }) => {
    const { invoiceExternalId } = await createInvoiceFixture(page, request);
    const createResponse = await addPayment(page, request, invoiceExternalId, 50, {
      description: 'payment to delete',
    });
    expect(createResponse.status()).toBe(201);

    const beforeDelete = await paymentsData(request, invoiceExternalId);
    const beforePayload = await beforeDelete.json();
    const paymentExternalId = String(beforePayload.data[0].external_id);

    const deleteResponse = await request.delete(`${PLAYWRIGHT_BASE_URL}/payment/${paymentExternalId}`, {
      failOnStatusCode: false,
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': await fetchCsrfToken(page),
      },
    });

    expect(deleteResponse.status()).toBe(200);

    const afterDelete = await paymentsData(request, invoiceExternalId);
    const afterPayload = await afterDelete.json();
    expect(JSON.stringify(afterPayload)).not.toContain('payment to delete');
  });
});

nonAdminTest.describe('Payments permissions', () => {
  nonAdminTest('denies payment creation without permission', async ({ page, request }) => {
    const admin = await createAdminSession(page);

    try {
      const { invoiceExternalId } = await createInvoiceFixture(admin.page, admin.request);
      const response = await request.post(`${PLAYWRIGHT_BASE_URL}/payment/add-payment/${invoiceExternalId}`, {
        failOnStatusCode: false,
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
        form: {
          amount: 50,
          payment_date: '2020-01-01',
          source: 'bank',
          description: 'forbidden payment',
        },
      });

      expect(response.status()).toBe(403);
    } finally {
      await admin.dispose();
    }
  });

  nonAdminTest('denies payment deletion without permission', async ({ page, request }) => {
    const admin = await createAdminSession(page);

    try {
      const { invoiceExternalId } = await createInvoiceFixture(admin.page, admin.request);
      const created = await addPayment(admin.page, admin.request, invoiceExternalId, 75, {
        description: 'admin-owned payment',
      });
      expect(created.status()).toBe(201);

      const data = await paymentsData(admin.request, invoiceExternalId);
      const payload = await data.json();
      const paymentExternalId = String(payload.data[0].external_id);

      const response = await request.delete(`${PLAYWRIGHT_BASE_URL}/payment/${paymentExternalId}`, {
        failOnStatusCode: false,
        headers: {
          Accept: 'application/json',
          'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
      });

      expect(response.status()).toBe(403);
    } finally {
      await admin.dispose();
    }
  });
});
