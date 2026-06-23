import { test, expect, ALICE, login, logout } from './helpers';

test.describe('Authentication', () => {
  test('register new user', async ({ page }) => {
    const unique = Date.now();
    await page.goto('/register');
    const form = page.locator('form[action="/register"]');
    await form.locator('input[name="name"]').fill(`Carol ${unique}`);
    await form.locator('input[name="email"]').fill(`carol+${unique}@e2e.test`);
    await form.locator('input[name="password"]').fill('StrongP@ss123');
    await form.locator('input[name="password_confirmation"]').fill('StrongP@ss123');
    await form.locator('button[type="submit"]').click();
    await expect(page).not.toHaveURL(/register/);
  });

  test('login with valid credentials', async ({ page }) => {
    await login(page, ALICE);
    await expect(page.locator('nav').getByText(ALICE.name)).toBeVisible();
  });

  test('login with invalid credentials shows error', async ({ page }) => {
    await page.goto('/login');
    const form = page.locator('form[action="/login"]');
    await form.locator('input[name="email"]').fill('nobody@e2e.test');
    await form.locator('input[name="password"]').fill('wrongpw');
    await form.locator('button[type="submit"]').click();
    await expect(page).toHaveURL(/login/);
  });

  test('logout returns to guest navbar', async ({ page }) => {
    await login(page, ALICE);
    await logout(page);
    await expect(page.getByRole('link', { name: /sign in/i })).toBeVisible();
  });

  test('forgot password page renders', async ({ page }) => {
    await page.goto('/forgot-password');
    await expect(page.locator('input[name="email"]')).toBeVisible();
  });

  test('register rejects invalid email and short password', async ({ page }) => {
    await page.goto('/register');
    const form = page.locator('form[action="/register"]');
    await form.locator('input[name="name"]').fill('Bad Inputs');
    await form.locator('input[name="email"]').fill('not-an-email');
    await form.locator('input[name="password"]').fill('123');
    await form.locator('input[name="password_confirmation"]').fill('123');
    await form.locator('button[type="submit"]').click();
    await expect(page).toHaveURL(/register/);
    await expect(page.getByText(/email/i).first()).toBeVisible();
  });

  test('register requires matching password confirmation', async ({ page }) => {
    const unique = Date.now();
    await page.goto('/register');
    const form = page.locator('form[action="/register"]');
    await form.locator('input[name="name"]').fill('Mismatch User');
    await form.locator('input[name="email"]').fill(`mismatch+${unique}@e2e.test`);
    await form.locator('input[name="password"]').fill('StrongP@ss123');
    await form.locator('input[name="password_confirmation"]').fill('DifferentP@ss123');
    await form.locator('button[type="submit"]').click();
    await expect(page).toHaveURL(/register/);
  });
});
