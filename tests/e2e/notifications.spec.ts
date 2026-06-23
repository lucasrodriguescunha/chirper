import { test, expect, ALICE, BOB, login, logout } from './helpers';

// Seeder is deterministic: alice=1, bob=2 (see app/Console/Commands/E2eResetCommand.php).
const ALICE_ID = 1;

async function bobFollowsAlice(page) {
  await login(page, BOB);
  await page.goto(`/users/${ALICE_ID}`);
  await expect(page.getByRole('heading', { name: ALICE.name })).toBeVisible();
  const followResp = page.waitForResponse(r => r.url().includes(`/users/${ALICE_ID}/follow`) && r.request().method() === 'POST');
  await page.getByRole('button', { name: /^follow$/i }).click();
  const resp = await followResp;
  // eslint-disable-next-line no-console
  console.log('follow POST status', resp.status(), 'final URL', page.url());
  await expect(page.getByRole('button', { name: /unfollow/i })).toBeVisible();
}

test.describe('Notifications', () => {
  test('bell badge appears after a follow and clears on visit', async ({ page }) => {
    await bobFollowsAlice(page);
    await logout(page);

    await login(page, ALICE);
    const bell = page.locator('nav a[aria-label="Notifications"]');
    await expect(bell.locator('.badge')).toHaveText('1');

    await bell.click();
    await expect(page).toHaveURL(/\/notifications/);
    await expect(page.getByText(new RegExp(`${BOB.name}.*follow`, 'i'))).toBeVisible();

    await page.goto('/');
    await expect(page.locator('nav a[aria-label="Notifications"] .badge')).toHaveCount(0);
  });

  test('clear all removes notifications', async ({ page }) => {
    await bobFollowsAlice(page);
    await logout(page);

    await login(page, ALICE);
    await page.goto('/notifications');
    await expect(page.getByText(new RegExp(`${BOB.name}.*follow`, 'i'))).toBeVisible();

    await page.getByRole('button', { name: /clear all/i }).click();
    await expect(page.getByText(/no notifications yet/i)).toBeVisible();
  });
});
