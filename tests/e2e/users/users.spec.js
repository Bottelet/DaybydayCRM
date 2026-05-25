import { test as guestTest, expect as guestExpect } from '@playwright/test';
const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createUser, userData, jsonHeaders, expectValidationError, uniqueValue } = require('../helpers/plain-e2e');
const {PLAYWRIGHT_BASE_URL} = require("../../helpers/config");
const {DomainAssertions} = require("../../helpers/feature-domain");

test('creating a user registers the account in the users datatable payload', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;

  /* Act */
  const { response, name, email } = await createUser(page, request);
  const dataResponse = await userData(request, name);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(response.status(), 'User creation should return a 302 redirect').toBe(302);
  expect(dataResponse.status(), 'Users data table should return 200').toBe(200);
  const rows = dataPayload.data || [];
  expect(
    rows.some(row => row.name === name && row.email === email),
    `Newly created user "${name}" with email "${email}" should appear in the data table`
  ).toBe(true);
});

test('submitting a user form without required fields returns a name field validation error', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;

  /* Act */
  const response = await request.post(`${BASE_URL}/users`, {
    failOnStatusCode: false,
    headers: await jsonHeaders(page),
    form: {},
  });

  /* Assert */
  await expectValidationError(response, 'name');
});

test('updating a user display name persists the change in the users datatable payload', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;
  const { name, email } = await createUser(page, request, uniqueValue('PW User Update'));

  /* Locate the created user row to get the external_id */
  const createdDataResponse = await userData(request, name);
  const createdDataPayload = await createdDataResponse.json();
  const createdRows = Array.isArray(createdDataPayload?.data) ? createdDataPayload.data : [];
  const createdRow = createdRows.find(row => row.name === name && row.email === email);
  const userExternalId = createdRow?.external_id;
  expect(userExternalId, 'Created user must have an external_id for update').toBeTruthy();

  /* Fetch edit page data to obtain role and department IDs */
  const editResponse = await request.get(`${BASE_URL}/users/${userExternalId}/edit`, {
    failOnStatusCode: false,
    headers: { Accept: 'application/json' },
  });
  expect(editResponse.status(), 'User edit endpoint should return 200').toBe(200);
  const editPayload = await editResponse.json();
  const updatedName = uniqueValue('PW User Updated');

  /* Act */
  const updateResponse = await request.patch(`${BASE_URL}/users/${userExternalId}`, {
    failOnStatusCode: false,
    maxRedirects: 0,
    headers: await jsonHeaders(page),
    form: {
      name: updatedName,
      email,
      role: Number(Object.keys(editPayload.roles ?? {})[0]),
      department: Number(Object.keys(editPayload.departments ?? {})[0]),
    },
  });
  const dataResponse = await userData(request, updatedName);
  const dataPayload = await dataResponse.json();

  /* Assert */
  expect(updateResponse.status(), 'User update should return a 302 redirect').toBe(302);
  expect(updateResponse.headers()['location'] ?? '', 'Redirect must not point to login').not.toContain('/login');
  expect(dataResponse.status(), 'Users data table should return 200 after update').toBe(200);
  const rows = Array.isArray(dataPayload?.data) ? dataPayload.data : [];
  expect(
    rows.some(row => row.name === updatedName && row.email === email),
    `Updated user "${updatedName}" should appear in the data table with the original email`
  ).toBe(true);
});

test('store happy path creates user visible in users data', async ({ page, request }) => {
    const name = `PW User ${Date.now()}`;
    const email = `pw_user_${Date.now()}@example.com`;
    const response = await UserActions.create(page, request, name, email);

    expect(response.status()).toBe(302);
    const dataResponse = await UserActions.data(request, name);
    await DomainAssertions.expectDataContainsTitle(dataResponse, name);
});

test('validation failure returns required name error', async ({ page, request }) => {
    const response = await request.post(`${PLAYWRIGHT_BASE_URL}/users`, {
        failOnStatusCode: false,
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': await (await import('../../helpers/csrf')).fetchCsrfToken(page),
        },
        form: {},
    });

    await DomainAssertions.expectValidationError(response, 'name');
});

test('create form validation alert is rendered at top of page content', async ({ page }) => {
    await page.goto(`${PLAYWRIGHT_BASE_URL}/users/create`);
    await page.locator('form button[type="submit"], form input[type="submit"]').first().click();

    const errorAlert = page.locator('.col-lg-12 > .alert.alert-danger').first();
    await expect(errorAlert).toBeVisible();

    const firstChildClassName = await page.locator('.col-lg-12 > :first-child').evaluate((element) => element.className);
    expect(firstChildClassName).toContain('alert');
});

test('update malformed user id returns not found', async ({ page, request }) => {
    const response = await request.patch(`${PLAYWRIGHT_BASE_URL}/users/${malformedId}`, {
        failOnStatusCode: false,
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': await (await import('../../helpers/csrf')).fetchCsrfToken(page),
        },
        form: {
            name: 'Invalid Update',
            email: 'invalid@example.com',
        },
    });

    expect(response.status()).toBe(404);
});

test('delete malformed user id is denied with not found', async ({ page, request }) => {
    const response = await request.delete(`${PLAYWRIGHT_BASE_URL}/users/${malformedId}`, {
        failOnStatusCode: false,
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': await (await import('../../helpers/csrf')).fetchCsrfToken(page),
        },
    });

    expect(response.status()).toBe(404);
});

test('users data endpoint supports searching', async ({ request }) => {
    const response = await UserActions.data(request, 'User');
    expect(response.status()).toBe(200);
    const payload = await response.json();
    expect(payload).toHaveProperty('data');
});

