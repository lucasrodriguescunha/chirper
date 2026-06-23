import { test, expect, ALICE, BOB, login } from './helpers';

test.describe('Bookmarks', () => {
  test('saving a chirp adds it to /bookmarks and increments navbar badge', async ({ page }) => {
    await login(page, ALICE);

    const card = page.locator('.card', { hasText: 'Hello world from Bob' }).first();
    await card.getByRole('button', { name: /save chirp/i }).click();

    const navBookmark = page.locator('nav a[aria-label="Bookmarks"]').first();
    await expect(navBookmark.locator('.badge')).toHaveText('1');

    await navBookmark.click();
    await expect(page).toHaveURL(/\/bookmarks/);
    await expect(page.getByText('Hello world from Bob')).toBeVisible();
  });

  test('removing a bookmark clears the badge but keeps the chirp in the feed', async ({ page }) => {
    await login(page, ALICE);

    const card = page.locator('.card', { hasText: 'Hello world from Bob' }).first();
    await card.getByRole('button', { name: /save chirp/i }).click();
    await expect(page.locator('nav a[aria-label="Bookmarks"]').first().locator('.badge')).toHaveText('1');

    const savedCard = page.locator('.card', { hasText: 'Hello world from Bob' }).first();
    await savedCard.getByRole('button', { name: /remove bookmark/i }).click();

    await expect(page.locator('nav a[aria-label="Bookmarks"]').first().locator('.badge')).toHaveCount(0);
    await expect(page.getByText('Hello world from Bob')).toBeVisible();

    await page.goto('/bookmarks');
    await expect(page.getByText(/no bookmarks yet/i)).toBeVisible();
  });

  test('owner can bookmark their own chirp', async ({ page }) => {
    await login(page, ALICE);

    const card = page.locator('.card', { hasText: 'Alice first chirp' }).first();
    await card.getByRole('button', { name: /save chirp/i }).click();
    await expect(page.locator('nav a[aria-label="Bookmarks"]').first().locator('.badge')).toHaveText('1');

    await page.goto('/bookmarks');
    await expect(page.getByText('Alice first chirp')).toBeVisible();
  });

  test('empty bookmark index shows placeholder', async ({ page }) => {
    await login(page, BOB);
    await page.goto('/bookmarks');
    await expect(page.getByText(/no bookmarks yet/i)).toBeVisible();
  });

  test('bookmark routes require auth', async ({ page }) => {
    await page.goto('/bookmarks');
    await expect(page).toHaveURL(/login/);
  });
});
