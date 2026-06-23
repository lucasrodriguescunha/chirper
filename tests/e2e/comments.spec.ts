import { test, expect, ALICE, BOB, login, logout } from './helpers';

test.describe('Comments', () => {
  test('post comment on chirp', async ({ page }) => {
    await login(page, ALICE);
    const card = page.locator('.card', { hasText: 'Hello world from Bob' }).first();
    const body = `Nice chirp ${Date.now()}`;
    await card.locator('input[name="body"]').fill(body);
    await card.locator('form button[type="submit"]').filter({ hasText: /send/i }).click();
    await expect(page.locator('[data-comment-body]').filter({ hasText: body })).toBeVisible();
  });

  test('edit own comment', async ({ page }) => {
    await login(page, ALICE);
    const card = page.locator('.card', { hasText: 'Hello world from Bob' }).first();
    const body = `Editable comment ${Date.now()}`;
    await card.locator('input[name="body"]').fill(body);
    await card.locator('form button[type="submit"]').filter({ hasText: /send/i }).click();
    const row = page.locator('[data-comment]').filter({ hasText: body }).first();
    await expect(row).toBeVisible();

    await row.locator('[data-comment-edit-toggle]').click();
    const updated = `${body} EDITED`;
    await row.locator('textarea[name="body"]').fill(updated);
    await row.locator('button[type="submit"]').filter({ hasText: /save/i }).click();
    await expect(page.locator('[data-comment-body]').filter({ hasText: updated })).toBeVisible();
  });

  test('delete own comment', async ({ page }) => {
    await login(page, ALICE);
    const card = page.locator('.card', { hasText: 'Hello world from Bob' }).first();
    const body = `Disposable ${Date.now()}`;
    await card.locator('input[name="body"]').fill(body);
    await card.locator('form button[type="submit"]').filter({ hasText: /send/i }).click();
    const row = page.locator('[data-comment]').filter({ hasText: body }).first();
    await expect(row).toBeVisible();

    page.on('dialog', d => d.accept());
    await row.locator('button[type="submit"]').filter({ hasText: '✕' }).click();
    await expect(page.locator('[data-comment-body]').filter({ hasText: body })).toHaveCount(0);
  });

  test('non-owner sees no edit/delete controls on another user comment', async ({ page }) => {
    // Bob posts comment on his own chirp
    await login(page, BOB);
    const card = page.locator('.card', { hasText: 'Hello world from Bob' }).first();
    const body = `Bob says hi ${Date.now()}`;
    await card.locator('input[name="body"]').fill(body);
    await card.locator('form button[type="submit"]').filter({ hasText: /send/i }).click();
    await expect(page.locator('[data-comment-body]').filter({ hasText: body })).toBeVisible();
    await logout(page);

    // Alice cannot edit/delete Bob's comment
    await login(page, ALICE);
    const row = page.locator('[data-comment]').filter({ hasText: body }).first();
    await expect(row).toBeVisible();
    await expect(row.locator('[data-comment-edit-toggle]')).toHaveCount(0);
    await expect(row.locator('button[type="submit"]').filter({ hasText: '✕' })).toHaveCount(0);
  });

  test('like comment toggles state', async ({ page }) => {
    await login(page, ALICE);
    const card = page.locator('.card', { hasText: 'Hello world from Bob' }).first();
    const body = `Like-me ${Date.now()}`;
    await card.locator('input[name="body"]').fill(body);
    await card.locator('form button[type="submit"]').filter({ hasText: /send/i }).click();
    const row = page.locator('[data-comment]').filter({ hasText: body }).first();
    await expect(row).toBeVisible();

    const likeBtn = row.locator('button.comment-reaction-button[data-type="like"]');
    const reactionResp = page.waitForResponse(r => /\/comments\/\d+\/reaction/.test(r.url()) && r.request().method() === 'POST');
    await likeBtn.click();
    await reactionResp;
    await expect(likeBtn).toHaveClass(/text-red-600/, { timeout: 10_000 });
  });
});
