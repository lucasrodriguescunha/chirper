import { test, expect } from '@playwright/test';

test('home loads', async ({ page }) => {
  const response = await page.goto('/', { waitUntil: 'domcontentloaded' });
  expect(response?.ok()).toBeTruthy();
});

test('login page renders form', async ({ page }) => {
  await page.goto('/login');
  await expect(page.getByLabel(/email/i)).toBeVisible();
  await expect(page.getByLabel(/password/i).first()).toBeVisible();
});

test('register page renders form', async ({ page }) => {
  await page.goto('/register');
  await expect(page.getByLabel(/name/i)).toBeVisible();
  await expect(page.getByLabel(/email/i)).toBeVisible();
});
