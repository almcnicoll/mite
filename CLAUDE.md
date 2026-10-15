# Mite — instructions for Claude

## What this is

A small Laravel 12 / PHP 8.2+ app for a family "daily giving" rotation. Each day one user in a fixed
rotation gets to "pick" a cause; that day is worth a configured amount (`Setup`), which accrues to the
cause. Donations are recorded as they're actually paid out, so each cause has a balance
(picked − donated). The root `/` is the daily screen.

No authentication — every route is open (it's a household tool). `users` has a `password` column but
nothing uses it for login. Don't assume `auth` middleware exists.

## Stack

- Laravel 12, Blade views, Eloquent. No JS framework.
- **Views are self-contained**: `resources/views/layouts/app.blade.php` has all CSS inline; `stats` pulls
  Chart.js from a CDN. The Vite/Tailwind scaffold (`resources/css`, `resources/js`, `vite.config.js`,
  `package.json`) is unused by any view — **there is no frontend build step** and `public/build` isn't
  needed.
- Local DB is MySQL (Laragon); `.env.example` still says sqlite. Sessions/cache/queue use the `database`
  driver, so the `0001_01_01_*` migrations (users/sessions, cache, jobs) matter.
- `plesk/ext-laravel-integration` is a composer dependency (Plesk Laravel Toolkit) — the live host is Plesk.
- **Tab / sort selector** — any page that needs one uses the shared pieces rather than its own: include
  `partials/tab-bar` (`['tabs' => ['key' => 'Label', ...]]`) and call
  `miteTabs('<page-key>', '<default key>', name => {...})` (defined in `layouts/app`). It marks the active
  button and **remembers the last selection per page in the browser** (`localStorage` key
  `mite.tab.<page-key>`, try/catch-guarded). Stats and Causes use it; use it for future pages too.
- Tests: `php artisan test` (sqlite in-memory, `RefreshDatabase`). Feature tests cover home, stats and the
  causes page; JS behaviour (sorting, remembering) isn't covered by PHP tests — check it in a browser.

## Domain model

| Model | Table | Notes |
|---|---|---|
| `User` | `users` | `rotation_order` (unique int, 1..N) drives who picks next. `colour`, `picture` for display. |
| `Cause` | `causes` | `name`, `email`, `notes`, `colour`, `picture`. `totalPicked()`, `totalDonated()`, `balance()`. |
| `Pick` | `picks` | One row per `date` (unique). `user_id` (who's turn), `cause_id` (nullable until chosen), `guest_name` for non-rotation guests. FKs null on delete. |
| `Setup` | `setups` | `date_from` + `amount_per_day`. `Setup::rateFor($date)` = the most recent setup on/before the date. Changing the rate only affects dates from `date_from` onward. |
| `Donation` | `donations` | `cause_id`, `date_paid`, `amount`. |

Key logic:

- **Rotation** — `User::nextInRotation()`: take the latest pick that has a `user_id`, advance to the next
  `rotation_order`, wrapping to 1. Guest picks (`user_id` null) don't advance the rotation.
- **Backfill** — `App\Services\PickBackfillService::backfill()` creates missing `Pick` rows for every date
  from the earliest pick to yesterday, continuing the rotation. It runs on every load of the daily
  screen (`PickController::today`), so missed days "catch up" automatically and show up as *outstanding*
  (picks with a user but no cause, before today).
- **Amount per pick** is never stored; it's always derived via `Setup::rateFor(pick.date)`.
- **Stats** — `StatsController` computes earned vs donated per cause in PHP (it re-implements the rate
  lookup inline rather than calling `Setup::rateFor`) and renders two Chart.js bar charts: all-time
  earned and pending (earned − donated, floored at 0).

## Routes (`routes/web.php`)

- `/`, `/today` → `PickController@today` (both named `picks.today`; the later registration wins for URL
  generation). `/pick/{date}` (YYYY-MM-DD) views a past day, read via the same action.
- `picks` resource (index/edit/update/store/destroy), plus `GET|POST /picks/reset` to bulk-delete outstanding
  picks (those with a user but no cause, before today — they'll be re-created by backfill).
- `users`, `causes` full resources (users has no `show`); `donations` index/create/store/destroy;
  `setup` index/create/store (setups are append-only — no edit/delete).
- `/stats` → `StatsController@index`.

## Gotchas

- **Uploaded pictures** go to the `public` disk (`storage/app/public/pictures`) and are referenced via
  `asset('storage/...')`. The server needs `php artisan storage:link` run once (Plesk Laravel module can
  run artisan). Symptom when `public/storage` is missing: image URLs look right but return a Laravel
  **403** — the request falls through to Laravel's built-in `storage/{path}` route for the `local` disk,
  which demands a signed URL. `asset()` follows the request host, so `APP_URL` doesn't affect these images.
- **Live site**: <https://mite.almcnicoll.co.uk/> (a subdomain; `almcnicoll.co.uk/mite/` just redirects there).
  `APP_URL` on the server should match that. No access control by decision — nothing sensitive in it.
- `todayOrCreate()` returns null when there are no users; the controller redirects to create one. Setup
  must exist before any pick screen works (redirects to `setup.index`).
- `Cause::totalPicked()` and `balance()` load all picks and sum per-row `Setup::rateFor` → N+1 queries.
  Fine at this scale, but watch it if lists get long (causes index/donations create call it per cause).
- Seeders (`UserSeeder`) contain real family email addresses and a placeholder `password`. Don't run
  `db:seed` against production.
- `.env` is gitignored and holds the local DB credentials. Never print or commit it.

## Workflow

- Day-to-day development happens on the `working` branch. `master` is the deployable branch and is only
  updated by merging `working` when deploying. (Both exist on `origin`; there is no `main`.)
- Commit in small, meaningful steps with descriptive messages. Only commit or push when asked.
- Local dev: Laragon serves the project at `http://mite.test` (or use `php artisan serve`). Run
  `php artisan migrate` after pulling new migrations.
- Keep changes minimal and match the surrounding style (4-space indent, `.editorconfig`; controllers do
  inline `$request->validate([...])`, no form requests).

## Deploying

Deployment mechanism: the live host (Plesk shared hosting, same as the pastoreyes, foxhole and opus
projects) pulls from git via a Plesk "Git" webhook. **Only deploy when the user asks.** Pushing to
`working` never deploys.

Status: **webhook URL is in `.deploy-webhook-url`; first deploy done, server first-run setup (`.env`, migrate, storage:link) is the user's** — see [doc/deployment-checklist.md](doc/deployment-checklist.md).
Once the checklist is done and `.deploy-webhook-url` exists, deploy = in order:

1. Make sure `working` is clean and `php artisan test` passes; otherwise stop and tell the user.
2. Check out `master`, merge `working` (ask if conflicts are non-trivial), `git push origin master`.
3. POST to the webhook: `curl -sk -X POST "$(cat .deploy-webhook-url)"` — HTTP **204** is success.
   It must be **POST** (GET does nothing) and `-k` is required (the Plesk cert doesn't validate from here;
   don't disable TLS verification anywhere else).
4. Switch back to `working`.
5. Flag manual steps the webhook can't do. This host **can't run composer after a deploy**, so
   `vendor/` is **committed to git** (dev packages included — simplest) and arrives via the pull.
   - Any change to `composer.json`/`composer.lock` must be followed by `composer install` locally and
     committing the resulting `vendor/` changes in the same push. Never deploy a lock change without the
     matching `vendor/`.
   - Server PHP is **8.5.4**, local is 8.4.x; the lockfile requires `>= 8.4` (Symfony 8). Don't let the
     lock resolve to anything needing newer than the server.
   - New files under `database/migrations/` → tell the user `php artisan migrate --force` must be run
     on the server (the webhook only pulls).
   Record the last-deployed commit in the deploy summary.

`.deploy-webhook-url` holds a bare URL with an embedded secret. It's gitignored. Never commit it or
paste its contents anywhere. If it's missing or empty, say the push happened but nothing was deployed.

## GitHub issues

Bugs and feature requests are tracked at <https://github.com/almcnicoll/mite/issues> (repo
`almcnicoll/mite`). The user will create issues there and ask Claude to retrieve and fix/implement
them. When asked:

1. `gh issue list -R almcnicoll/mite` / `gh issue view <n> -R almcnicoll/mite --comments` to read them.
2. Do the work on `working`; reference the issue in the commit message (`Fixes #<n>`).
3. Don't close issues or comment on them unless asked — merging to `master` with `Fixes #n` will close
   them on push.
