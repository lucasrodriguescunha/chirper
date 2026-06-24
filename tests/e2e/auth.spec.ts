import { test, expect, ALICE, login, logout } from './helpers';

test.describe('Authentication', () => {
  test('register new user', async ({ page }) => {
    const unique = Date.now();
    await page.goto('/register');
    const form = page.locator('form[action="/register"]');
    await form.locator('input[name="name"]').fill(`Carol ${unique}`);
    await form.locator('input[name="username"]').fill(`carol${unique}`);
    await form.locator('input[name="email"]').fill(`carol+${unique}@e2e.test`);
    await form.locator('input[name="password"]').fill('StrongP@ss123');
    await form.locator('input[name="password_confirmation"]').fill('StrongP@ss123');
    await form.locator('button[type="submit"]').click();
    await expect(page).not.toHaveURL(/register/);
  });

  test('login with valid credentials', async ({ page }) => {
    await login(page, ALICE);
    await expect(page.locator('nav').getByText(ALICE.name)).toBeVisible();
  });

  test('login with invalid credentials shows error', async ({ page }) => {
    await page.goto('/login');
    const form = page.locator('form[action="/login"]');
    await form.locator('input[name="email"]').fill('nobody@e2e.test');
    await form.locator('input[name="password"]').fill('wrongpw');
    await form.locator('button[type="submit"]').click();
    await expect(page).toHaveURL(/login/);
  });

  test('logout returns to guest navbar', async ({ page }) => {
    await login(page, ALICE);
    await logout(page);
    await expect(page.getByRole('link', { name: /sign in/i })).toBeVisible();
  });

  test('forgot password page renders', async ({ page }) => {
    await page.goto('/forgot-password');
    await expect(page.locator('input[name="email"]')).toBeVisible();
  });

  test('register rejects invalid email and short password', async ({ page }) => {
    const unique = Date.now();
    await page.goto('/register');
    const form = page.locator('form[action="/register"]');
    await form.evaluate((f: HTMLFormElement) => (f.noValidate = true));
    await form.locator('input[name="name"]').fill('Bad Inputs');
    await form.locator('input[name="username"]').fill(`badinputs${unique}`);
    await form.locator('input[name="email"]').fill('not-an-email');
    await form.locator('input[name="password"]').fill('123');
    await form.locator('input[name="password_confirmation"]').fill('123');
    await form.locator('button[type="submit"]').click();
    await expect(page).toHaveURL(/register/);
    await expect(page.getByText(/email/i).first()).toBeVisible();
  });

  test('register requires matching password confirmation', async ({ page }) => {
    const unique = Date.now();
    await page.goto('/register');
    const form = page.locator('form[action="/register"]');
    await form.locator('input[name="name"]').fill('Mismatch User');
    await form.locator('input[name="username"]').fill(`mismatch${unique}`);
    await form.locator('input[name="email"]').fill(`mismatch+${unique}@e2e.test`);
    await form.locator('input[name="password"]').fill('StrongP@ss123');
    await form.locator('input[name="password_confirmation"]').fill('DifferentP@ss123');
    await form.locator('button[type="submit"]').click();
    await expect(page).toHaveURL(/register/);
  });

  test('register rejects duplicate username', async ({ page }) => {
    const unique = Date.now();
    await page.goto('/register');
    const form = page.locator('form[action="/register"]');
    await form.locator('input[name="name"]').fill('Dup User');
    await form.locator('input[name="username"]').fill('alice');
    await form.locator('input[name="email"]').fill(`dup+${unique}@e2e.test`);
    await form.locator('input[name="password"]').fill('StrongP@ss123');
    await form.locator('input[name="password_confirmation"]').fill('StrongP@ss123');
    await form.locator('button[type="submit"]').click();
    await expect(page).toHaveURL(/register/);
    await expect(page.getByText(/this username is taken/i)).toBeVisible();
  });

  test('register rejects username with invalid characters', async ({ page }) => {
    const unique = Date.now();
    await page.goto('/register');
    const form = page.locator('form[action="/register"]');
    await form.evaluate((f: HTMLFormElement) => (f.noValidate = true));
    await form.locator('input[name="name"]').fill('Bad Chars');
    await form.locator('input[name="username"]').fill('user!');
    await form.locator('input[name="email"]').fill(`bad+${unique}@e2e.test`);
    await form.locator('input[name="password"]').fill('StrongP@ss123');
    await form.locator('input[name="password_confirmation"]').fill('StrongP@ss123');
    await form.locator('button[type="submit"]').click();
    await expect(page).toHaveURL(/register/);
    await expect(page.getByText(/letters, numbers, and underscores/i)).toBeVisible();
  });

  test('register rejects username shorter than 3 chars', async ({ page }) => {
    const unique = Date.now();
    await page.goto('/register');
    const form = page.locator('form[action="/register"]');
    await form.evaluate((f: HTMLFormElement) => (f.noValidate = true));
    await form.locator('input[name="name"]').fill('Short User');
    await form.locator('input[name="username"]').fill('ab');
    await form.locator('input[name="email"]').fill(`short+${unique}@e2e.test`);
    await form.locator('input[name="password"]').fill('StrongP@ss123');
    await form.locator('input[name="password_confirmation"]').fill('StrongP@ss123');
    await form.locator('button[type="submit"]').click();
    await expect(page).toHaveURL(/register/);
    await expect(page.getByText(/at least 3 characters/i)).toBeVisible();
  });

  test('register throttles after 5 attempts per minute', async ({ page }) => {
    await page.goto('/register');
    const token = await page.locator('form[action="/register"] input[name="_token"]').inputValue();

    const post = (i: number) =>
      page.request.post('/register', {
        form: {
          _token: token,
          name: `Throttle ${i}`,
          username: `throttle_${Date.now()}_${i}`,
          email: `throttle+${Date.now()}+${i}@e2e.test`,
          password: 'wrong',
          password_confirmation: 'mismatch',
        },
        maxRedirects: 0,
        failOnStatusCode: false,
      });

    for (let i = 0; i < 5; i++) {
      const res = await post(i);
      expect(res.status()).not.toBe(429);
    }

    const blocked = await post(5);
    expect(blocked.status()).toBe(429);
  });

  test('register rejects username longer than 30 chars', async ({ page }) => {
    const unique = Date.now();
    await page.goto('/register');
    const form = page.locator('form[action="/register"]');
    await form.evaluate((f: HTMLFormElement) => {
      f.noValidate = true;
      const u = f.querySelector('input[name="username"]') as HTMLInputElement;
      u.removeAttribute('maxlength');
    });
    await form.locator('input[name="name"]').fill('Long User');
    await form.locator('input[name="username"]').fill('a'.repeat(31));
    await form.locator('input[name="email"]').fill(`long+${unique}@e2e.test`);
    await form.locator('input[name="password"]').fill('StrongP@ss123');
    await form.locator('input[name="password_confirmation"]').fill('StrongP@ss123');
    await form.locator('button[type="submit"]').click();
    await expect(page).toHaveURL(/register/);
    await expect(page.getByText(/30 characters or less/i)).toBeVisible();
  });
});
