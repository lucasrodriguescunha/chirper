import { test, expect, ALICE } from './helpers';
import { execSync } from 'node:child_process';

function createResetToken(email: string): string {
  const cmd = `php artisan tinker --execute="echo Illuminate\\Support\\Facades\\Password::broker()->createToken(App\\Models\\User::where('email','${email}')->first());"`;
  return execSync(cmd, { stdio: ['pipe', 'pipe', 'pipe'] }).toString().trim();
}

test.describe('Password reset', () => {
  test('forgot-password page is reachable from login', async ({ page }) => {
    await page.goto('/login');
    await page.locator('a[href="/forgot-password"]').click();
    await expect(page).toHaveURL(/\/forgot-password/);
    await expect(page.getByRole('button', { name: /send reset link/i })).toBeVisible();
  });

  test('reset-password form renders for valid token', async ({ page }) => {
    const token = createResetToken(ALICE.email);
    await page.goto(`/reset-password/${token}`);
    await expect(page.locator('input[name="token"]')).toHaveValue(token);
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('input[name="password_confirmation"]')).toBeVisible();
    await expect(page.getByRole('button', { name: /^reset password$/i })).toBeVisible();
  });

  test('reset password with valid token logs user in with new password', async ({ page }) => {
    const token = createResetToken(ALICE.email);
    await page.goto(`/reset-password/${token}`);

    await page.locator('input[name="email"]').fill(ALICE.email);
    await page.locator('input[name="password"]').fill('NewP@ssword456');
    await page.locator('input[name="password_confirmation"]').fill('NewP@ssword456');
    await page.getByRole('button', { name: /^reset password$/i }).click();

    await expect(page).toHaveURL(/\/login/, { timeout: 10_000 });

    await page.locator('form[action="/login"] input[name="email"]').fill(ALICE.email);
    await page.locator('form[action="/login"] input[name="password"]').fill('NewP@ssword456');
    await page.locator('form[action="/login"] button[type="submit"]').click();
    await expect(page).not.toHaveURL(/\/login/);
  });
});
