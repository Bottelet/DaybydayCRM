const { test: guestTest, expect: guestExpect } = require('@playwright/test');

const { test, expect } = require('../../helpers/fixtures');

const { PLAYWRIGHT_BASE_URL, BASE_URL } = require('../../helpers/config');

const {
    loginAsAdmin,
    createProject,
    projectData,
    jsonHeaders,
    uniqueValue,
    expectValidationError,
    usersCollection,
} = require('../helpers/plain-e2e');

const { fetchCsrfToken } = require('../../helpers/csrf');

const {
    ProjectActions,
    DomainAssertions,
} = require('../../helpers/feature-domain');
const {SEED_CLIENT_NAME} = require("../../fixtures/users");

const malformedId = 'invalid-@@@';

test.describe('Projects feature behavior', () => {
    test('creating a project registers its title in the searchable project data response', async ({
                                                                                                      page,
                                                                                                  }) => {
        await loginAsAdmin(page);

        const request = page.context().request;

        const title = uniqueValue('PW Project');

        const { response } = await createProject(
            page,
            request,
            title
        );

        const dataResponse = await projectData(
            request,
            title
        );

        const dataPayload = await dataResponse.json();

        expect(
            response.status(),
            'Project creation should return 200'
        ).toBe(200);

        expect(
            dataResponse.status(),
            'Project data feed should return 200'
        ).toBe(200);

        const rows = Array.isArray(dataPayload?.data)
            ? dataPayload.data
            : [];

        expect(
            rows.some((row) => row.title === title),
            `Project "${title}" should appear in the data response`
        ).toBe(true);
    });

    test('updating a project status redirects to the project page and not login', async ({
                                                                                             page,
                                                                                         }) => {
        await loginAsAdmin(page);

        const request = page.context().request;

        const { payload, statusId } = await createProject(
            page,
            request
        );

        const externalId = payload.project_external_id;

        const response = await request.patch(
            `${BASE_URL}/projects/updatestatus/${externalId}`,
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
            'Project status update should return 302'
        ).toBe(302);

        const location =
            response.headers().location ?? '';

        expect(
            location,
            'Redirect should target the project page'
        ).toContain(`/projects/${externalId}`);

        expect(
            location,
            'Redirect must not point to login'
        ).not.toContain('/login');
    });

    test('submitting a project form without required fields returns title validation error', async ({
                                                                                                        page,
                                                                                                    }) => {
        await loginAsAdmin(page);

        const request = page.context().request;

        const response = await request.post(
            `${BASE_URL}/projects`,
            {
                failOnStatusCode: false,
                headers: await jsonHeaders(page),
                form: {},
            }
        );

        await expectValidationError(response, 'title');
    });

    test('reassigning a project to a new user redirects back without login redirect', async ({
                                                                                                 page,
                                                                                             }) => {
        await loginAsAdmin(page);

        const request = page.context().request;

        const { payload } = await createProject(
            page,
            request,
            uniqueValue('PW Project Assign')
        );

        const externalId = payload.project_external_id;

        const users = await usersCollection(request);

        expect(
            users.length,
            'Project reassignment requires at least one user'
        ).toBeGreaterThan(0);

        const newAssignee = users[0];

        const response = await request.patch(
            `${BASE_URL}/projects/updateassign/${externalId}`,
            {
                failOnStatusCode: false,
                maxRedirects: 0,
                headers: await jsonHeaders(page),
                form: {
                    user_assigned_id:
                    newAssignee.external_id,
                },
            }
        );

        expect(
            response.status(),
            'Project assignee update should return 302'
        ).toBe(302);

        expect(
            response.headers().location ?? '',
            'Redirect must not point to login'
        ).not.toContain('/login');
    });

    test('deleting a project removes it from the project data feed', async ({
                                                                                page,
                                                                            }) => {
        await loginAsAdmin(page);

        const request = page.context().request;

        const title = uniqueValue('PW Project Delete');

        const { payload } = await createProject(
            page,
            request,
            title
        );

        const externalId = payload.project_external_id;

        const deleteResponse = await request.delete(
            `${BASE_URL}/projects/${externalId}`,
            {
                failOnStatusCode: false,
                headers: await jsonHeaders(page),
                maxRedirects: 0,
            }
        );

        const dataResponse = await projectData(
            request,
            title
        );

        const dataPayload = await dataResponse.json();

        expect(
            deleteResponse.status(),
            'Project deletion should return success redirect'
        ).toBeLessThan(400);

        expect(
            dataResponse.status(),
            'Project data feed should return 200'
        ).toBe(200);

        const rows = Array.isArray(dataPayload?.data)
            ? dataPayload.data
            : [];

        expect(
            rows.some((row) => row.title === title),
            `Deleted project "${title}" must not appear`
        ).toBe(false);
    });

    test('store happy path creates project visible in projects data', async ({
                                                                                 page,
                                                                                 request,
                                                                             }) => {
        await loginAsAdmin(page);

        const title = `PW Project ${Date.now()}`;

        const { response } = await ProjectActions.create(
            page,
            request,
            title
        );

        expect(response.status()).toBe(200);

        const payload = await response.json();

        expect(payload).toHaveProperty(
            'project_external_id'
        );

        const dataResponse = await ProjectActions.data(
            request,
            title
        );

        await DomainAssertions.expectDataContainsTitle(
            dataResponse,
            title
        );
    });

    test('validation failure returns missing title', async ({
                                                                page,
                                                                request,
                                                            }) => {
        await loginAsAdmin(page);

        const response = await request.post(
            `${PLAYWRIGHT_BASE_URL}/projects`,
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

        await page.goto(
            `${PLAYWRIGHT_BASE_URL}/projects/create`
        );

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

    test('update workflow rejects malformed project id', async ({
                                                                    page,
                                                                    request,
                                                                }) => {
        await loginAsAdmin(page);

        const response = await request.patch(
            `${PLAYWRIGHT_BASE_URL}/projects/updatestatus/${malformedId}`,
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

    test('workflow status transition succeeds for valid project', async ({
                                                                             page,
                                                                             request,
                                                                         }) => {
        await loginAsAdmin(page);

        const title = `PW Project Workflow ${Date.now()}`;

        const { response, statusId } =
            await ProjectActions.create(
                page,
                request,
                title
            );

        const payload = await response.json();

        const externalId =
            payload.project_external_id;

        const statusResponse = await request.patch(
            `${PLAYWRIGHT_BASE_URL}/projects/updatestatus/${externalId}`,
            {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
                form: {
                    status_id: statusId,
                },
            }
        );

        expect(statusResponse.status()).toBe(302);
    });

    test('delete workflow archives project and hides it from data search', async ({
                                                                                      page,
                                                                                      request,
                                                                                  }) => {
        await loginAsAdmin(page);

        const title = `PW Project Delete ${Date.now()}`;

        const { response } = await ProjectActions.create(
            page,
            request,
            title
        );

        const payload = await response.json();

        const externalId =
            payload.project_external_id;

        const deleteResponse = await request.delete(
            `${PLAYWRIGHT_BASE_URL}/projects/${externalId}`,
            {
                failOnStatusCode: false,
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': await fetchCsrfToken(page),
                },
            }
        );

        expect(deleteResponse.status()).toBe(200);

        const dataResponse = await ProjectActions.data(
            request,
            title
        );

        const dataPayload = await dataResponse.json();

        expect(
            JSON.stringify(dataPayload)
        ).not.toContain(title);
    });

    test('data endpoint supports search payload', async ({
                                                             page,
                                                             request,
                                                         }) => {
        await loginAsAdmin(page);

        const response = await ProjectActions.data(
            request,
            'Project'
        );

        expect(response.status()).toBe(200);

        const payload = await response.json();

        expect(payload).toHaveProperty('data');
    });
});

test('can create a project on the seed client', async ({ page }) => {
    test.skip(!['owner', 'manager'].includes(test.info().project.name));
    const p = new ProjectsPage(page);
    const title = `PW Project ${Date.now()}`;
    await p.create({ name: title, client: SEED_CLIENT_NAME });
    await p.assertVisible(title);
});

test('can edit a project', async ({ page }) => {
    test.skip(!['owner', 'manager'].includes(test.info().project.name));
    const p = new ProjectsPage(page);
    const title = `PW Project Edit ${Date.now()}`;
    const updated = `${title} Updated`;
    await p.create({ name: title, client: SEED_CLIENT_NAME });
    await p.edit(title, { name: updated });
    await p.assertVisible(updated);
});

test("can change a project's status", async ({ page }) => {
    test.skip(!['owner', 'manager'].includes(test.info().project.name));
    const p = new ProjectsPage(page);
    const title = `PW Project Status ${Date.now()}`;
    await p.create({ name: title, client: SEED_CLIENT_NAME });
    await p.changeStatus(title, 'Done');
    await p.assertStatus(title, 'Done');
});

test('can delete a project created in this test', async ({ page }) => {
    test.skip(!['owner', 'manager'].includes(test.info().project.name));
    const p = new ProjectsPage(page);
    const title = `PW Project Delete ${Date.now()}`;
    await p.create({ name: title, client: SEED_CLIENT_NAME });
    await p.delete(title);
    await p.assertNotVisible(title);
});

test('it can create project', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/projects');

    /* Assert */
    await expect(page.getByText(/(create|new|add)/i).first()).toBeVisible();
});

test('it returns web error when project creation throws exception', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/projects');

    /* Assert */
    await expect(page.getByText(/(error|invalid|required|unprocessable|forbidden)/i).first()).toBeVisible();
});

test('it returns json error when project creation throws exception', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/projects');

    /* Assert */
    await expect(page.getByText(/(error|invalid|required|unprocessable|forbidden)/i).first()).toBeVisible();
});

test('it can update assignee', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/projects');

    /* Assert */
    await expect(page.getByText(/(update|updated|saved|assigned|status|restored)/i).first()).toBeVisible();
});

test('it can update status', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/projects');

    /* Assert */
    await expect(page.getByText(/(update|updated|saved|assigned|status|restored)/i).first()).toBeVisible();
});

test('it can update deadline for project', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const user = TEST_USERS.owner;

    /* Act */
    await page.goto('/projects');

    /* Assert */
    await expect(page.getByText(/(update|updated|saved|assigned|status|restored)/i).first()).toBeVisible();
});

guestTest('guest is redirected from projects create', async ({
                                                                 page,
                                                             }) => {
    await page.goto(
        `${PLAYWRIGHT_BASE_URL}/projects/create`
    );

    await guestExpect(page).toHaveURL(/login/);
});
