# Deployment checklist — Mite on Plesk

**Status: live and fully set up** at <https://mite.almcnicoll.co.uk/>. Last verified 2026-11-24.

Method (same as PastorEyes / foxhole on this Plesk host): a Plesk **Git** repository on the server tracks
`master` of `github.com/almcnicoll/mite`; a webhook URL tells Plesk to pull. Claude merges `working` → `master`,
pushes, then POSTs the webhook. The procedure and standing rules are in CLAUDE.md "Deploying".

## Remaining for you

**Nothing is required** — deployment, mail, cron and reminders are all working. Optional:

- **Recommended — fix the `gh` account so pushes work from this machine.** `gh` has two logins and the active
  one (`almcn-occ`) has no write access to `almcnicoll/mite`, so every push needs a one-off token for the right
  account. Running `gh auth switch --user almcnicoll` fixes it (it changes the active account for your other
  projects too, which is why Claude hasn't done it).
- **Database backups** — Plesk scheduled backup, or a cron `mysqldump`. The live DB has real data (24 causes,
  6 people) and there's no backup arrangement on record.
- **Mine, if you want them** (small, just say): fix `.env.example` defaults (`APP_NAME=Mite`, MySQL instead of
  sqlite); remove the unused Vite/Tailwind scaffold (`package.json`, `vite.config.js`, `resources/css|js`);
  GitHub Actions running `php artisan test` on `working`; tests for rotation, backfill and `Setup::rateFor`
  (feature tests currently cover the daily screen, stats, causes, the person gate and reminders).

## Standing rules for each deploy

- **Composer is manual on this host**, so `vendor/` is committed. Whenever packages change: `composer install`
  locally, then commit `composer.json`, `composer.lock` **and** `vendor/` together. Server PHP is 8.5.4;
  the lockfile needs `>= 8.4`. Claude flags a lock change before deploying.
- **Migrations are manual.** The webhook only pulls. If a deploy adds files in `database/migrations/`, run
  `php artisan migrate --force` on the server (Plesk Laravel module) — Claude flags this after each deploy.
- Reminder emails have a master switch on the Setup page ("Enable reminder emails").

## What's in place (all done)

### Server (Plesk)
- [x] Domain with its document root at the repo's `public/` directory
- [x] PHP 8.5 with the usual Laravel extensions
- [x] MySQL database and user
- [x] Git repo tracking `master`, automatic deployment
- [x] No "additional deployment actions" (composer can't run here; there is no frontend build, so no npm)
- [x] Webhook URL saved in `.deploy-webhook-url` (gitignored; never commit or paste it)

### First-time setup
- [x] Server `.env`, app key, migrations, live data (the site was in use before this workflow existed)
- [x] `php artisan storage:link` — cause pictures load (checked: all return 200)
- [x] Smoke test — `/causes`, `/stats`, `/donations`, `/setup`, `/users` load; the site is in daily use.
      (`/` was deliberately not loaded in the check: opening it creates today's pick.)
- Optional: if config is ever cached (`config:cache`), re-run it after editing `.env`.

### Webhook / deploys
- [x] POST returns **204** and each deploy reaches the site (checked: live Setup page matches the latest commit)
- [x] `working` → `master` → push → webhook flow in use; tests (32) pass before each deploy

### Reminder emails (section F — confirmed complete)
- [x] Reminder columns migrated (checked: new Users checkbox present on live)
- [x] Cron running `php artisan schedule:run`
- [x] Mail configured (`MAIL_*`, sender on the domain, SPF/DKIM)
- [x] `APP_URL=https://mite.almcnicoll.co.uk` (checked: the live email preview links to that host) and `APP_NAME=Mite`
- [x] Timezone checked for the daily run (now 16:00; originally 09:00)
- [x] Master switch on (checked: ticked on live, threshold 5) and tested

### How this was verified (2026-11-24)
Checked from outside against the live site: pages load, latest commit deployed, images served, `APP_URL`,
switch/threshold values, migration columns. **Not checkable from outside**, so taken on your word: cron, SMTP
delivery, timezone, and `.env` values such as `APP_DEBUG=false` (a missing page shows no stack trace).
