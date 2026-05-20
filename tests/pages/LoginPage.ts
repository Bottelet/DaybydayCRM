import { expect, type Page } from '@playwright/test';

export class LoginPage {
  constructor(private readonly page: Page) {}

  async goto(): Promise<this> {
    await this.page.goto('/login');
    return this;
  }

  async login(email: string, password: string): Promise<void> {
    await this.page.getByLabel(/email/i).fill(email);
    await this.page.getByLabel(/password/i).fill(password);
    await this.page.getByRole('button', { name: /log ?in|sign ?in/i }).click();
  }

  async assertLoginErrorVisible(): Promise<void> {
    await expect(this.page.getByText(/invalid|failed|incorrect|credentials/i)).toBeVisible();
  }
}
