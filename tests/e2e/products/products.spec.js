const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, uniqueValue } = require('../helpers/plain-e2e');

test('guest is redirected from products route', async ({ page }) => {
  await page.goto(`${BASE_URL}/products`);
  await expect(page).toHaveURL(/login/);
});

test('creating a product through the browser shows it on the index with the correct price', async ({ page }) => {
  await loginAsAdmin(page);
  await page.goto(`${BASE_URL}/products`);

  const name = uniqueValue('PW Product');
  await page.locator('#create-product-btn').click();
  await page.locator('input[name="name"]').fill(name);
  await page.locator('input[name="price"]').fill('12.50');
  await page.locator('input[type="submit"][value="Submit"]').click();

  const row = page.locator('tr', { hasText: name });
  await expect(row).toBeVisible();
  await expect(row).toContainText('12.50');
});

// Regression guard: the products table's `archived` column has no DB default
// and ProductsController@update never set it when creating a new product, so
// every product creation crashed with a 500 (SQLSTATE: Field 'archived'
// doesn't have a default value). A blank price field also crashed separately
// with a TypeError ("Unsupported operand types: string * int") since PHP 8
// no longer silently coerces an empty string to 0 in arithmetic.
test('creating a product with an empty price field does not 500', async ({ page }) => {
  await loginAsAdmin(page);
  await page.goto(`${BASE_URL}/products`);

  const name = uniqueValue('PW Product Empty Price');
  await page.locator('#create-product-btn').click();
  await page.locator('input[name="name"]').fill(name);
  await page.locator('input[type="submit"][value="Submit"]').click();

  await expect(page.locator('tr', { hasText: name })).toBeVisible();
});

test('editing a product persists the new price', async ({ page }) => {
  await loginAsAdmin(page);
  await page.goto(`${BASE_URL}/products`);

  const name = uniqueValue('PW Product Edit');
  await page.locator('#create-product-btn').click();
  await page.locator('input[name="name"]').fill(name);
  await page.locator('input[name="price"]').fill('10.00');
  await page.locator('input[type="submit"][value="Submit"]').click();

  const row = page.locator('tr', { hasText: name });
  await expect(row).toBeVisible();
  await row.locator('.edit-product-btn').click();

  await page.locator('input[name="price"]').fill('99.00');
  await page.locator('input[type="submit"][value="Submit"]').click();

  await expect(row).toContainText('99.00');
});

test('deleting a product removes it from the index', async ({ page }) => {
  await loginAsAdmin(page);
  await page.goto(`${BASE_URL}/products`);

  const name = uniqueValue('PW Product Delete');
  await page.locator('#create-product-btn').click();
  await page.locator('input[name="name"]').fill(name);
  await page.locator('input[type="submit"][value="Submit"]').click();

  const row = page.locator('tr', { hasText: name });
  await expect(row).toBeVisible();
  await row.locator('button.btn-warning').click();
  await page.locator('#deletion-form input[type="submit"]').click();

  await expect(page.locator('tr', { hasText: name })).toHaveCount(0);
});
