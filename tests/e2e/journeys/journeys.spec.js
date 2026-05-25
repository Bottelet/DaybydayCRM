const { test, expect } = require('@playwright/test');
const { BASE_URL, loginAsAdmin } = require('../helpers/plain-e2e');

test('the application does not expose a journeys index route today', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;

  /* Act */
  const response = await request.get(`${BASE_URL}/journeys`, {
    failOnStatusCode: false,
    maxRedirects: 0,
  });

  /* Assert */
  expect(response.status()).toBe(404);
});

test('the application does not expose a journeys create route today', async ({ page }) => {
  /* Arrange */
  await loginAsAdmin(page);
  const request = page.context().request;

  /* Act */
  const response = await request.get(`${BASE_URL}/journeys/create`, {
    failOnStatusCode: false,
    maxRedirects: 0,
  });

  /* Assert */
  expect(response.status()).toBe(404);
});

test('owner can complete full journey and cleanup', async ({ page }) => {
    test.skip(test.info().project.name !== 'owner');
    test.slow();

    const clientName = `PW Journey Client ${Date.now()}`;
    const leadTitle = `PW Journey Lead ${Date.now()}`;
    const offerTitle = `PW Journey Offer ${Date.now()}`;

    const clients = new ClientsPage(page);
    let clientCreated = false;

    try {
        await clients.goto();
        await clients.create({ company: clientName, email: `${Date.now()}@example.test` });
        clientCreated = true;
        await clients.assertVisible(clientName);

        const leads = new LeadsPage(page);
        await leads.goto();
        await leads.create({ title: leadTitle, client: clientName });
        await leads.assertVisible(leadTitle);

        const offers = new OffersPage(page);
        await offers.goto();
        await offers.create({ title: offerTitle, lead: leadTitle, item: 'Service Item', quantity: '1', price: '100' });
        await offers.assertVisible(offerTitle);

        await page.goto('/leads');
        await expect(page.getByText(offerTitle)).toBeVisible();

        await page
            .getByRole('row', { name: new RegExp(escapeRegExp(offerTitle), 'i') })
            .getByRole('button', { name: /convert.*invoice|create invoice/i })
            .click();

        const invoices = new InvoicesPage(page);
        await invoices.goto();
        await invoices.assertVisible(offerTitle);
        const invoiceRow = page.getByRole('row', { name: new RegExp(escapeRegExp(offerTitle), 'i') });
        await expect(invoiceRow.getByText(/^100(?:\.00)?$/)).toBeVisible();
    } finally {
        if (clientCreated) {
            await clients.goto();
            await clients.delete(clientName);
        }
    }
});
