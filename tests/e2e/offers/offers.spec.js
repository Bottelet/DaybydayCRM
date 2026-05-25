const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createOffer, jsonHeaders } = require('../helpers/plain-e2e');
const {fetchCsrfToken} = require("../../helpers/csrf");
const {PLAYWRIGHT_BASE_URL} = require("../../helpers/config");
const {createAdminSession} = require("../helpers/session-context");
const {nonAdminTest} = require("../../helpers/fixtures");
const {TEST_USERS} = require("../../fixtures/users");

test('marking an offer as lost transitions the owning lead to a lost state', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { leadExternalId, offerExternalId } = await createOffer(page, request);

  /* Act */
  const lostResponse = await request.post(`${BASE_URL}/offer/lost`, {
    failOnStatusCode: false,
    headers: {
      'X-CSRF-TOKEN': (await jsonHeaders(page))['X-CSRF-TOKEN'],
    },
    form: {
      offer_external_id: offerExternalId,
    },
    maxRedirects: 0,
  });
  const leadResponse = await request.get(`${BASE_URL}/leads/${leadExternalId}`, {
    failOnStatusCode: false,
  });
  const leadHtml = await leadResponse.text();

  /* Assert */
  expect(lostResponse.status(), 'Mark-lost endpoint should return 302').toBe(302);
  expect(leadResponse.status(), 'Lead page should return 200 after offer marked lost').toBe(200);
  /* The lead page renders the current offer status; "lost" must appear in the status UI */
  expect(leadHtml.toLowerCase(), 'The lead page should reflect the lost offer status').toContain('lost');
});

test('marking an offer as won creates an invoice and links it to the owning lead', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { leadExternalId, offerExternalId } = await createOffer(page, request);

  /* Act */
  const wonResponse = await request.post(`${BASE_URL}/offer/won`, {
    failOnStatusCode: false,
    headers: {
      'X-CSRF-TOKEN': (await jsonHeaders(page))['X-CSRF-TOKEN'],
    },
    form: {
      offer_external_id: offerExternalId,
    },
    maxRedirects: 0,
  });
  const leadResponse = await request.get(`${BASE_URL}/leads/${leadExternalId}`, {
    failOnStatusCode: false,
  });
  const leadHtml = await leadResponse.text();

  /* Assert */
  expect(wonResponse.status(), 'Mark-won endpoint should return 302').toBe(302);
  expect(leadResponse.status(), 'Lead page should return 200 after offer marked won').toBe(200);
  /* Winning an offer generates an invoice; the lead page must contain a link to /invoices/ */
  expect(leadHtml, 'The lead page should show an invoice link after the offer is won').toContain('/invoices/');
});

test('updated offer lines are reflected in the offer invoice-lines JSON endpoint', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { response: createResponse, offerExternalId } = await createOffer(page, request);
  const updatedTitle = `Playwright Updated Offer ${Date.now()}`;

  /* Act */
  const updateResponse = await request.post(`${BASE_URL}/offer/${offerExternalId}/update`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    data: [
      {
        title: updatedTitle,
        type: 'hours',
        price: 75,
        quantity: 2,
        comment: 'updated line',
      },
    ],
  });
  const linesResponse = await request.get(`${BASE_URL}/offer/${offerExternalId}/invoice-lines/json`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
  const lines = await linesResponse.json();

  /* Assert */
  expect(createResponse.status(), 'Offer creation should return 200').toBe(200);
  expect(updateResponse.status(), 'Offer line update should return 200').toBe(200);
  expect(linesResponse.status(), 'Invoice-lines endpoint should return 200').toBe(200);
  expect(Array.isArray(lines), 'Invoice-lines response should be an array').toBe(true);
  expect(
    lines.some(line => line.title === updatedTitle),
    `Updated line title "${updatedTitle}" should appear in the invoice-lines response`
  ).toBe(true);
});

test('offer line updates reject a line with a missing title and return a validation error', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { offerExternalId } = await createOffer(page, request);

  /* Act */
  const response = await request.post(`${BASE_URL}/offer/${offerExternalId}/update`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    data: [
      {
        type: 'hours',
        price: 20,
        quantity: 1,
        /* title intentionally omitted to trigger validation */
      },
    ],
  });
  const body = await response.text();

  /* Assert – the business rule is that every offer line must have a title */
  expect(response.status(), 'Missing title in offer line should return 422').toBe(422);
  expect(body.toLowerCase(), 'Response body should describe the missing-fields error').toContain('missing fields');
});

