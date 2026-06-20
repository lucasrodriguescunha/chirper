import { test, expect, ALICE, login } from './helpers';

test.describe('Profile settings', () => {
  test('edit name and bio', async ({ page }) => {
    await login(page, ALICE);
    await page.getByRole('link', { name: /^profile$/i }).click();
    await expect(page).toHaveURL(/settings\/profile/);

    const newBio = `Updated bio ${Date.now()}`;
    await page.locator('textarea[name="bio"]').fill(newBio);
    await page.getByRole('button', { name: /save changes/i }).click();

    await page.goto('/settings/profile');
    await expect(page.locator('textarea[name="bio"]')).toHaveValue(newBio);
  });

  test('users.show page lists chirp count', async ({ page }) => {
    await login(page, ALICE);
    const bobChirp = page.locator('.card', { hasText: 'Hello world from Bob' }).first();
    await bobChirp.getByRole('link', { name: 'Bob E2E' }).click();
    await expect(page.getByText(/Chirps/i).first()).toBeVisible();
  });
});
