const { test, expect } = require('@playwright/test');

const { PLAYWRIGHT_BASE_URL, BASE_URL } = require('../../helpers/config');

const {
    loginAsAdmin,
    createLead,
    leadData,
    jsonHeaders,
    uniqueValue,
    expectValidationError,
    usersCollection,
} = require('../helpers/plain-e2e');

const { fetchCsrfToken } = require('../../helpers/csrf');

const {
    LeadActions,
    DomainAssertions,
} = require('../../helpers/feature-domain');
const {TEST_USERS} = require("../../fixtures/users");
const {LoginPage} = require("../../pages/LoginPage");

const malformedId = 'invalid-@@@';

test.describe('Leads feature behavior', () => {
    test('creating a lead registers the title in the searchable lead data response', async ({
                                                                                                page,
                                                                                            }) => {
        await loginAsAdmin(page);

        const request = page.context().request;

        const title = uniqueValue('PW Lead');

        const { response } = await createLead(
            page,
            request,
            title
        );

        const dataResponse = await leadData(
            request,
            title
        );

        const dataPayload = await dataResponse.json();

        expect(
            response.status(),
            'Lead creation should return 302 redirect'
        ).toBe(302);

        expect(
            response.headers().location ?? '',
            'Redirect should point to /leads/'
        ).toContain('/leads/');

        expect(
            dataResponse.status(),
            'Lead data feed should return 200'
        ).toBe(200);

        const rows = Array.isArray(dataPayload?.data)
            ? dataPayload.data
            : [];

        expect(
            rows.some((row) => row.title === title),
            `Lead "${title}" should appear in the data response`
        ).toBe(true);
    });

    test('deleting a lead removes its title from the lead data response', async ({
                                                                                     page,
                                                                                 }) => {
        await loginAsAdmin(page);

        const request = page.context().request;

        const title = uniqueValue('PW Lead Delete');

        const { leadExternalId } = await createLead(
            page,
            request,
            title
        );

        const headers = await jsonHeaders(page);

        const deleteResponse = await request.delete(
            `${BASE_URL}/leads/${leadExternalId}/json`,
            {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': headers['X-CSRF-TOKEN'],
                },
            }
        );

        const dataResponse = await leadData(
            request,
            title
        );

        const dataPayload = await dataResponse.json();

        expect(
            deleteResponse.status(),
            'Lead delete endpoint should return 200'
        ).toBe(200);

        expect(
            dataResponse.status(),
            'Lead data feed should return 200 after delete'
        ).toBe(200);

        const rows = Array.isArray(dataPayload?.data)
            ? dataPayload.data
            : [];

        expect(
            rows.some((row) => row.title === title),
            `Deleted lead "${title}" must not appear`
        ).toBe(false);
    });

    test('submitting a lead form without required fields returns a title validation error', async ({
                                                                                                       page,
                                                                                                   }) => {
        await loginAsAdmin(page);

        const request = page.context().request;

        const response = await request.post(
            `${BASE_URL}/leads`,
            {
                failOnStatusCode: false,
                headers: await jsonHeaders(page),
                form: {},
            }
        );

        await expectValidationError(response, 'title');
    });

    test('updating a lead status redirects back to the lead without triggering login redirect', async ({
                                                                                                           page,
                                                                                                       }) => {
        await loginAsAdmin(page);

        const request = page.context().request;

        const { leadExternalId, statusId } = await createLead(
            page,
            request,
            uniqueValue('PW Lead Status')
        );

        const response = await request.patch(
            `${BASE_URL}/leads/updatestatus/${leadExternalId}`,
            {
                failOnStatusCode: false,
                maxRedirects: 0,
                headers: await jsonHeaders(page),
                form: {
                    status_id: statusId,
                },
            }
        );

        expect(
            response.status(),
            'Lead status update should return 302'
        ).toBe(302);

        expect(
            response.headers().location ?? '',
            'Redirect must not send the user to login'
        ).not.toContain('/login');
    });

    test('reassigning a lead to a new user redirects back without triggering login redirect', async ({
                                                                                                         page,
                                                                                                     }) => {
        await loginAsAdmin(page);

        const request = page.context().request;

        const { leadExternalId } = await createLead(
            page,
            request,
            uniqueValue('PW Lead Assign')
        );

        const users = await usersCollection(request);

        expect(
            users.length,
            'Lead reassignment test requires users'
        ).toBeGreaterThan(0);

        const newAssignee = users[0];

        const response = await request.patch(
            `${BASE_URL}/leads/updateassign/${leadExternalId}`,
            {
                failOnStatusCode: false,
                maxRedirects: 0,
                headers: await jsonHeaders(page),
                form: {
                    user_assigned_id: newAssignee.external_id,
                },
            }
        );

        expect(
            response.status(),
            'Lead assignee update should return 302'
        ).toBe(302);

        expect(
            response.headers().location ?? '',
            'Redirect must not point to login'
        ).not.toContain('/login');
    });

    test('store happy path creates a lead and redirects to lead page', async ({
                                                                                  page,
                                                                                  request,
                                                                              }) => {
        await loginAsAdmin(page);

        const title = `PW Lead ${Date.now()}`;

        const { response } = await LeadActions.create(
            page,
            request,
            title
        );

        expect(response.status()).toBe(302);

        expect(
            response.headers().location ?? ''
        ).toContain('/leads/');

        const dataResponse = await LeadActions.data(
            request,
            title
        );

        await DomainAssertions.expectDataContainsTitle(
            dataResponse,
            title
        );
    });

    test('validation failure returns required-field error', async ({
                                                                       page,
                                                                       request,
                                                                   }) => {
        await loginAsAdmin(page);

        const response = await request.post(
            `${PLAYWRIGHT_BASE_URL}/leads`,
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
            'title'
        );
    });

    test('create form validation alert is rendered at top of page content', async ({
                                                                                       page,
                                                                                   }) => {
        await loginAsAdmin(page);

        await page.goto(`${PLAYWRIGHT_BASE_URL}/leads/create`);

        await page
            .locator(
                'form button[type="submit"], form input[type="submit"]'
            )
            .first()
            .click();

        const errorAlert = page
            .locator('.col-lg-12 > .alert.alert-danger')
            .first();

        await expect(errorAlert).toBeVisible();

        const firstChildClassName = await page
            .locator('.col-lg-12 > :first-child')
            .evaluate((element) => element.className);

        expect(firstChildClassName).toContain('alert');
    });

    test('workflow status mutation on malformed input returns not found', async ({
                                                                                     page,
                                                                                     request,
                                                                                 }) => {
        await loginAsAdmin(page);

        const response = await request.patch(
            `${PLAYWRIGHT_BASE_URL}/leads/updatestatus/${malformedId}`,
            {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
                form: {
                    status_id: 1,
                },
            }
        );

        expect(response.status()).toBe(404);
    });

    test('delete workflow removes lead from data endpoint', async ({
                                                                       page,
                                                                       request,
                                                                   }) => {
        await loginAsAdmin(page);

        const title = `PW Lead Delete ${Date.now()}`;

        const { response } = await LeadActions.create(
            page,
            request,
            title
        );

        const leadPath =
            response.headers().location ?? '';

        const leadUrl = new URL(
            leadPath,
            PLAYWRIGHT_BASE_URL
        );

        const externalId = leadUrl.pathname
            .split('/')
            .filter(Boolean)
            .pop();

        const deleteResponse = await request.delete(
            `${PLAYWRIGHT_BASE_URL}/leads/${externalId}/json`,
            {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
            }
        );

        expect(deleteResponse.status()).toBe(200);

        const dataResponse = await LeadActions.data(
            request,
            title
        );

        const payload = await dataResponse.json();

        expect(
            JSON.stringify(payload)
        ).not.toContain(title);
    });

    test('data and search endpoint returns lead collections', async ({
                                                                         page,
                                                                         request,
                                                                     }) => {
        await loginAsAdmin(page);

        const response = await LeadActions.data(
            request,
            'Lead'
        );

        expect(response.status()).toBe(200);

        const payload = await response.json();

        expect(payload).toHaveProperty('data');
    });
});

