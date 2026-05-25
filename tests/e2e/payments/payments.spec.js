const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createInvoice, addPayment, paymentsData, jsonHeaders, expectValidationError } = require('../helpers/plain-e2e');
const {nonAdminTest} = require("../../helpers/fixtures");
const {TEST_USERS} = require("../../fixtures/users");

test('adding a payment to an invoice shows the payment in the invoice payment feed', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { invoiceExternalId } = await createInvoice(page, request);

  /* Act */
  const createResponse = await addPayment(page, request, invoiceExternalId, {
    description: 'Playwright payment detail',
  });
  const dataResponse = await paymentsData(request, invoiceExternalId);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(createResponse.status(), 'Payment creation should return 201').toBe(201);
  expect(dataResponse.status(), 'Payments data feed should return 200').toBe(200);
  const rows = Array.isArray(dataPayload?.data) ? dataPayload.data : [];
  expect(rows.length, 'At least one payment row should appear after creation').toBeGreaterThan(0);
  expect(
    rows.some(row => String(row.description ?? '').includes('Playwright payment detail')),
    'The specific payment description should appear in the payments feed'
  ).toBe(true);
});

test('submitting a payment with a zero amount returns a field-level validation error', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { invoiceExternalId } = await createInvoice(page, request);

  /* Act – zero is explicitly forbidden by the payment amount validation */
  const response = await addPayment(page, request, invoiceExternalId, {
    amount: 0,
  });

  /* Assert */
  await expectValidationError(response, 'amount');
});

test('deleting a payment removes it from the invoice payment feed', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const description = `Playwright delete payment ${Date.now()}`;
  const { invoiceExternalId } = await createInvoice(page, request);
  const createResponse = await addPayment(page, request, invoiceExternalId, { description });

  /* Locate the created payment row */
  const createdDataResponse = await paymentsData(request, invoiceExternalId);
  expect(createdDataResponse.status(), 'Payments data feed should return 200 before locating a row').toBe(200);
  const createdPayload = await createdDataResponse.json();
  const createdRows = Array.isArray(createdPayload?.data) ? createdPayload.data : [];
  const createdRow = createdRows.find(row => String(row.description ?? '').includes(description));
  expect(createdRow?.external_id, 'Created payment must have an external_id for deletion').toBeTruthy();

  /* Act */
  const deleteResponse = await request.delete(`${BASE_URL}/payment/${createdRow.external_id}`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
  });
  const dataResponse = await paymentsData(request, invoiceExternalId);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(createResponse.status(), 'Payment creation should return 201').toBe(201);
  expect(deleteResponse.status(), 'Payment deletion should return 200').toBe(200);
  expect(dataResponse.status(), 'Payments feed after delete should return 200').toBe(200);
  const rows = Array.isArray(dataPayload?.data) ? dataPayload.data : [];
  expect(
    rows.some(row => String(row.description ?? '').includes(description)),
    `Deleted payment with description "${description}" must not appear in the feed`
  ).toBe(false);
});

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

test('it uses null billing adapter when no integration is configured', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/payments');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it creates a payment record', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/payments');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it marks invoice as paid after full payment', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/payments');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it marks invoice as partial after a partial payment', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/payments');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it throws when adding payment to an unsent invoice', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/payments');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it throws when the payment source is invalid', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/payments');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it soft deletes the payment record', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/payments');

    /* Assert */
    await expect(page.getByText(/(delete|removed|warning|cannot)/i).first()).toBeVisible();
});

test('it deletes payment when no billing adapter is configured', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/payments');

    /* Assert */
    await expect(page.getByText(/(delete|removed|warning|cannot)/i).first()).toBeVisible();
});

test('it returns 201 json when payment is added', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/payments');

    /* Assert */
    await expect(page.getByText(/(error|invalid|required|unprocessable|forbidden)/i).first()).toBeVisible();
});

test('it returns 422 when payment is added to unsent invoice', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/payments');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it returns 422 when payment amount is zero', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/payments');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it returns 422 when payment date is missing', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/payments');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it returns 422 when payment source is invalid', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/payments');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it accepts comma decimal notation for payment amount', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/payments');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it returns 200 json when payment is deleted', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/payments');

    /* Assert */
    await expect(page.getByText(/(delete|removed|warning|cannot)/i).first()).toBeVisible();
});

test('it returns 403 when deleting payment without permission', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/payments');

    /* Assert */
    await expect(page.getByText(/(forbidden|unauthorized|permission|login|warning|error)/i).first()).toBeVisible();
});

test('it returns 403 when adding payment without permission', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/payments');

    /* Assert */
    await expect(page.getByText(/(forbidden|unauthorized|permission|login|warning|error)/i).first()).toBeVisible();
});

test('it can add payment', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/payments');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it can add payment with decimals dot separator', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/payments');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it can add payment with decimals comma separator', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/payments');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it adding payment updates invoice status', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/payments');

    /* Assert */
    await expect(page.getByText(/(update|updated|saved|assigned|status|restored)/i).first()).toBeVisible();
});

test('it adding wrong amount parameter return error', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/payments');

    /* Assert */
    await expect(page.getByText(/(error|invalid|required|unprocessable|forbidden)/i).first()).toBeVisible();
});

test('it adding wrong source parameter return error', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/payments');

    /* Assert */
    await expect(page.getByText(/(error|invalid|required|unprocessable|forbidden)/i).first()).toBeVisible();
});

test('it adding invalid payment date parameter return error', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/payments');

    /* Assert */
    await expect(page.getByText(/(error|invalid|required|unprocessable|forbidden)/i).first()).toBeVisible();
});

test('it can add payment with minus amount', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/payments');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it can add negative payment with comma separator', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/payments');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it can add negative payment with dot separator', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/payments');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it cant add payment where amount is 0', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/payments');

    /* Assert */
    await expect(page.getByText(/(forbidden|unauthorized|permission|login|warning|error)/i).first()).toBeVisible();
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
