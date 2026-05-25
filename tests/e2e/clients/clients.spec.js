const { test: guestTest, expect: guestExpect } = require('@playwright/test');

const { test, expect, nonAdminTest } = require('../../helpers/fixtures');

const { PLAYWRIGHT_BASE_URL, BASE_URL } = require('../../helpers/config');

const {
    loginAsAdmin,
    createClient,
    clientData,
    jsonHeaders,
    expectValidationError,
    uniqueValue,
    usersCollection,
    html,
} = require('../helpers/plain-e2e');

const { fetchCsrfToken } = require('../../helpers/csrf');

const {
    ClientActions,
    DomainAssertions,
} = require('../../helpers/feature-domain');

const malformedId = 'invalid-@@@';

test.describe('Clients feature behavior', () => {
    test('creating a client registers the company in the searchable clients data table', async ({
                                                                                                    page,
                                                                                                }) => {
        await loginAsAdmin(page);

        const request = page.context().request;
        const companyName = uniqueValue('PW Client');

        const { response } = await createClient(
            page,
            request,
            companyName
        );

        const dataResponse = await clientData(
            request,
            companyName
        );

        const dataPayload = await dataResponse.json();

        expect(
            response.status(),
            'Client creation should return 201'
        ).toBe(201);

        expect(
            dataResponse.status(),
            'Clients data table should return 200'
        ).toBe(200);

        const rows = dataPayload.data || [];

        expect(
            rows.some((row) => row.company_name === companyName),
            `Newly created company "${companyName}" should appear in the data table`
        ).toBe(true);
    });

    test('submitting a client form without required fields returns field-level validation errors', async ({
                                                                                                              page,
                                                                                                          }) => {
        await loginAsAdmin(page);

        const request = page.context().request;

        const response = await request.post(`${BASE_URL}/clients`, {
            failOnStatusCode: false,
            headers: await jsonHeaders(page),
            form: {},
        });

        await expectValidationError(response, 'name');
    });

    test('updating a client contact name is reflected on the client detail page', async ({
                                                                                             page,
                                                                                         }) => {
        await loginAsAdmin(page);

        const request = page.context().request;

        const companyName = uniqueValue('PW Client Update');

        const { payload } = await createClient(
            page,
            request,
            companyName
        );

        const clientExternalId = payload.client.external_id;

        const updatedContactName = uniqueValue('PW Contact Updated');

        const { body: editHtml } = await html(
            request,
            `/clients/${clientExternalId}/edit`
        );

        const industryIdMatch = editHtml.match(
            /name="industry_id"[^>]*>[\s\S]*?<option[^>]*value="(\d+)"/
        );

        expect(
            industryIdMatch?.[1],
            'Edit form must expose an industry option'
        ).toBeTruthy();

        const industryId = industryIdMatch[1];

        const userIdMatch = editHtml.match(
            /name="user_id"[^>]*>[\s\S]*?<option[^>]*value="(\d+)"/
        );

        expect(
            userIdMatch?.[1],
            'Edit form must expose a user option'
        ).toBeTruthy();

        const userId = userIdMatch[1];

        const updateResponse = await request.patch(
            `${BASE_URL}/clients/${clientExternalId}`,
            {
                failOnStatusCode: false,
                maxRedirects: 0,
                headers: await jsonHeaders(page),
                form: {
                    name: updatedContactName,
                    company_name: companyName,
                    email: `pw_updated_${Date.now()}@example.com`,
                    primary_number: '11111111',
                    secondary_number: '22222222',
                    vat: `${Date.now()}`.slice(-8),
                    zipcode: '2000',
                    city: 'UpdatedCity',
                    industry_id: industryId,
                    user_id: userId,
                },
            }
        );

        const showResponse = await request.get(
            `${BASE_URL}/clients/${clientExternalId}`,
            {
                failOnStatusCode: false,
            }
        );

        const showHtml = await showResponse.text();

        expect(
            updateResponse.status(),
            'Client update should return a 302 redirect'
        ).toBe(302);

        expect(
            updateResponse.headers().location ?? '',
            'Redirect should not point to login'
        ).not.toContain('/login');

        expect(
            showResponse.status(),
            'Client show page should return 200'
        ).toBe(200);

        expect(
            showHtml,
            'Updated contact name should be visible'
        ).toContain(updatedContactName);
    });

    test('assigning a new user to a client redirects back without hitting the login page', async ({
                                                                                                      page,
                                                                                                  }) => {
        await loginAsAdmin(page);

        const request = page.context().request;

        const companyName = uniqueValue('PW Client Assign');

        const { payload } = await createClient(
            page,
            request,
            companyName
        );

        const clientExternalId = payload.client.external_id;

        const users = await usersCollection(request);

        expect(
            users.length,
            'Client assignment test requires at least one user'
        ).toBeGreaterThan(0);

        const assignee = users[0];

        const updateResponse = await request.patch(
            `${BASE_URL}/clients/updateassign/${clientExternalId}`,
            {
                failOnStatusCode: false,
                maxRedirects: 0,
                headers: await jsonHeaders(page),
                form: {
                    user_external_id: assignee.external_id,
                },
            }
        );

        expect(
            updateResponse.status(),
            'Assignee update should return 302'
        ).toBe(302);

        expect(
            updateResponse.headers().location ?? '',
            'Redirect should not point to login'
        ).not.toContain('/login');
    });

    test('deleting a client removes the company from the clients data table', async ({
                                                                                         page,
                                                                                     }) => {
        await loginAsAdmin(page);

        const request = page.context().request;

        const companyName = uniqueValue('PW Client Delete');

        const { payload } = await createClient(
            page,
            request,
            companyName
        );

        const clientExternalId = payload.client.external_id;

        const deleteResponse = await request.delete(
            `${BASE_URL}/clients/${clientExternalId}`,
            {
                failOnStatusCode: false,
                headers: await jsonHeaders(page),
                maxRedirects: 0,
            }
        );

        const dataResponse = await clientData(
            request,
            companyName
        );

        const dataPayload = await dataResponse.json();

        expect(
            deleteResponse.status(),
            'Client deletion should return a 302 redirect'
        ).toBe(302);

        expect(
            deleteResponse.headers().location ?? '',
            'Delete redirect must not point to login'
        ).not.toContain('/login');

        expect(
            dataResponse.status(),
            'Clients data table should return 200 after delete'
        ).toBe(200);

        const rows = dataPayload.data || [];

        expect(
            rows.some((row) => row.company_name === companyName),
            `Deleted client "${companyName}" must not appear in the data table`
        ).toBe(false);
    });

    test('authenticated user can open clients index page', async ({
                                                                      page,
                                                                  }) => {
        await loginAsAdmin(page);

        await page.goto(`${PLAYWRIGHT_BASE_URL}/clients`);

        await expect(page).toHaveURL(/\/clients/);
    });

    test('authenticated user can create client from UI when form is available', async ({
                                                                                           page,
                                                                                       }) => {
        await loginAsAdmin(page);

        const clientName = `PW Client ${Date.now()}`;

        await page.goto(`${PLAYWRIGHT_BASE_URL}/clients`);

        const createClientButton = page.getByRole('button', {
            name: /create client/i,
        });

        await expect(createClientButton).toBeVisible();

        await createClientButton.click();

        await page.getByPlaceholder(/client name/i).fill(clientName);

        await Promise.all([
            page.waitForResponse((response) => {
                return (
                    response.url().includes('/clients') &&
                    response.request().method() === 'POST' &&
                    [200, 201, 302].includes(response.status())
                );
            }),
            page
                .getByRole('button', {
                    name: /create client/i,
                })
                .last()
                .click(),
        ]);

        await expect(
            page.getByText(clientName)
        ).toBeVisible();
    });

    test('store happy path creates a client visible in clients data', async ({
                                                                                 page,
                                                                                 request,
                                                                             }) => {
        const companyName = `PW Client ${Date.now()}`;

        const { response } = await ClientActions.create(
            page,
            request,
            companyName
        );

        expect(response.status()).toBe(201);

        const dataResponse = await ClientActions.data(
            request,
            companyName
        );

        await DomainAssertions.expectDataContainsTitle(
            dataResponse,
            companyName
        );
    });

    test('store validation failure returns field-level errors', async ({
                                                                           page,
                                                                           request,
                                                                       }) => {
        const response = await request.post(
            `${PLAYWRIGHT_BASE_URL}/clients`,
            {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
                form: {},
            }
        );

        await DomainAssertions.expectValidationError(
            response,
            'name'
        );
    });

    test('create form validation alert is rendered at top of page content', async ({
                                                                                       page,
                                                                                   }) => {
        await loginAsAdmin(page);

        await page.goto(`${PLAYWRIGHT_BASE_URL}/clients/create`);

        await page.locator('#submitClient').click();

        const errorAlert = page
            .locator('.col-lg-12 > .alert.alert-danger')
            .first();

        await expect(errorAlert).toBeVisible();

        const firstChildClassName = await page
            .locator('.col-lg-12 > :first-child')
            .evaluate((element) => element.className);

        expect(firstChildClassName).toContain('alert');
    });

    test('update workflow persists new company_name', async ({
                                                                 page,
                                                                 request,
                                                             }) => {
        const companyName = `PW Client Update ${Date.now()}`;

        const { response } = await ClientActions.create(
            page,
            request,
            companyName
        );

        const payload = await response.json();

        const externalId = payload.client.external_id;

        const updateResponse = await request.patch(
            `${PLAYWRIGHT_BASE_URL}/clients/${externalId}`,
            {
                failOnStatusCode: false,
                headers: {
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
                form: {
                    name: `${companyName} Contact`,
                    company_name: `${companyName} Updated`,
                    email: `${Date.now()}@example.com`,
                    primary_number: '12345678',
                    secondary_number: '87654321',
                    vat: `${Date.now()}`.slice(-8),
                    zipcode: '1000',
                    city: 'Copenhagen',
                    industry_id: payload.client.industry_id,
                    user_id: payload.client.user_id,
                },
            }
        );

        expect(updateResponse.status()).toBe(302);

        const showResponse = await request.get(
            `${PLAYWRIGHT_BASE_URL}/clients/${externalId}`,
            {
                failOnStatusCode: false,
            }
        );

        expect(showResponse.status()).toBe(200);

        const showHtml = await showResponse.text();

        expect(showHtml).toContain(
            `${companyName} Updated`
        );

        const dataResponse = await ClientActions.data(
            request,
            `${companyName} Updated`
        );

        await DomainAssertions.expectDataContainsTitle(
            dataResponse,
            `${companyName} Updated`
        );
    });

    test('delete/archive removes client from listing', async ({
                                                                  page,
                                                                  request,
                                                              }) => {
        const companyName = `PW Client Delete ${Date.now()}`;

        const { response } = await ClientActions.create(
            page,
            request,
            companyName
        );

        const payload = await response.json();

        const externalId = payload.client.external_id;

        const deleteResponse = await request.delete(
            `${PLAYWRIGHT_BASE_URL}/clients/${externalId}`,
            {
                failOnStatusCode: false,
                headers: {
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
            }
        );

        expect(deleteResponse.status()).toBe(302);

        const dataResponse = await ClientActions.data(
            request,
            companyName
        );

        const dataPayload = await dataResponse.json();

        expect(
            JSON.stringify(dataPayload)
        ).not.toContain(companyName);
    });

    test('malformed input update returns not found', async ({
                                                                page,
                                                                request,
                                                            }) => {
        const response = await request.patch(
            `${PLAYWRIGHT_BASE_URL}/clients/${malformedId}`,
            {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                form: {},
            }
        );

        expect(response.status()).toBe(404);
    });

    test('data endpoint and search return structured payload', async ({
                                                                          request,
                                                                      }) => {
        const response = await ClientActions.data(
            request,
            'PW'
        );

        expect(response.status()).toBe(200);

        const payload = await response.json();

        expect(payload).toHaveProperty('data');
    });
});

test('can create a new client with all fields', async ({ page }) => {
    const p = new ClientsPage(page);
    const name = `PW Client ${Date.now()}`;
    await p.create({ company: name, email: 'client@example.test', phone: '123456789', address: 'Main Street 1' });
    await p.assertVisible(name);
});

test('can view seeded Playwright Seed Client', async ({ page }) => {
    const p = new ClientsPage(page);
    await p.assertVisible(SEED_CLIENT_NAME);
});

test("can edit a client's company name", async ({ page }) => {
    const p = new ClientsPage(page);
    const original = `PW Edit ${Date.now()}`;
    const updated = `${original} Updated`;
    await p.create({ company: original, email: `${Date.now()}@example.test` });
    await p.edit(original, { company: updated });
    await p.assertVisible(updated);
});

test('can delete a client created in this test', async ({ page }) => {
    const p = new ClientsPage(page);
    const name = `PW Delete ${Date.now()}`;
    await p.create({ company: name, email: `${Date.now()}@example.test` });
    await p.delete(name);
});

guestTest('guest is redirected when opening clients create flow', async ({
                                                                             page,
                                                                         }) => {
    await page.goto(`${PLAYWRIGHT_BASE_URL}/clients/create`);

    await guestExpect(page).toHaveURL(/login/);
});
