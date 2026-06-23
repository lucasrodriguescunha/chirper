import { Page, expect, test as base } from '@playwright/test';
import { execSync } from 'node:child_process';

export const ALICE = { email: 'alice@e2e.test', password: 'password123', name: 'Alice E2E' };
export const BOB = { email: 'bob@e2e.test', password: 'password123', name: 'Bob E2E' };

export function resetDb() {
  execSync('php artisan e2e:reset', { stdio: 'pipe' });
}

export async function login(page: Page, creds: { email: string; password: string }) {
  await page.goto('/login');
  await page.locator('form[action="/login"] input[name="email"]').fill(creds.email);
  await page.locator('form[action="/login"] input[name="password"]').fill(creds.password);
  await page.locator('form[action="/login"] button[type="submit"]').click();
  await expect(page).not.toHaveURL(/\/login/);
}

export async function logout(page: Page) {
  await page.locator('nav [aria-label="Account menu"]').click();
  await page.locator('form[action="/logout"] button[type="submit"]').first().click({ force: true });
  await expect(page.getByRole('link', { name: /sign in/i })).toBeVisible();
}

export const test = base.extend({
  page: async ({ page }, use) => {
    resetDb();
    await use(page);
  },
});

export { expect };
