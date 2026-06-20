import { test, expect, ALICE, login } from './helpers';

test.describe('Search', () => {
  test('search finds users and chirps via navbar', async ({ page }) => {
    await login(page, ALICE);
    await page.locator('nav input[name="q"]').fill('Bob');
    await page.locator('nav form[action*="search"]').evaluate(f => (f as HTMLFormElement).submit());
    await expect(page).toHaveURL(/\/search\?q=Bob/);
    await expect(page.getByRole('heading', { name: /users/i })).toBeVisible();
    await expect(page.getByText(/Bob E2E/).first()).toBeVisible();
  });

  test('search finds matching chirp content', async ({ page }) => {
    await login(page, ALICE);
    await page.goto('/search?q=Hello+world');
    await expect(page.getByText('Hello world from Bob — testing chirps.')).toBeVisible();
  });

  test('empty query shows prompt', async ({ page }) => {
    await login(page, ALICE);
    await page.goto('/search');
    await expect(page.getByText(/type a query/i)).toBeVisible();
  });

  test('no match renders empty state', async ({ page }) => {
    await login(page, ALICE);
    await page.goto('/search?q=zzzzz_nonexistent');
    await expect(page.getByText(/no users matched/i)).toBeVisible();
    await expect(page.getByText(/no chirps matched/i)).toBeVisible();
  });
});
