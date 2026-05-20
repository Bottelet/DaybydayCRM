import { expect, type Page } from '@playwright/test';

export class TasksPage {
  constructor(private readonly page: Page) {}

  async goto(): Promise<this> {
    await this.page.goto('/tasks');
    return this;
  }

  async create(data: Record<string, string>): Promise<void> {
    await this.page.getByRole('button', { name: /new task|create task|add task/i }).click();

    for (const [label, value] of Object.entries(data)) {
      await this.page.getByLabel(new RegExp(label, 'i')).fill(value);
    }

    await this.page.getByRole('button', { name: /save|create/i }).click();
  }

  async edit(identifier: string, data: Record<string, string>): Promise<void> {
    const row = this.page.getByRole('row', { name: new RegExp(identifier, 'i') });
    await row.getByRole('link', { name: /edit/i }).click();

    for (const [label, value] of Object.entries(data)) {
      await this.page.getByLabel(new RegExp(label, 'i')).fill(value);
    }

    await this.page.getByRole('button', { name: /save|update/i }).click();
  }

  async close(identifier: string): Promise<void> {
    const row = this.page.getByRole('row', { name: new RegExp(identifier, 'i') });
    await row.getByRole('button', { name: /close|mark closed|status/i }).click();
  }

  async delete(identifier: string): Promise<void> {
    const row = this.page.getByRole('row', { name: new RegExp(identifier, 'i') });
    await row.getByRole('button', { name: /delete|remove/i }).click();
    await this.page.getByRole('button', { name: /confirm|delete/i }).click();
  }

  async assertVisible(identifier: string): Promise<void> {
    await expect(this.page.getByText(identifier)).toBeVisible();
  }

  async assertTaskClosed(identifier: string): Promise<void> {
    const row = this.page.getByRole('row', { name: new RegExp(identifier, 'i') });
    await expect(row.getByText(/closed|complete/i)).toBeVisible();
  }

  async assertNotVisible(identifier: string): Promise<void> {
    await expect(this.page.getByText(identifier)).not.toBeVisible();
  }
}
