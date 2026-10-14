# Deployment checklist — Mite on Plesk

Method (copied from PastorEyes / foxhole, which are on the same Plesk host): a Plesk **Git** repository
on the server tracks `master` of `github.com/almcnicoll/mite`; a webhook URL tells Plesk to pull.
Claude merges `working` → `master`, pushes, then POSTs the webhook (see CLAUDE.md "Deploying").

Items marked **(you)** need Plesk/server access or secrets, which I can't do. Tick as we go.

## A. Server (Plesk) — you

- [x] Create the subdomain/domain for Mite and note the **document root**. It must point at the repo's
      `public/` directory (e.g. `httpdocs/public`), not the repo root — otherwise `.env`, `vendor/`, etc.
      are web-served.
- [x] Set the domain's PHP to **8.2 or newer** (`composer.json` requires `^8.2`) with the usual Laravel
      extensions (mbstring, openssl, pdo_mysql, tokenizer, xml, ctype, json, fileinfo).
- [x] Create a MySQL database + user for Mite; note host/name/user/password.
- [x] Plesk → *Git* → add the repository `https://github.com/almcnicoll/mite.git`, branch **`master`**,
      deployment mode **automatic** (so the webhook pulls and deploys).
- [x] ~~Additional deployment actions: `composer install`~~ — **not possible on this host.** Leave the
      actions empty; Mite has no frontend build, so no npm either. See "Composer is manual" below.
- [x] Copy the repo's **webhook URL** (Git → repository → *Webhook / Deployment trigger URL*).

## Composer is manual → `vendor/` is committed

The host can't run `composer` after a git deploy, so `vendor/` is committed and arrives with the pull.
Server PHP is 8.5.4; the lockfile needs `>= 8.4`, so that's fine.

- [x] `vendor/` removed from `.gitignore` and committed.
- [ ] **Whenever packages change:** run `composer install` locally, commit `composer.json`,
      `composer.lock` **and** `vendor/` together, then deploy. Claude flags this if it sees a lock change.

> **Note:** the app was already live at <https://mite.almcnicoll.co.uk/> before this workflow existed, so
> most of section B was already done. Remaining known gap: `php artisan storage:link` (images 403 without it).

## B. First-time server setup — you (SSH or Plesk "PHP composer"/"Run command")

- [ ] Create `.env` on the server from `.env.example`. Set: `APP_ENV=production`, `APP_DEBUG=false`,
      `APP_URL=https://<live host>`, `DB_CONNECTION=mysql` + the DB creds, `SESSION_DRIVER=database`,
      `LOG_LEVEL=warning`. (`.env.example` currently defaults to sqlite/local — don't copy blindly.)
- [ ] `php artisan key:generate`
- [ ] `php artisan migrate --force`
- [ ] `php artisan storage:link` (avatars/pictures use `public/storage`)
- [ ] Make `storage/` and `bootstrap/cache/` writable by the web user.
- [ ] **Data**: decide whether live starts empty or from the local DB (dump local → import via phpMyAdmin).
      Do **not** run `db:seed` on prod (seeders hold real emails + placeholder passwords).
      Without data you need at least one Setup + one User before `/` works.
- [ ] `php artisan config:cache && php artisan route:cache && php artisan view:cache` (optional; re-run
      after `.env` changes).
- [ ] Smoke test: load `/`, `/stats`, upload a picture on a cause, record a donation.

## C. Webhook wiring — us

- [x] Put the webhook URL, bare, in `.deploy-webhook-url` at the repo root (gitignored already).
- [ ] Dry run: `curl -sk -X POST -w "%{http_code}" "$(cat .deploy-webhook-url)"` → expect `204`.
- [ ] Confirm in Plesk's Git log that a pull happened.

## D. Repo prep — me (on `working`)

- [x] `working` branch created from `master`.
- [x] `.deploy-webhook-url` added to `.gitignore`.
- [x] `CLAUDE.md` documents workflow + deploy procedure.
- [ ] Fix `.env.example` defaults for Mite (`APP_NAME=Mite`, `DB_CONNECTION=mysql`) so server setup is
      less error-prone. *(small; say if you'd like it)*
- [x] Access control: decided none needed (nothing sensitive). Revisit if that changes.
- [ ] Remove the unused Vite/Tailwind scaffold (`package.json`, `vite.config.js`, `resources/css|js`)?
      Optional; avoids Plesk/npm confusion.

## E. First real deploy — us

- [x] `php artisan test` passes on `working`.
- [ ] Merge `working` → `master`, push, POST webhook (Claude does this on request).
- [ ] Verify the live site; check `storage/logs/laravel.log` on the server for errors.
- [ ] Note in CLAUDE.md that deployment is live (replace the "not yet set up" status line) and record the
      live URL.

## Later / nice to have

- [ ] Database backups (Plesk scheduled backup or a cron `mysqldump`).
- [ ] GitHub Actions running `php artisan test` on `working` pushes.
- [ ] Real tests for rotation, backfill and `Setup::rateFor` — the only tests today are Laravel's stock examples.
