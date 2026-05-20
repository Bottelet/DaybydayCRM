import { expect, test } from '@playwright/test';
import { ClientsPage } from '../pages/ClientsPage';
import { InvoicesPage } from '../pages/InvoicesPage';
import { LeadsPage } from '../pages/LeadsPage';
import { OffersPage } from '../pages/OffersPage';

function escapeRegExp(str: string): string {
  return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

test.describe('Lead to offer to invoice journey', () => {
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
});
