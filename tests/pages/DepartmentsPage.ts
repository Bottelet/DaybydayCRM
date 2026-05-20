import { expect, type Page } from '@playwright/test';

function escapeRegExp(str: string): string {
  return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

export class DepartmentsPage {
  constructor(private readonly page: Page) {}

  async goto(): Promise<this> {
    await this.page.goto('/departments');
    return this;
  }

  async create(data: { name: string; description: string }): Promise<void> {
    await this.page.getByRole('button', { name: /new department|create department|add department/i }).click();
    await this.page.getByLabel(/name/i).fill(data.name);
    await this.page.getByLabel(/description/i).fill(data.description);
    await this.page.getByRole('button', { name: /save|create/i }).click();
  }

  async delete(name: string): Promise<void> {
    const row = this.page.getByRole('row', { name: new RegExp(escapeRegExp(name), 'i') });
    await row.getByRole('button', { name: /delete|remove/i }).click();
    await this.page.getByRole('button', { name: /confirm|delete/i }).click();
  }

  async assertRowVisible(name: string): Promise<void> {
    await expect(this.page.getByRole('row', { name: new RegExp(escapeRegExp(name), 'i') })).toBeVisible();
  }

  async assertRowNotVisible(name: string): Promise<void> {
    await expect(this.page.getByRole('row', { name: new RegExp(escapeRegExp(name), 'i') })).not.toBeVisible();
  }
}