test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);
});

test('it can create lead', async ({ page }) => {
    /* Arrange */
    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Create ${Date.now()}`;
    await leadsPage.goto();

    /* Act */
    await leadsPage.create({ title: leadTitle, description: 'New lead' });

    /* Assert */
    await leadsPage.assertVisible(leadTitle);
});

test('it returns web error when lead creation throws exception', async ({ page }) => {
    /* Arrange */
    const leadsPage = new LeadsPage(page);
    await leadsPage.goto();

    /* Act */
    await page.getByRole('button', { name: /new lead|create lead/i }).click();
    await page.getByRole('button', { name: /save|create/i }).click();

    /* Assert */
    await expect(page.getByText('The title field is required')).toBeVisible();
});

test('it returns json error when lead creation throws exception', async ({ page }) => {
    /* Arrange */
    const leadsPage = new LeadsPage(page);
    await leadsPage.goto();

    /* Act */
    await page.getByRole('button', { name: /new lead|create lead/i }).click();
    await page.getByRole('button', { name: /save|create/i }).click();

    /* Assert */
    await expect(page.getByText('The title field is required')).toBeVisible();
});

test('it can update assignee', async ({ page }) => {
    /* Arrange */
    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Update Assign ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead to update assignee' });

    /* Act */
    const row = page.getByRole('row', { name: new RegExp(leadTitle, 'i') });
    await row.getByRole('button', { name: /assign/i }).click();
    await page.getByRole('option').first().click();

    /* Assert */
    await expect(page.getByText('Lead updated successfully')).toBeVisible();
});

test('it can update status', async ({ page }) => {
    /* Arrange */
    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Update Status ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead to update status' });

    /* Act */
    await leadsPage.changeStatus(leadTitle, 'won');

    /* Assert */
    await leadsPage.assertStatus(leadTitle, 'won');
});

test('it can update deadline for lead', async ({ page }) => {
    /* Arrange */
    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Deadline ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead to update deadline' });

    /* Act */
    await leadsPage.edit(leadTitle, { deadline: '2026-12-31' });

    /* Assert */
    await expect(page.getByText('Lead updated successfully')).toBeVisible();
});

test('it updates followup stores deadline as datetime string', async ({ page }) => {
    /* Arrange */
    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Followup DateTime ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead followup datetime test' });

    /* Act */
    await leadsPage.edit(leadTitle, { followup: '2026-12-31' });

    /* Assert */
    await expect(page.getByText('Lead updated successfully')).toBeVisible();
});

test('it updates followup stores deadline with correct time component', async ({ page }) => {
    /* Arrange */
    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Followup Time ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead followup time test' });

    /* Act */
    await leadsPage.edit(leadTitle, { followup: '2026-12-31 14:30' });

    /* Assert */
    await expect(page.getByText('Lead updated successfully')).toBeVisible();
});

test('it updates followup deadline is stored as parseable date in database', async ({ page }) => {
    /* Arrange */
    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Followup Parse ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead followup parse test' });

    /* Act */
    await leadsPage.edit(leadTitle, { followup: '2026-12-31' });

    /* Assert */
    await expect(page.getByText('Lead updated successfully')).toBeVisible();
});

test('it deletes lead', async ({ page }) => {
    /* Arrange */
    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Delete ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead to delete' });
    await leadsPage.assertVisible(leadTitle);

    /* Act */
    await leadsPage.delete(leadTitle);

    /* Assert */
    await expect(page.getByText(leadTitle)).not.toBeVisible();
});

test('it deletes offers if flag given', async ({ page }) => {
    /* Arrange */
    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead with Offers ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead with offers' });
    await leadsPage.assertVisible(leadTitle);

    /* Act */
    await leadsPage.delete(leadTitle);

    /* Assert */
    await expect(page.getByText(leadTitle)).not.toBeVisible();
});

test('it does not delete offers if flag is not given but remove reference', async ({ page }) => {
    /* Arrange */
    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Offer Ref ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead with offer reference' });
    await leadsPage.assertVisible(leadTitle);

    /* Act */
    await leadsPage.delete(leadTitle);

    /* Assert */
    await expect(page.getByText(leadTitle)).not.toBeVisible();
});

test('it can delete lead if flag is given and offers does not exists', async ({ page }) => {
    /* Arrange */
    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead No Offers ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead without offers' });
    await leadsPage.assertVisible(leadTitle);

    /* Act */
    await leadsPage.delete(leadTitle);

    /* Assert */
    await expect(page.getByText(leadTitle)).not.toBeVisible();
});

test('it authorized user can reassign lead', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Reassign ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead to reassign' });

    /* Act */
    const row = page.getByRole('row', { name: new RegExp(leadTitle, 'i') });
    await row.getByRole('button', { name: /assign/i }).click();
    await page.getByRole('option').first().click();

    /* Assert */
    await expect(page.getByRole('alert').filter({ hasText: /assigned|updated successfully/i })).toBeVisible();
});

test('it unauthorized user cannot reassign lead', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.employee.email, TEST_USERS.employee.password);

    const leadsPage = new LeadsPage(page);
    await leadsPage.goto();

    /* Act */
    const firstRow = page.getByRole('row').nth(1);
    await firstRow.getByRole('button', { name: /assign/i }).click();

    /* Assert */
    await expect(page.getByRole('alert').filter({ hasText: /forbidden|unauthorized|permission denied/i })).toBeVisible();
});

test('it user with lead delete permission can delete lead', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Auth Delete ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead to test delete permission' });

    /* Act */
    const row = page.getByRole('row', { name: new RegExp(leadTitle, 'i') });
    const deleteButton = row.getByRole('button', { name: /delete/i });

    /* Assert */
    await expect(deleteButton).toBeVisible();
});

test('it user without lead delete permission cannot delete lead', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.employee.email, TEST_USERS.employee.password);

    const leadsPage = new LeadsPage(page);
    await leadsPage.goto();

    /* Act */
    const firstRow = page.getByRole('row').nth(1);
    const deleteButton = firstRow.getByRole('button', { name: /delete/i });

    /* Assert */
    await expect(deleteButton).not.toBeVisible();
});

test('it lead update assign only accepts user assigned id field', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Assign Field ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Test assign field validation' });

    /* Act */
    const row = page.getByRole('row', { name: new RegExp(leadTitle, 'i') });
    await row.getByRole('button', { name: /assign/i }).click();
    await page.getByRole('option').first().click();

    /* Assert */
    await expect(page.getByRole('alert').filter({ hasText: /assigned|updated/i })).toBeVisible();
});

test('it lead update status only accepts status id field', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Status Field ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Test status field validation' });

    /* Act */
    await leadsPage.changeStatus(leadTitle, 'closed');

    /* Assert */
    await expect(page.getByRole('alert').filter({ hasText: /status updated|updated successfully/i })).toBeVisible();
});

test('it authorized user can delete lead', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Sec Delete ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead security delete test' });

    /* Act */
    await leadsPage.delete(leadTitle);

    /* Assert */
    await expect(page.getByRole('alert').filter({ hasText: /deleted successfully|removed/i })).toBeVisible();
});

test('it unauthorized user cannot delete lead', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.employee.email, TEST_USERS.employee.password);

    const leadsPage = new LeadsPage(page);
    await leadsPage.goto();

    /* Act */
    const firstRow = page.getByRole('row').nth(1);

    /* Assert */
    await expect(firstRow.getByRole('button', { name: /delete/i })).not.toBeVisible();
});

test('it unauthorized user cannot delete lead via json', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.employee.email, TEST_USERS.employee.password);

    const leadsPage = new LeadsPage(page);
    await leadsPage.goto();

    /* Act */
    const response = await page.request.delete('/leads/1');

    /* Assert */
    expect(response.status()).toBe(403);
});

test('it updates assign only accepts user assigned id field', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Sec Assign ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead security assign test' });

    /* Act */
    const row = page.getByRole('row', { name: new RegExp(leadTitle, 'i') });
    await row.getByRole('button', { name: /assign/i }).click();
    await page.getByRole('option').first().click();

    /* Assert */
    await expect(page.getByRole('alert').filter({ hasText: /assigned|updated successfully/i })).toBeVisible();
});

test('it updates status only accepts status id field', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    const leadsPage = new LeadsPage(page);
    const leadTitle = `PW Lead Sec Status ${Date.now()}`;
    await leadsPage.goto();
    await leadsPage.create({ title: leadTitle, description: 'Lead security status test' });

    /* Act */
    await leadsPage.changeStatus(leadTitle, 'closed');

    /* Assert */
    await expect(page.getByRole('alert').filter({ hasText: /status updated|updated successfully/i })).toBeVisible();
});

test('it updates status rejects invalid status type', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    const leadsPage = new LeadsPage(page);
    await leadsPage.goto();

    /* Act */
    const response = await page.request.patch('/leads/1/status', {
        data: { status: 'invalid_status_type' }
    });

    /* Assert */
    expect(response.status()).toBe(422);
});

test('it updates status rejects nonexistent status id', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    const leadsPage = new LeadsPage(page);
    await leadsPage.goto();

    /* Act */
    const response = await page.request.patch('/leads/1/status', {
        data: { status_id: 99999 }
    });

    /* Assert */
    expect(response.status()).toBe(422);
});

test('can create a lead on the seed client', async ({ page }) => {
    test.skip(!['owner', 'manager'].includes(test.info().project.name));
    const p = new LeadsPage(page);
    const title = `PW Lead ${Date.now()}`;
    await p.create({ title, client: SEED_CLIENT_NAME });
    await p.assertVisible(title);
});

test('can view the three seeded leads by title', async ({ page }) => {
    test.skip(!['owner', 'manager'].includes(test.info().project.name));
    const p = new LeadsPage(page);

    for (const title of SEED_LEAD_TITLES) {
        await p.assertVisible(title);
    }
});

test("can edit a lead's title", async ({ page }) => {
    test.skip(!['owner', 'manager'].includes(test.info().project.name));
    const p = new LeadsPage(page);
    const title = `PW Lead Edit ${Date.now()}`;
    const updated = `${title} Updated`;
    await p.create({ title, client: SEED_CLIENT_NAME });
    await p.edit(title, { title: updated });
    await p.assertVisible(updated);
});

test("can change a lead's status", async ({ page }) => {
    test.skip(!['owner', 'manager'].includes(test.info().project.name));
    const p = new LeadsPage(page);
    const title = `PW Lead Status ${Date.now()}`;
    await p.create({ title, client: SEED_CLIENT_NAME });
    await p.changeStatus(title, 'Won');
    await p.assertStatus(title, 'Won');
});

test('can delete a lead created in this test', async ({ page }) => {
    test.skip(!['owner', 'manager'].includes(test.info().project.name));
    const p = new LeadsPage(page);
    const title = `PW Lead Delete ${Date.now()}`;
    await p.create({ title, client: SEED_CLIENT_NAME });
    await p.delete(title);
});