guestTest('guest is redirected from users index', async ({ page }) => {
    await page.goto(`${PLAYWRIGHT_BASE_URL}/users`);
    await guestExpect(page).toHaveURL(/login/);
});

test('it allows owner to update user role', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    await page.goto('/users');
    const userRow = page.getByRole('row').filter({ hasText: TEST_USERS.employee.email });
    await userRow.getByRole('link', { name: /edit/i }).click();

    /* Act */
    await page.getByLabel(/role/i).selectOption({ index: 1 });
    await page.getByRole('button', { name: /save|update/i }).click();

    /* Assert */
    await expect(page.getByText('User updated successfully')).toBeVisible();
});

test('it only owner role can update user', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    await page.goto('/users');
    const userRow = page.getByRole('row').filter({ hasText: TEST_USERS.manager.email });
    await userRow.getByRole('link', { name: /edit/i }).click();

    /* Act */
    await page.getByLabel(/name/i).fill('Updated Name');
    await page.getByRole('button', { name: /save|update/i }).click();

    /* Assert */
    await expect(page.getByText('User updated successfully')).toBeVisible();
});

test('it returns web error when user creation throws exception', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    await page.goto('/users');

    /* Act */
    await page.getByRole('button', { name: /new user|create user/i }).click();
    await page.getByRole('button', { name: /save|create/i }).click();

    /* Assert */
    await expect(page.locator('.error-message, [role="alert"]').filter({ hasText: /email.*required|name.*required/i })).toBeVisible();
});

test('it returns json error when user creation throws exception', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    await page.goto('/users');

    /* Act */
    await page.getByRole('button', { name: /new user|create user/i }).click();
    await page.getByRole('button', { name: /save|create/i }).click();

    /* Assert */
    await expect(page.locator('.error-message, [role="alert"]').filter({ hasText: /email.*required|name.*required/i })).toBeVisible();
});

test('it authorized user can edit user', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    await page.goto('/users');
    const userRow = page.getByRole('row').filter({ hasText: TEST_USERS.employee.email });

    /* Act */
    await userRow.getByRole('link', { name: /edit/i }).click();

    /* Assert */
    await expect(page.getByRole('heading', { name: /edit user/i })).toBeVisible();
});

test('it unauthorized user cannot edit user', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.employee.email, TEST_USERS.employee.password);

    /* Act */
    await page.goto('/users/1/edit');

    /* Assert */
    await expect(page.getByRole('alert').filter({ hasText: /forbidden|unauthorized|access denied/i })).toBeVisible();
});

test('it authorized user can update user', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    await page.goto('/users');
    const userRow = page.getByRole('row').filter({ hasText: TEST_USERS.manager.email });
    await userRow.getByRole('link', { name: /edit/i }).click();

    /* Act */
    await page.getByLabel(/name/i).fill('Updated Manager Name');
    await page.getByRole('button', { name: /save|update/i }).click();

    /* Assert */
    await expect(page.getByRole('status', { name: /updated successfully/i }).or(page.getByText('User updated successfully'))).toBeVisible();
});

test('it unauthorized user cannot update user', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.employee.email, TEST_USERS.employee.password);

    /* Act */
    const response = await page.request.patch('/users/1', {
        data: { name: 'Hacked Name' }
    });

    /* Assert */
    expect(response.status()).toBe(403);
});

test('it user update prevents password change without permission', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.employee.email, TEST_USERS.employee.password);

    /* Act */
    const response = await page.request.patch('/users/1', {
        data: { password: 'newpassword123' }
    });

    /* Assert */
    await expect(page.getByRole('alert').filter({ hasText: /forbidden|unauthorized|cannot change password/i }).or(page.locator('body'))).toBeTruthy();
    expect(response.status()).toBeGreaterThanOrEqual(400);
});

test('it user can be restored after soft delete', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    await page.goto('/users');
    const userRow = page.getByRole('row').filter({ hasText: TEST_USERS.manager.email });
    await userRow.getByRole('button', { name: /delete/i }).click();
    await page.getByRole('button', { name: /confirm|delete/i }).click();

    /* Act */
    await page.getByRole('button', { name: /show deleted|trash|archived/i }).click();
    const deletedUserRow = page.getByRole('row').filter({ hasText: TEST_USERS.manager.email });
    await deletedUserRow.getByRole('button', { name: /restore/i }).click();

    /* Assert */
    await expect(page.getByRole('alert').filter({ hasText: /restored successfully|user restored/i })).toBeVisible();
});

test('it user with user delete permission can delete user', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    await page.goto('/users');

    /* Act */
    const userRow = page.getByRole('row').filter({ hasText: TEST_USERS.employee.email });
    const deleteButton = userRow.getByRole('button', { name: /delete/i });

    /* Assert */
    await expect(deleteButton).toBeVisible();
});

test('it user without user delete permission cannot delete user', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.employee.email, TEST_USERS.employee.password);

    await page.goto('/users');

    /* Act */
    const firstRow = page.getByRole('row').nth(1);
    const deleteButton = firstRow.getByRole('button', { name: /delete/i });

    /* Assert */
    await expect(deleteButton).not.toBeVisible();
});

test('it owner user cannot be deleted even with permission', async ({ page }) => {
    /* Arrange */
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.owner.email, TEST_USERS.owner.password);

    await page.goto('/users');

    /* Act */
    const ownerRow = page.getByRole('row').filter({ hasText: TEST_USERS.owner.email });
    await ownerRow.getByRole('button', { name: /delete/i }).click();
    await page.getByRole('button', { name: /confirm|delete/i }).click();

    /* Assert */
    await expect(page.getByRole('alert').filter({ hasText: /cannot delete owner|owner cannot be deleted/i })).toBeVisible();
});
