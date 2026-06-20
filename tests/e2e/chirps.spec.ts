import { test, expect, ALICE, login } from './helpers';

test.describe('Chirps CRUD', () => {
  test('create chirp appears in feed', async ({ page }) => {
    await login(page, ALICE);
    const message = `Test chirp ${Date.now()}`;
    await page.locator('textarea[name="message"]').fill(message);
    await page.locator('form[action="/chirps"] button[type="submit"]').click();
    await expect(page.getByText(message)).toBeVisible();
  });

  test('edit own chirp', async ({ page }) => {
    await login(page, ALICE);
    const original = `Edit-me ${Date.now()}`;
    await page.locator('textarea[name="message"]').fill(original);
    await page.locator('form[action="/chirps"] button[type="submit"]').click();
    await expect(page.getByText(original)).toBeVisible();

    const card = page.locator('.card', { hasText: original }).first();
    await card.getByRole('link', { name: /edit/i }).click();
    const updated = `${original} EDITED`;
    await page.locator('textarea[name="message"]').fill(updated);
    await page.locator('button[type="submit"]').filter({ hasText: /save|update/i }).first().click();
    await expect(page.getByText(updated)).toBeVisible();
  });

  test('delete own chirp', async ({ page }) => {
    await login(page, ALICE);
    const message = `Delete-me ${Date.now()}`;
    await page.locator('textarea[name="message"]').fill(message);
    await page.locator('form[action="/chirps"] button[type="submit"]').click();
    await expect(page.getByText(message)).toBeVisible();

    page.on('dialog', d => d.accept());
    const card = page.locator('.card', { hasText: message }).first();
    await card.getByRole('button', { name: /delete/i }).click();
    await expect(page.getByText(message)).toHaveCount(0);
  });

  test('like chirp toggles count', async ({ page }) => {
    await login(page, ALICE);
    await page.waitForLoadState('networkidle');
    const card = page.locator('.card', { hasText: 'Hello world from Bob' }).first();
    const likeBtn = card.locator('button.reaction-button[data-type="like"]');
    const reactionResp = page.waitForResponse(r => r.url().includes('/reaction') && r.request().method() === 'POST');
    await likeBtn.click();
    await reactionResp;
    await expect(likeBtn).toHaveClass(/text-red-600/, { timeout: 10_000 });
  });
});
