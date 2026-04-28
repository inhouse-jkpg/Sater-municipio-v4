# Local development

Spin the site up locally with Docker. The stack includes nginx, PHP-FPM, MariaDB, Redis, phpMyAdmin and Mailpit.

## 1. Prerequisites

- Docker Desktop (or compatible Docker Engine + Compose v2)
- [mkcert](https://github.com/FiloSottile/mkcert) for local HTTPS certs
  - macOS: `brew install mkcert`
  - Linux: install `libnss3-tools` then mkcert from the releases page

## 2. One-time setup

```bash
# 1. Copy env template
cp .env.example .env

# 2. Generate local HTTPS certs (writes into nginx/ssl/)
./certs-setup.sh

# 3. Add hostnames to /etc/hosts (macOS/Linux) or
#    C:\Windows\System32\drivers\etc\hosts (Windows, admin):
#
#       127.0.0.1 sater.test pma.sater.test mail.sater.test

# 4. Put a database dump at db/seed.sql (any filename ending in .sql works).
#    The MariaDB container imports it on first boot.
#    Ask a teammate for a sanitized dump, do not commit it.
```

## 3. Boot the stack

```bash
docker compose up -d
```

Services:

| URL                    | What         |
|------------------------|--------------|
| https://sater.test     | WordPress    |
| https://pma.sater.test | phpMyAdmin   |
| https://mail.sater.test| Mailpit UI   |

## 4. Re-seed the database

If you drop in a new `db/seed.sql` and want to reimport it, reset the DB volume:

```bash
docker compose down -v
docker compose up -d
```

## 5. After seeding: rewrite URLs

A production dump will contain production URLs. Rewrite them to `sater.test`:

```bash
docker compose exec wordpress wp search-replace \
  'https://sater.se' 'https://sater.test' \
  --all-tables --allow-root
```

(Adjust `sater.se` if the production host is different, e.g. `www.sater.se`.)

## 6. Common commands

```bash
# tail logs
docker compose logs -f wordpress

# open a shell in the WordPress container
docker compose exec wordpress bash

# run wp-cli
docker compose exec wordpress wp plugin list --allow-root

# run composer install (uses committed composer.lock)
docker compose exec wordpress composer install

# stop the stack (keep DB volume)
docker compose down

# stop the stack and wipe the DB volume
docker compose down -v
```

## 7. Media assets

The local nginx proxies any missing image/font/video file to production (`https://sater.se$uri`) so you do not need to download the `uploads/` directory. Local media always wins if the file exists.

## 8. Secrets

`.env` is gitignored. ACF Pro, Algolia, S3, SSO etc. can be added locally when you need those features; they are not required to boot the site.

## 9. WordPress config (Docker only)

Production has its own `/config/*.php` files on the server (they are gitignored and stripped during deploy). For local Docker, we keep a parallel set of config files at **`.docker/config/`** that is committed. Docker mounts it into the `wordpress` container over `/var/www/html/config`, so `wp-config.php` loads it without touching anything else.

If you need to override something for your own machine (e.g. point a local plugin at a different service), copy the relevant file, tweak it, and either keep it locally-only or PR it if it should apply to the team.

