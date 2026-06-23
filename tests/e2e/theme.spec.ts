import { test, expect } from './helpers';

test.describe('Theme toggle', () => {
  test('toggles between light and dark and persists in localStorage', async ({ page }) => {
    await page.goto('/login');
    await page.evaluate(() => localStorage.removeItem('theme'));
    await page.reload();

    const html = page.locator('html');
    const initial = await html.getAttribute('data-theme');
    expect(initial).toMatch(/^laravelChirper(Dark)?$/);

    await page.locator('#theme-toggle').click();
    const after = initial === 'laravelChirperDark' ? 'laravelChirper' : 'laravelChirperDark';
    await expect(html).toHaveAttribute('data-theme', after);

    const stored = await page.evaluate(() => localStorage.getItem('theme'));
    expect(stored).toBe(after);

    // Reload — inline head script must apply persisted theme before paint.
    await page.reload();
    await expect(page.locator('html')).toHaveAttribute('data-theme', after);
  });
});
