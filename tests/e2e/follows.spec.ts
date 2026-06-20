import { test, expect, ALICE, BOB, login } from './helpers';

test.describe('Follows', () => {
  test('follow then unfollow another user', async ({ page }) => {
    await login(page, ALICE);
    const bobChirp = page.locator('.card', { hasText: 'Hello world from Bob' }).first();
    await bobChirp.getByRole('link', { name: BOB.name }).click();
    await expect(page).toHaveURL(/\/users\/\d+/);

    await page.getByRole('button', { name: /^follow$/i }).click();
    await expect(page.getByRole('button', { name: /unfollow/i })).toBeVisible();

    await page.getByRole('button', { name: /unfollow/i }).click();
    await expect(page.getByRole('button', { name: /^follow$/i })).toBeVisible();
  });
});
