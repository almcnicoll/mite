# Deployment checklist — Mite on Plesk

Method (copied from PastorEyes / foxhole, which are on the same Plesk host): a Plesk **Git** repository
on the server tracks `master` of `github.com/almcnicoll/mite`; a webhook URL tells Plesk to pull.
Claude merges `working` → `master`, pushes, then POSTs the webhook (see CLAUDE.md "Deploying").

Items marked **(you)** need Plesk/server access or secrets, which I can't do. Tick as we go.

## A. Server (Plesk) — you

- [ ] Create the subdomain/domain for Mite and note the **document root**. It must point at the repo's
      `public/` directory (e.g. `httpdocs/public`), not the repo root — otherwise `.env`, `vendor/`, etc.
      are web-served.
- [ ] Set the domain's PHP to **8.2 or newer** (`composer.json` requires `^8.2`) with the usual Laravel
      extensions (mbstring, openssl, pdo_mysql, tokenizer, xml, ctype, json, fileinfo).
- [ ] Create a MySQL database + user for Mite; note host/name/user/password.
- [ ] Plesk → *Git* → add the repository `https://github.com/almcnicoll/mite.git`, branch **`master`**,
      deployment mode **automatic** (so the webhook pulls and deploys).
- [ ] In the Git repo's "Additional deployment actions", add the same as the other projects:
      `composer install --no-dev --optimize-autoloader` (PastorEyes' webhook also runs `npm install`;
      Mite has **no frontend build**, so skip npm).
- [ ] Copy the repo's **webhook URL** (Git → repository → *Webhook / Deployment trigger URL*).

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

- [ ] Put the webhook URL, bare, in `.deploy-webhook-url` at the repo root (gitignored already).
- [ ] Dry run: `curl -sk -X POST -w "%{http_code}" "$(cat .deploy-webhook-url)"` → expect `204`.
- [ ] Confirm in Plesk's Git log that a pull happened.

## D. Repo prep — me (on `working`)

- [x] `working` branch created from `master`.
- [x] `.deploy-webhook-url` added to `.gitignore`.
- [x] `CLAUDE.md` documents workflow + deploy procedure.
- [ ] Fix `.env.example` defaults for Mite (`APP_NAME=Mite`, `DB_CONNECTION=mysql`) so server setup is
      less error-prone. *(small; say if you'd like it)*
- [ ] Decide on access control: the app has **no authentication**, so once it's on a public URL anyone
      can edit it. Options: Plesk password-protected directory (zero code), HTTP basic auth middleware, or
      real login. Needs your decision **before** go-live.
- [ ] Remove the unused Vite/Tailwind scaffold (`package.json`, `vite.config.js`, `resources/css|js`)?
      Optional; avoids Plesk/npm confusion.

## E. First real deploy — us

- [ ] `php artisan test` passes on `working`.
- [ ] Merge `working` → `master`, push, POST webhook (Claude does this on request).
- [ ] Verify the live site; check `storage/logs/laravel.log` on the server for errors.
- [ ] Note in CLAUDE.md that deployment is live (replace the "not yet set up" status line) and record the
      live URL.

## Later / nice to have

- [ ] Database backups (Plesk scheduled backup or a cron `mysqldump`).
- [ ] GitHub Actions running `php artisan test` on `working` pushes.
- [ ] Real tests for rotation, backfill and `Setup::rateFor` — the only tests today are Laravel's stock examples.