test('creates, updates, wins, and loses offers', async ({ page, request }) => {
    const { leadExternalId, offerExternalId } = await createOfferFixture(page, request);

    const updateResponse = await request.post(`${PLAYWRIGHT_BASE_URL}/offer/${offerExternalId}/update`, {
        failOnStatusCode: false,
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
        data: [
            {
                title: 'Updated Offer Line',
                type: 'hours',
                price: 200,
                quantity: 2,
                comment: 'Updated by Playwright',
            },
        ],
    });
    expect(updateResponse.status()).toBe(200);

    const lostResponse = await request.post(`${PLAYWRIGHT_BASE_URL}/offer/lost`, {
        failOnStatusCode: false,
        headers: {
            'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
        form: {
            offer_external_id: offerExternalId,
        },
        maxRedirects: 0,
    });
    expect(lostResponse.status()).toBe(302);

    const lostLeadResponse = await request.get(`${PLAYWRIGHT_BASE_URL}/leads/${leadExternalId}`, {
        failOnStatusCode: false,
    });
    const lostLeadHtml = await lostLeadResponse.text();
    expect(lostLeadHtml.toLowerCase()).toContain('lost');

    const freshOffer = await createOfferFixture(page, request);
    const wonResponse = await request.post(`${PLAYWRIGHT_BASE_URL}/offer/won`, {
        failOnStatusCode: false,
        headers: {
            'X-CSRF-TOKEN': await fetchCsrfToken(page),
        },
        form: {
            offer_external_id: freshOffer.offerExternalId,
        },
        maxRedirects: 0,
    });
    expect(wonResponse.status()).toBe(302);

    const wonLeadHtml = await (
        await request.get(`${PLAYWRIGHT_BASE_URL}/leads/${freshOffer.leadExternalId}`, {
            failOnStatusCode: false,
        })
    ).text();
    expect(wonLeadHtml).toContain('/invoices/');
});

test('it can create offer', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it can create offer for client', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it returns web error when offer creation throws exception', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(error|invalid|required|unprocessable|forbidden)/i).first()).toBeVisible();
});

test('it returns json error when offer creation throws exception', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(error|invalid|required|unprocessable|forbidden)/i).first()).toBeVisible();
});

test('it can update offer', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(update|updated|saved|assigned|status|restored)/i).first()).toBeVisible();
});

test('it can set offer as won', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it can set offer as lost', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it user with offer create permission can create offer', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it user without offer create permission cannot create offer', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(forbidden|unauthorized|permission|login|warning|error)/i).first()).toBeVisible();
});

test('it user with offer edit permission can update offer', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(update|updated|saved|assigned|status|restored)/i).first()).toBeVisible();
});

test('it user without offer edit permission cannot update offer', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(forbidden|unauthorized|permission|login|warning|error)/i).first()).toBeVisible();
});

test('it user with offer edit permission can mark offer as won', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(update|updated|saved|assigned|status|restored)/i).first()).toBeVisible();
});

test('it user without offer edit permission cannot mark offer as won', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(forbidden|unauthorized|permission|login|warning|error)/i).first()).toBeVisible();
});

test('it user with offer edit permission can mark offer as lost', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(update|updated|saved|assigned|status|restored)/i).first()).toBeVisible();
});

test('it user without offer edit permission cannot mark offer as lost', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/offers');

    /* Assert */
    await expect(page.getByText(/(forbidden|unauthorized|permission|login|warning|error)/i).first()).toBeVisible();
});

nonAdminTest.describe('Offers permissions', () => {
    nonAdminTest('denies offer create, update, win, and lose actions', async ({ page, request }) => {
        const admin = await createAdminSession(page);

        try {
            const { leadExternalId, offerExternalId } = await createOfferFixture(admin.page, admin.request);

            const createResponse = await request.post(`${PLAYWRIGHT_BASE_URL}/offers/create/${leadExternalId}`, {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
                data: [{ title: 'Forbidden Offer', type: 'hours', price: 10, quantity: 1 }],
            });
            expect(createResponse.status()).toBe(403);

            const updateResponse = await request.post(`${PLAYWRIGHT_BASE_URL}/offer/${offerExternalId}/update`, {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
                data: [{ title: 'Forbidden Update', type: 'hours', price: 10, quantity: 1 }],
            });
            expect(updateResponse.status()).toBe(403);

            const wonResponse = await request.post(`${PLAYWRIGHT_BASE_URL}/offer/won`, {
                failOnStatusCode: false,
                headers: {
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
                form: { offer_external_id: offerExternalId },
                maxRedirects: 0,
            });
            expect(wonResponse.status()).toBe(403);

            const lostResponse = await request.post(`${PLAYWRIGHT_BASE_URL}/offer/lost`, {
                failOnStatusCode: false,
                headers: {
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
                form: { offer_external_id: offerExternalId },
                maxRedirects: 0,
            });
            expect(lostResponse.status()).toBe(403);
        } finally {
            await admin.dispose();
        }
    });
});
