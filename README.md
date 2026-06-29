# Chirper Together

![Chirper Together](public/chirp-together.png)

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)
![Blade](https://img.shields.io/badge/Blade-Components-F05340?logo=laravel&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-4-38B2AC?logo=tailwindcss&logoColor=white)
![DaisyUI](https://img.shields.io/badge/DaisyUI-5-5A0EF8?logo=daisyui&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-7-646CFF?logo=vite&logoColor=white)
![SQLite](https://img.shields.io/badge/SQLite-default-003B57?logo=sqlite&logoColor=white)
![Pest](https://img.shields.io/badge/Pest-4-8B5CF6?logo=php&logoColor=white)
![Playwright](https://img.shields.io/badge/Playwright-E2E-2EAD33?logo=playwright&logoColor=white)
![Resend](https://img.shields.io/badge/Resend-Mail-000000?logo=resend&logoColor=white)
![Laravel Cloud](https://img.shields.io/badge/Deploy-Laravel%20Cloud-FF2D20?logo=laravel&logoColor=white)

Twitter-style social mini-network built with Laravel 12. Users post short chirps, follow each other, comment, react with like/dislike, and get notified when others interact with their content.

Live: [chirper-master-ej8kbb.laravel.cloud](https://chirper-master-ej8kbb.laravel.cloud)

## Stack

- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Blade components, Vite 7, TailwindCSS 4, DaisyUI 5
- **Database**: SQLite (default), MySQL/PostgreSQL compatible via Eloquent
- **Mail**: Resend (`resend/resend-laravel`) — `MAIL_MAILER=resend`, set `RESEND_API_KEY` in `.env`
- **Bot protection**: Cloudflare Turnstile on login/register — set `TURNSTILE_SITE_KEY` and `TURNSTILE_SECRET_KEY` in `.env` (skipped automatically when blank in local dev)
- **Payments**: Stripe via Laravel Cashier — set `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`, `STRIPE_VERIFIED_PRICE_ID`, `CASHIER_CURRENCY=brl`. Local webhook forwarding: `stripe listen --forward-to localhost:8000/stripe/webhook`
- **2FA**: TOTP secrets and recovery codes stored on `users` (encrypted), QR code rendered as inline SVG
- **Testing**: Pest 4 + pest-plugin-laravel; SQLite `:memory:` for fast feature tests
- **E2E**: Playwright
- **Deploy**: Laravel Cloud — Flex 256 MiB compute (US East / Ohio), Serverless Postgres 17 (¼ unit), edge network with DDoS protection + CDN + edge caching

## Features

| Area | Highlights |
|------|-----------|
| Auth | Register (with unique `username`, 3–30 chars, `[A-Za-z0-9_]`), login, email verification (Resend), password reset; strong password policy via `Password::defaults()`; Cloudflare Turnstile CAPTCHA on login/register; logout gated by `verified` middleware — unverified accounts must confirm email before signing out |
| Two-factor auth | TOTP enrollment with QR code at `/settings/two-factor`, single-use recovery codes (regenerable behind `current_password`), guest-only `/two-factor-challenge` after login, throttled 6/min |
| Mail | Transactional mail via Resend (verification + password reset) |
| Chirps | Create, edit (owner), delete, image-only attachment (`jpg/jpeg/png/webp/gif`, ≤ 2 MB), like/dislike reactions |
| Feed | Twitter-style tabs on `/`: "For you" (all chirps) vs "Following" (chirps from followed users + self); selection survives pagination via `?feed=following`; empty Following state links to search to discover users |
| Mentions | `@username` mentions parsed from chirp body (3–30 chars, `[A-Za-z0-9_]`); resolved handles render as profile links, unknown handles stay as plain text, HTML escaped first to keep rendering XSS-safe; mentioned users receive a `NewMentionNotification` on create; edits only notify *newly added* mentions (no re-notify on minor edits); self-mentions silent |
| Hashtags | `#tag` parsed from chirp body; clickable hashtags link to per-tag listing at `/tag/{slug}`; tag page shows all chirps containing that hashtag; multiple tags per chirp supported |
| Verified badge | Paid R$ 5.00/month subscription via Stripe + Laravel Cashier grants a blue verified checkmark next to the user's name across the app; `/settings/billing` exposes Stripe Hosted Checkout, the Customer Portal, and cancel-at-period-end; webhook listener flips `users.verified_at` on `invoice.payment_succeeded` and clears it on `customer.subscription.deleted` / `invoice.payment_failed` |
| Comments | Threaded under chirps, inline edit, owner delete, like/dislike |
| Follow | Follow/unfollow users; counters on profile |
| Profile | Public `/users/{user}` page with avatar, bio, chirp list, follower counters |
| Search | Find chirps and users by name/email/message with LIKE-wildcard escaping |
| Command palette | `Ctrl/Cmd+K` opens a debounced live-suggest modal hitting `/search/suggest` for users + chirps, with "see all results" fallback |
| Bookmarks | Save chirps for later via per-card toggle; dedicated `/bookmarks` index; navbar bookmark icon shows saved-count badge (cached per user); removing a bookmark leaves the chirp untouched |
| Notifications | Database channel for new followers, comments, likes, and `@`-mentions; navbar bell with unread badge (cached per user); per-item delete and "Clear all" |
| Access control | Home, search, notifications, bookmarks, settings, and logout gated behind `auth` + `verified` middleware |
| Rate limiting | Per-route throttles: register `5/min`, password reset `6/min`, verification resend `6/min`, 2FA challenge `6/min`, chirp/comment create `20/min`, reactions `60/min`; login limited to 3 attempts per 15 min per `email+IP` |
| Security headers | CSP, HSTS (prod), `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, `Permissions-Policy`, COOP/CORP cross-origin isolation; HTTPS enforced in production |
| Hardening | Boot aborts when `APP_DEBUG=true` under `APP_ENV=production` to prevent accidental stack-trace leaks |
| Theme | Light/dark toggle (DaisyUI `theme-controller`) with `localStorage` + `prefers-color-scheme`, applied inline before paint to avoid flashes |
| UX polish | Mobile-first responsive layout (DaisyUI utilities), navbar collapses into a hamburger dropdown on mobile/tablet, unified `x-alert` component for flash + validation messages, live character counters on textareas, long-word wrapping, DaisyUI numbered pagination (hidden when results fit one page) |

## Project layout

```
app/
  Http/Controllers/      Chirp, Comment, Follow, Like, Notification, Search, User, Bookmark, Tag, Settings\Billing
  Notifications/         NewFollower, NewComment, NewLike, NewMention (database channel)
  Listeners/             HandleVerifiedSubscription (Cashier WebhookReceived)
  Models/                User (Billable), Chirp, Comment, Like, Follow, ChirpAttachment, Bookmark
database/migrations/     Users, chirps, likes, comments, follows, notifications, bookmarks,
                         subscriptions, subscription_items, customer columns, verified_at, ...
resources/
  views/                 Blade pages + chirps/comments components
  js/                    charCounter, commentEdit, userReaction, sendChirpAttachment, commandPalette
  css/app.css            laravelChirper + laravelChirperDark DaisyUI themes
routes/                  Split per resource: web, auth, chirps, comments, follows,
                         users, search, notifications, bookmarks, tags, billing,
                         two_factor, password, profile, verification
tests/Feature/           Pest feature tests (auth, chirps, comments, follows, ...)
tests/e2e/               Playwright specs + global-setup + helpers
app/Console/Commands/    E2eResetCommand (php artisan e2e:reset)
```

## Setup

```bash
git clone <repo> chirper
cd chirper
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Or use the composer shortcut: `composer setup`.

For local dev with hot reload (server + queue + Vite in parallel):

```bash
composer dev
```

## Testing

PHP feature tests (Pest):

```bash
./vendor/bin/pest
# or
composer test
```

E2E tests (Playwright):

```bash
npm run test:e2e            # headless, record video per test
npm run test:e2e:headed     # watch the browser drive itself
npm run test:e2e:ui         # interactive UI mode (best for debugging)
npm run test:e2e:codegen    # record clicks and emit a new spec
```

The Playwright config auto-starts `php artisan serve --port=8000`. A
`globalSetup` hook builds Vite assets (when missing) and runs `php artisan
e2e:reset` to drop the database and seed two verified users
(`alice@e2e.test` / `bob@e2e.test`, password `password123`) plus baseline
chirps. A per-test fixture re-seeds before every spec so tests never
collide on shared state.

Videos and traces land under `test-results/<test-name>/`. The HTML
report is available with:

```bash
npx playwright show-report
```

Suites cover auth (including two-factor), chirps CRUD + reactions,
comments CRUD + reactions, follow/unfollow, navbar and dedicated search,
bookmarks, notifications, password reset, theme toggle, and profile
editing.

## Notifications

Four notification types are dispatched on the `database` channel:

- `NewFollowerNotification` — sent on first follow (no spam on repeat clicks)
- `NewCommentNotification` — sent to chirp owner when a different user comments
- `NewLikeNotification` — sent to chirp owner on new reaction or reaction type change; self-reactions are silent
- `NewMentionNotification` — sent to each user `@`-mentioned in a chirp; self-mentions are silent; edits only notify *newly added* mentions (diff against the original message)

The navbar bell shows unread count; visiting `/notifications` marks everything read.

## Themes

Two DaisyUI themes are defined in `resources/css/app.css`: `laravelChirper` (light) and `laravelChirperDark` (dark). Selection is persisted in `localStorage` and applied via an inline `<script>` in `<head>` before paint to avoid flashes.

## Security

- **Strong passwords** — `Password::defaults()` enforces min length, mixed case, numbers, and symbols on register / reset.
- **Turnstile CAPTCHA** — login and register submissions are verified server-side against Cloudflare Turnstile when keys are configured.
- **Two-factor authentication** — opt-in TOTP at `/settings/two-factor`. After a successful login on a 2FA-enabled account the user is redirected to `/two-factor-challenge` (guest-only, throttled `6/min`) and can authenticate with either a TOTP code or a one-time recovery code. Recovery codes can be regenerated behind `current_password`.
- **Rate limiting** — every authentication endpoint and write endpoint is throttled. Login uses a custom limiter of 3 attempts per 15 min per `email+IP`; register, password reset, verification resend, and 2FA challenge use Laravel `throttle:` middleware; chirp/comment create are `20/min` and reactions `60/min`.
- **HTTP security headers** — `SecurityHeaders` middleware sets `Content-Security-Policy`, `Strict-Transport-Security` (production only), `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy` (camera/mic/geolocation/payment disabled), and COOP / CORP for cross-origin isolation.
- **HTTPS in production** — schema is forced to HTTPS and `upgrade-insecure-requests` is added to the CSP when `APP_ENV=production`.
- **Attachment hardening** — chirp uploads are restricted to images (`jpg/jpeg/png/webp/gif`, ≤ 2 MB) to remove PDF/text attack surface; uploads run inside a DB transaction and the file is cleaned up if the commit fails.
- **Boot guard** — application refuses to boot when `APP_DEBUG=true` under `APP_ENV=production`, preventing stack-trace leaks in deployed environments.
- **Authorization** — chirp edit/delete gated by `ChirpPolicy`; comments and follows check ownership / self-follow in the controllers; logout requires a verified email so unverified sessions cannot escape the verification wall.
