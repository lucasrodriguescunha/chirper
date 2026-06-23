import { test, expect, ALICE, BOB, login } from './helpers';
import path from 'node:path';
import fs from 'node:fs';
import os from 'node:os';

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
    const card = page.locator('.card', { hasText: 'Hello world from Bob' }).first();
    const likeBtn = card.locator('button.reaction-button[data-type="like"]');
    const reactionResp = page.waitForResponse(r => r.url().includes('/reaction') && r.request().method() === 'POST');
    await likeBtn.click();
    await reactionResp;
    await expect(likeBtn).toHaveClass(/text-red-600/, { timeout: 10_000 });
  });

  test('upload attachment with chirp renders image in feed', async ({ page }) => {
    await login(page, ALICE);

    // 1x1 PNG
    const pngBytes = Buffer.from(
      'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=',
      'base64',
    );
    const tmpFile = path.join(os.tmpdir(), `chirp-e2e-${Date.now()}.png`);
    fs.writeFileSync(tmpFile, pngBytes);

    const message = `Attach ${Date.now()}`;
    await page.locator('textarea[name="message"]').fill(message);
    await page.locator('#attachment-input').setInputFiles(tmpFile);
    await page.locator('form[action="/chirps"] button[type="submit"]').click();

    const card = page.locator('.card', { hasText: message }).first();
    await expect(card).toBeVisible();
    await expect(card.locator('img').first()).toBeVisible();

    fs.unlinkSync(tmpFile);
  });

  test('dislike after like flips reaction state', async ({ page }) => {
    await login(page, ALICE);
    const card = page.locator('.card', { hasText: 'Hello world from Bob' }).first();
    const likeBtn = card.locator('button.reaction-button[data-type="like"]');
    const dislikeBtn = card.locator('button.reaction-button[data-type="dislike"]');

    const likeResp = page.waitForResponse(r => r.url().includes('/reaction') && r.request().method() === 'POST');
    await likeBtn.click();
    await likeResp;
    await expect(likeBtn).toHaveClass(/text-red-600/, { timeout: 10_000 });

    const dislikeResp = page.waitForResponse(r => r.url().includes('/reaction') && r.request().method() === 'POST');
    await dislikeBtn.click();
    await dislikeResp;
    await expect(dislikeBtn).toHaveClass(/text-red-600/, { timeout: 10_000 });
    await expect(likeBtn).not.toHaveClass(/text-red-600/);
  });

  test('non-owner cannot reach edit page (403)', async ({ page }) => {
    await login(page, ALICE);
    const card = page.locator('.card', { hasText: 'Hello world from Bob' }).first();
    await expect(card).toBeVisible();
    await expect(card.getByRole('link', { name: /edit/i })).toHaveCount(0);

    const response = await page.goto('/chirps/1/edit');
    expect(response?.status()).toBe(403);
  });

  test('rejects non-image attachment', async ({ page }) => {
    await login(page, ALICE);
    const tmpFile = path.join(os.tmpdir(), `chirp-e2e-${Date.now()}.pdf`);
    fs.writeFileSync(tmpFile, Buffer.from('%PDF-1.4 fake'));

    await page.locator('textarea[name="message"]').fill(`Bad file ${Date.now()}`);
    await page.locator('#attachment-input').setInputFiles(tmpFile);
    await page.locator('form[action="/chirps"] button[type="submit"]').click();

    await expect(page.getByText(/attachment must be an image|attachment.*image/i)).toBeVisible();

    fs.unlinkSync(tmpFile);
  });
});

test.describe('Chirp authorization', () => {
  test('guest is redirected to login when posting chirp form', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveURL(/login/);
  });

  test('chirp edit page requires auth', async ({ page }) => {
    await page.goto('/chirps/1/edit');
    await expect(page).toHaveURL(/login/);
  });
});
