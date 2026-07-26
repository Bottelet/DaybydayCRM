const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin, createTask, uniqueValue } = require('../helpers/plain-e2e');

test('clicking a notification in the panel marks it read and navigates without error', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;

  // Creating a task fires a TaskActionNotification, which is what populates
  // the notifications panel we're about to click through. The admin user's
  // notification list is shared across parallel test workers, so this must
  // find the notification matching *this* task by title, not just "the
  // first one" - another worker's notification could easily be newer.
  const title = uniqueValue('PW Notification Task');
  const { payload } = await createTask(page, request, title);

  await page.goto(`${BASE_URL}/dashboard`);
  await page.locator('#grid-action').click();

  const notificationLink = page.locator('.action-panel a[onclick^="postRead"]', { hasText: title });
  await expect(notificationLink).toBeVisible();
  await notificationLink.click();

  // Regression guard: NotificationsController@markRead previously crashed
  // with a 500 on an already-read or invalid notification (->first()->
  // markAsRead() on a possibly-null query result). Clicking a real,
  // unread notification here exercises that same code path end-to-end.
  await expect(page).toHaveURL(new RegExp(payload.task_external_id));
});

test('an already-read notification link does not crash when clicked again', async ({ page }) => {
  await loginAsAdmin(page);
  const request = page.context().request;
  const title = uniqueValue('PW Notification Repeat');
  await createTask(page, request, title);

  await page.goto(`${BASE_URL}/dashboard`);
  await page.locator('#grid-action').click();

  const notificationLink = page.locator('.action-panel a[onclick^="postRead"]', { hasText: title });
  await expect(notificationLink).toBeVisible();
  const href = await notificationLink.getAttribute('href');

  const firstResponse = await request.get(href, { failOnStatusCode: false, maxRedirects: 0 });
  const secondResponse = await request.get(href, { failOnStatusCode: false, maxRedirects: 0 });

  expect(firstResponse.status()).toBeLessThan(400);
  expect(secondResponse.status()).toBeLessThan(400);
});
