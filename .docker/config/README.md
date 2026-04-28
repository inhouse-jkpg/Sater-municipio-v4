# Local WordPress config (Docker only)

This folder is **only used for local development**. Docker mounts it over
`/var/www/html/config` inside the `wordpress` container. It never ships to
production. Production uses its own `/config/` files on the server.

Files here are loaded by `wp-config.php`:

| File | Purpose |
|---|---|
| `memory.php` | `WP_MEMORY_LIMIT` |
| `content.php` | `WP_CONTENT_DIR`, `WP_CONTENT_URL`, default theme |
| `database.php` | Connects to the `db` container (env-driven with safe defaults) |
| `salts.php` | Dummy salts (local dev only, **never use in production**) |
| `plugins.php` | ACF flags |
| `update.php` | Disables core auto-updates |
| `upload.php` | Upload limits |
| `cron.php` | wp-cron enabled |
| `multisite.php` | Multisite enabled, `DOMAIN_CURRENT_SITE = sater.test` |
| `developer.php` | `WP_DEBUG`, `WP_HOME`, `WP_SITEURL` |
| `cache.php` | Redis object cache points at the `redis` container |

Optional files you can add for local feature work:

- `ad.php` — Active Directory local mock/stub
- `search.php` — Algolia/Typesense connection
- `sentry.php` — Sentry DSN (leave off locally)
- `cookie.php` — cookie consent/domain overrides
- `scripts.php` — script/styles overrides
