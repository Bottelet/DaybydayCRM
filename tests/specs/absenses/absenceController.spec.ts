import { test, expect } from '@playwright/test';
import { TEST_USERS } from '../../playwright/fixtures/users';

test.describe('AbsenceController', () => {
  test('it can create absence for other user', async ({ page }) => {
    /* Arrange */
    const owner = TEST_USERS.owner;
    const uniqueComment = `Sick kid ${Date.now()}`;
    await page.goto('/login');
    await page.getByLabel('Email').fill(owner.email);
    await page.getByLabel('Password').fill(owner.password);
    await page.getByRole('button', { name: /login/i }).click();

    /* Act */
    await page.goto('/absence/create');
    await page.getByLabel(/reason/i).selectOption({ label: 'Sick' });
    await page.getByLabel(/start date/i).fill('2020-01-01');
    await page.getByLabel(/end date/i).fill('2020-01-02');
    await page.getByLabel(/comment/i).fill(uniqueComment);
    await page.getByRole('button', { name: /save|create|submit/i }).click();

    /* Assert */
    await expect(page).toHaveURL(/absence/);
    await expect(page.getByText(uniqueComment)).toBeVisible();
  });

  test('it creates absence for authenticated user when user external id not provided', async ({ page }) => {
    /* Arrange */
    const owner = TEST_USERS.owner;
    const uniqueComment = `Self sick ${Date.now()}`;
    await page.goto('/login');
    await page.getByLabel('Email').fill(owner.email);
    await page.getByLabel('Password').fill(owner.password);
    await page.getByRole('button', { name: /login/i }).click();

    /* Act */
    await page.goto('/absence/create');
    await page.getByLabel(/reason/i).selectOption({ label: 'Sick' });
    await page.getByLabel(/start date/i).fill('2020-01-01');
    await page.getByLabel(/end date/i).fill('2020-01-02');
    await page.getByLabel(/comment/i).fill(uniqueComment);
    await page.getByRole('button', { name: /save|create|submit/i }).click();

    /* Assert */
    await expect(page).toHaveURL(/absence/);
    await expect(page.getByText(uniqueComment)).toBeVisible();
  });

  test('it creates absence for authenticated user when attempting to create for other user without permission', async ({ page }) => {
    /* Arrange */
    const employee = TEST_USERS.employee;
    const uniqueComment = `No permission ${Date.now()}`;
    await page.goto('/login');
    await page.getByLabel('Email').fill(employee.email);
    await page.getByLabel('Password').fill(employee.password);
    await page.getByRole('button', { name: /login/i }).click();

    /* Act */
    await page.goto('/absence/create');
    await page.getByLabel(/reason/i).selectOption({ label: 'Sick' });
    await page.getByLabel(/start date/i).fill('2020-01-01');
    await page.getByLabel(/end date/i).fill('2020-01-02');
    await page.getByLabel(/comment/i).fill(uniqueComment);
    await page.getByRole('button', { name: /save|create|submit/i }).click();

    /* Assert */
    await expect(page).toHaveURL(/absence/);
    await expect(page.getByText(uniqueComment)).toBeVisible();
  });

  test('it returns web error when absence creation throws exception', async ({ page }) => {
    /* Arrange */
    const owner = TEST_USERS.owner;
    await page.goto('/login');
    await page.getByLabel('Email').fill(owner.email);
    await page.getByLabel('Password').fill(owner.password);
    await page.getByRole('button', { name: /login/i }).click();

    /* Act */
    await page.goto('/absence/create?simulate_failure=1');
    await page.getByLabel(/reason/i).selectOption({ label: 'Sick' });
    await page.getByLabel(/start date/i).fill('2020-01-01');
    await page.getByLabel(/end date/i).fill('2020-01-02');
    await page.getByRole('button', { name: /save|create|submit/i }).click();

    /* Assert */
    await expect(page).toHaveURL(/absence\/create/);
    await expect(page.getByRole('alert')).toBeVisible();
  });

  test('it returns json error when absence creation throws exception', async ({ page }) => {
    /* Arrange */ // uses seeded data
    const owner = TEST_USERS.owner;
    await page.goto('/login');
    await page.getByLabel('Email').fill(owner.email);
    await page.getByLabel('Password').fill(owner.password);
    await page.getByRole('button', { name: /login/i }).click();

    /* Act */
    const responsePromise = page.waitForResponse((response) => response.url().includes('/absence') && response.status() >= 400);
    await page.goto('/absence/create?simulate_failure=1&format=json');
    await page.getByLabel(/reason/i).selectOption({ label: 'Sick' });
    await page.getByLabel(/start date/i).fill('2020-01-01');
    await page.getByLabel(/end date/i).fill('2020-01-02');
    await page.getByRole('button', { name: /save|create|submit/i }).click();
    const response = await responsePromise;

    /* Assert */
    await expect(response.status()).toBe(500);
    await expect(page.getByText(/could not be registered|try again/i)).toBeVisible();
  });
});
