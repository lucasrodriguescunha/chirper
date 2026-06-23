import { execSync } from 'node:child_process';
import { test, expect, ALICE, login } from './helpers';

const KNOWN_SECRET = 'JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP';
const RECOVERY_CODE = 'aaaaa-bbbbb';

function enableTwoFactorFor(email: string, secret = KNOWN_SECRET, recovery = RECOVERY_CODE) {
  const php = `\$u = App\\Models\\User::where('email', '${email}')->firstOrFail(); ` +
    `\$u->forceFill([` +
    `'two_factor_secret' => '${secret}',` +
    `'two_factor_recovery_codes' => json_encode(['${recovery}', 'ccccc-ddddd']),` +
    `'two_factor_confirmed_at' => now(),` +
    `])->save();`;
  execSync(`php artisan tinker --execute="${php}"`, { stdio: 'pipe' });
}

test.describe('Two-factor settings', () => {
  test('settings page requires auth', async ({ page }) => {
    await page.goto('/settings/two-factor');
    await expect(page).toHaveURL(/login/);
  });

  test('disabled user can start enrollment and sees pending QR + secret', async ({ page }) => {
    await login(page, ALICE);
    await page.goto('/settings/two-factor');
    await expect(page.getByText(/Disabled/i)).toBeVisible();

    await page.getByRole('button', { name: /Enable 2FA/i }).click();
    await expect(page).toHaveURL(/\/settings\/two-factor/);
    await expect(page.getByText(/Pending confirmation/i)).toBeVisible();
    await expect(page.locator('svg').first()).toBeVisible();
    await expect(page.locator('input[name="code"]')).toBeVisible();
  });

  test('invalid TOTP code during enrollment shows an error', async ({ page }) => {
    await login(page, ALICE);
    await page.goto('/settings/two-factor');
    await page.getByRole('button', { name: /Enable 2FA/i }).click();

    const confirmForm = page.locator('form').filter({ has: page.locator('input[name="code"]') });
    await confirmForm.locator('input[name="code"]').fill('000000');
    await confirmForm.locator('button[type="submit"]').click();

    await expect(page.getByText(/Invalid code/i)).toBeVisible();
  });

  test('disable form requires the current password', async ({ page }) => {
    await login(page, ALICE);
    enableTwoFactorFor(ALICE.email);
    await page.goto('/settings/two-factor');

    await expect(page.locator('.badge-success', { hasText: 'Enabled' })).toBeVisible();

    const disableForm = page.locator('form').filter({ has: page.getByRole('button', { name: /^Disable$/i }) });
    await disableForm.locator('input[name="password"]').fill('wrong-password');
    await disableForm.locator('button[type="submit"]').click();
    await expect(page.getByText(/password is incorrect|current password/i)).toBeVisible();
  });
});

test.describe('Two-factor challenge', () => {
  test('login on a 2FA-enabled account redirects to the challenge page', async ({ page }) => {
    enableTwoFactorFor(ALICE.email);

    await page.goto('/login');
    await page.locator('form[action="/login"] input[name="email"]').fill(ALICE.email);
    await page.locator('form[action="/login"] input[name="password"]').fill(ALICE.password);
    await page.locator('form[action="/login"] button[type="submit"]').click();

    await expect(page).toHaveURL(/\/two-factor-challenge/);
    await expect(page.getByText(/Two-Factor Authentication/i)).toBeVisible();
  });

  test('recovery code authenticates and consumes the code', async ({ page }) => {
    enableTwoFactorFor(ALICE.email);

    await page.goto('/login');
    await page.locator('form[action="/login"] input[name="email"]').fill(ALICE.email);
    await page.locator('form[action="/login"] input[name="password"]').fill(ALICE.password);
    await page.locator('form[action="/login"] button[type="submit"]').click();
    await expect(page).toHaveURL(/\/two-factor-challenge/);

    await page.locator('input[name="recovery_code"]').fill(RECOVERY_CODE);
    await page.locator('form[action="/two-factor-challenge"] button[type="submit"]').click();

    await expect(page).not.toHaveURL(/\/two-factor-challenge/);
    await expect(page).not.toHaveURL(/\/login/);
    await expect(page.locator('nav [aria-label="Account menu"]')).toBeVisible();
  });

  test('invalid code keeps the user on the challenge page', async ({ page }) => {
    enableTwoFactorFor(ALICE.email);

    await page.goto('/login');
    await page.locator('form[action="/login"] input[name="email"]').fill(ALICE.email);
    await page.locator('form[action="/login"] input[name="password"]').fill(ALICE.password);
    await page.locator('form[action="/login"] button[type="submit"]').click();
    await expect(page).toHaveURL(/\/two-factor-challenge/);

    await page.locator('input[name="code"]').fill('000000');
    await page.locator('form[action="/two-factor-challenge"] button[type="submit"]').click();

    await expect(page).toHaveURL(/\/two-factor-challenge/);
    await expect(page.getByText(/Invalid code/i)).toBeVisible();
  });

  test('challenge page redirects to login without pending session', async ({ page }) => {
    await page.goto('/two-factor-challenge');
    await expect(page).toHaveURL(/\/login/);
  });
});
