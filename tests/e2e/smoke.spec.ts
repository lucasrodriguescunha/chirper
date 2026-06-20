import { test, expect } from './helpers';

test('home loads', async ({ page }) => {
  const response = await page.goto('/', { waitUntil: 'domcontentloaded' });
  expect(response?.ok()).toBeTruthy();
});

test('login page renders form', async ({ page }) => {
  await page.goto('/login');
  await expect(page.locator('form[action="/login"] input[name="email"]')).toBeVisible();
  await expect(page.locator('form[action="/login"] input[name="password"]')).toBeVisible();
});

test('register page renders form', async ({ page }) => {
  await page.goto('/register');
  await expect(page.locator('form[action="/register"] input[name="name"]')).toBeVisible();
  await expect(page.locator('form[action="/register"] input[name="email"]')).toBeVisible();
});
