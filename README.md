<!-- SHIELDS -->
[![Contributors][contributors-shield]][contributors-url]
[![Forks][forks-shield]][forks-url]
[![Stargazers][stars-shield]][stars-url]
[![Issues][issues-shield]][issues-url]
[![License][license-shield]][license-url]

<p>
  <a href="https://github.com/municipio-se/municipio-deployment">
    <img src="images/municipio.svg" alt="Logo" width="300">
  </a>
</p>
<h3>Municipio (Standard) - Deployment</h3>
<p>
  Simplified deployment of Municipio
  <br />
  <a href="https://github.com/municipio-se/municipio-deployment/issues">Report Bug</a>
  ·
  <a href="https://github.com/municipio-se/municipio-deployment/issues">Request Feature</a>
</p>

## About Municipio (Standard) - Deployment
This repository simplifies the deployment for users of Municpio. Simply fork this repository and setup deployment details for your hosting environment and deploy whenever it suits you. 

This will ensure that deployments can be made by fetching the upstream of the forked repository without any technical knowledge. Guide on how to fetch a upstream repo with github user interface can be found here: https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/working-with-forks/syncing-a-fork.

This Sater deployment is hardened for upstream independence: Composer dependencies and the deploy pipeline are pinned, and a three-branch model (`master`, `production`, `stage`) controls how Helsingborg upstream changes reach production.

**Important:** Ignore `master` for day-to-day work. Do not sync the fork. Develop, test, and deploy using `stage` and `production` only.

## Branch strategy

| Branch | Purpose | Upstream sync | Deploys to |
|--------|---------|---------------|------------|
| `master` | Helsingborg mirror, read-only | Yes, via GitHub "Sync fork" | Nowhere |
| `production` | Locked production code | No | Production server |
| `stage` | Client UAT + integration branch | No | Stage server |

**Rules:**

- **Do not touch `master`.** Do not use "Sync fork", do not commit to it, do not deploy from it. Leave it as-is.
- All real work happens on `production` and `stage` only.
- Never merge `master` into any other branch. If you need something from upstream, review the diff on `master`, then cherry-pick specific commits into `stage`.
- Production deploys only from the `production` branch.
- `stage` is always based on `production`. Cherry-picks from upstream are applied on `stage` for testing, never directly into `production`.
- When `stage` drifts or needs a clean UAT snapshot, reset it from `production` and re-apply any in-flight cherry-picks.

## Local development

This section is **local development only**. It is not the production deploy process. Production is built and deployed by GitHub Actions (see [Deploy overview](#deploy-overview) below).

Spin the site up on your machine with Docker. The stack includes nginx, PHP-FPM, MariaDB, Redis, phpMyAdmin and Mailpit.

Use the root-level `docker compose` setup. Do not use `.devcontainer/` for this project.

**Where commands run**

| Where | Used for |
|-------|----------|
| **Your Mac (host)** | Steps 3, 8, 9 — Composer auth token, `php build.php`, `npm run build` |
| **Docker (`wordpress` container)** | Steps 7, 10 — `composer install`, `wp search-replace`, `wp cache flush` |

The WordPress container does **not** have Node.js/npm. Do not run `php build.php` or `npm run build` inside Docker.

### Prerequisites

- Docker Desktop (or compatible Docker Engine + Compose v2)
- [mkcert](https://github.com/FiloSottile/mkcert) for local HTTPS certs
  - macOS: `brew install mkcert`
  - Linux: install `libnss3-tools` then mkcert from the releases page
- [Node.js](https://nodejs.org/) on your host (for steps 8–9)

### Local setup (step by step)

All steps below are for **local dev** after cloning this repo to your machine.

1. **Clone the `production` branch** (do not use `master`). — *host*

```bash
git clone -b production <repository-url> .
cd "Into your folder"
```

2. **Copy the env template.** — *host*

```bash
cp .env.example .env
```

3. **Configure a GitHub token for Composer** (one-time on your Mac). Required before steps 8–9. Nested packages in the Municipio theme and several mu-plugins are downloaded from `helsingborg-stad/*` on GitHub. Create a token at https://github.com/settings/tokens/new with the `repo` scope if those packages are private. — *host*

```bash
composer config --global github-oauth.github.com YOUR_GITHUB_TOKEN
```

The token is stored in `~/.composer/auth.json`, not in this repo.

4. **Generate local HTTPS certs** (writes into `nginx/ssl/`). — *host*

```bash
./certs-setup.sh
```

5. **Add local hostnames** to `/etc/hosts` (macOS/Linux) or `C:\Windows\System32\drivers\etc\hosts` (Windows, admin): — *host*

```
127.0.0.1 sater.test pma.sater.test mail.sater.test
```

6. **Import the database and start Docker.** Place a sanitized dump in `db/` (any `.sql` filename; do not commit it). MariaDB imports from `db/` only on the **first** container start (empty DB volume). — *host*

```bash
docker compose up -d
```

If you started Docker before adding the dump, reset the DB volume and start again:

```bash
docker compose down -v
docker compose up -d
```

7. **Install WordPress core and PHP dependencies** (plugins, mu-plugins, theme packages via Composer). — *Docker*

```bash
docker compose exec wordpress composer install
```

8. **Build plugin and mu-plugin assets.** Run on your **Mac from the project root**, not inside the container. — *host*

```bash
php build.php
```

This runs each child `build.php` (`composer install`, `npm ci`, and similar). It does **not** compile the Municipio theme CSS/JS.

9. **Build the Municipio theme CSS/JS (required).** Run on your **Mac**, not in Docker. Without this step the site loads unstyled and shows red errors: *"Assets not built"*. Step 8 only installs npm packages for the theme; you must run webpack separately. — *host*

```bash
cd wp-content/themes/municipio
npm run build
cd -
```

This creates `wp-content/themes/municipio/assets/dist/` (gitignored; not in the repo). Production CI builds differently (`php build.php --cleanup --install-npm` on the GitHub runner).

10. **Rewrite production URLs in the database** to local. — *Docker*

Adjust `sater.se` if your dump uses a different production host.

```bash
docker compose exec wordpress wp search-replace \
  'https://sater.se' 'https://sater.test' \
  --all-tables --allow-root

docker compose exec wordpress wp cache flush --allow-root
```

### Local dev notes

- These steps are **only for local development**. CI/production uses the deploy workflows, not this checklist.
- `composer install` (step 7) reads the pinned `composer.lock` and installs into `wp/`, `vendor/`, and `wp-content/`. It does not compile frontend assets.
- Composer-managed `wp-content` (themes, platform mu-plugins) is gitignored. After steps 7–9, `git status` should stay clean except for your own `sater-*` changes.
- Do **not** use `php build.php --cleanup` on your Mac; that flag is for CI deploy and removes dev files.

To import a different dump later, reset the DB volume and start again:

```bash
docker compose down -v
# add or replace the .sql file in db/
docker compose up -d
```

### Services

| URL | What |
|-----|------|
| https://sater.test | WordPress |
| https://pma.sater.test | phpMyAdmin |
| https://mail.sater.test | Mailpit UI |

### Common commands

```bash
# tail logs
docker compose logs -f wordpress

# open a shell in the WordPress container
docker compose exec wordpress bash

# run wp-cli
docker compose exec wordpress wp plugin list --allow-root

# stop the stack (keep DB volume)
docker compose down

# stop the stack and wipe the DB volume
docker compose down -v
```

### Media assets

Local nginx proxies any missing image/font/video file to production (`https://sater.se$uri`) so you do not need to download the `uploads/` directory. Local media always wins if the file exists.

### Secrets

`.env` is gitignored. ACF Pro is installed automatically on wordpress container start from the zip URL in `.env.example` (override with `MUNICIPIO_ACF_PRO_DOWNLOAD_URL` if needed). Algolia, S3, SSO etc. can be added locally when you need those features; they are not required to boot the site.

### WordPress config (Docker only)

Production has its own `/config/*.php` files on the server (they are gitignored and stripped during deploy). For local Docker, we keep a parallel set of config files at **`.docker/config/`** that is committed. Docker mounts it into the `wordpress` container over `/var/www/html/config`, so `wp-config.php` loads it without touching anything else.

If you need to override something for your own machine (e.g. point a local plugin at a different service), copy the relevant file, tweak it, and either keep it locally-only or PR it if it should apply to the team.

> `.devcontainer/` is inherited from the Municipio theme and is not guaranteed to work in this repo. Use the root-level `docker compose` setup instead.

## Upstream independence

This deployment is hardened so upstream package or pipeline changes do not affect production unexpectedly.

**Locked decisions:**

- `vendor/` is gitignored; `composer.lock` is tracked and pins all PHP dependencies
- Forks of `municipio-deploy` and `municipio-e2e-tests` are owned by us, not the client
- Three-branch model: `master`, `production`, `stage`. `stage` is always based on `production` and serves as both client UAT and the integration branch where cherry-picks from `master` are tested before merging into `production`

**What is in place:**

- `composer.lock` is no longer stripped during deploy (`build.php`)
- `composer.local.json` usage is guarded in CI (deploy fails if `"require"` is not empty)
- `acf-export-manager` locked to exact version `1.0.12` across affected `composer.json` files
- `/vendor/` is in `.gitignore`; run `composer install` locally and on CI
- Deploy workflows point at our forked `municipio-deploy` action, with sub-actions pinned to commit SHAs
- `composer install` runs on the GitHub Actions runner; the resulting `vendor/` is deployed to the server via rsync
- Theme `dev-master` Composer references pinned to specific versions or SHAs
- `master` removed from `deploy-production.yml` triggers (syncing upstream no longer deploys)

## Custom code (mu-plugins)

Municipio is built around **must-use plugins** (`wp-content/mu-plugins/`). This is the recommended place for site-specific customizations, and it is how Helsingborg ships most of the platform itself (component library, Kirki, ACF field types, multisite helpers, and similar packages).

### Why mu-plugins?

- **Always active** on every site in the multisite network. Editors cannot accidentally deactivate them in wp-admin.
- **Load early**, before regular plugins, so hooks and filters are available when the rest of the stack boots.
- **Composer-managed**: packages with type `wordpress-muplugin` or `acf-plugin` install into `wp-content/mu-plugins/` automatically (see root `composer.json` installer paths).
- **Stable across deploys**: committed custom mu-plugins are part of the repo and deploy with `stage` / `production`. They are not treated as optional add-ons.

Regular plugins under `wp-content/plugins/` are fine for optional or third-party tools, but **Sater-specific behavior should live in mu-plugins**.

### How loading works in this repo

WordPress only auto-loads PHP files directly in `mu-plugins/`, not files in subfolders. Municipio solves this with `wp-content/mu-plugins/loader.php`, which scans subdirectories and loads each plugin's main file (folders prefixed with `_` are skipped).

Each custom plugin is a folder with a standard WordPress plugin header, for example:

```
wp-content/mu-plugins/sater-my-feature/
  sater-my-feature.php   ← Plugin Name header here
```

### Sater custom plugins

Site-specific code lives in `wp-content/mu-plugins/sater-*` (e.g. `sater-internal-link-picker`, `sater-mediaflow-video-modularity`, `sater-latest-events-modularity`). These are committed to git and deployed normally.

Custom **Modularity modules** can also be mu-plugins: register a module class extending `\Modularity\Module` in the plugin's main file, same as a regular plugin module.

### Server-only overrides (`local_`)

For code that should exist only on a specific server and never come from git, use the `local_` prefix under mu-plugins:

- `wp-content/mu-plugins/local_*` (excluded from rsync; survives deploys)

Plugins under `wp-content/plugins/local_*` that are committed to git deploy normally. Server-only copies there are not rsync-excluded and may be removed on deploy (`--delete`). Prefer mu-plugins for true server-only overrides.

Do not use `local_` for code that should be shared across environments; commit it under `sater-*` instead.

## Adding custom dependencies
You may add your own dependencies in the `composer.local.json` file. Add packages to the `require` or `require-dev` sections as needed:

```json
{
  "name": "municipio-se/municipio-deployment-custom",
  "license": "MIT",
  "description": "Additions for your own install of Municipio.",
  "require": {
    "vendor/package": "^1.0"
  },
  "require-dev": {
    "vendor/dev-package": "^2.0"
  }
}
```

When `composer install` runs, the build process will:
1. Temporarily merge your local requirements into `composer.json`
2. Run the installation
3. Automatically restore the original `composer.json` and `composer.lock`

This ensures no permanent modifications are made to version-controlled files while still allowing custom dependencies. The merge only happens when `composer.local.json` contains actual requirements.

**CI note:** Deploy workflows fail if `composer.local.json` has any packages in `"require"`. Keep `"require": {}` empty in the repo. Local overrides are only for explicitly intended cases.

You may also add plugins locally to your server with the folder name prefixed with `local_`. For mu-plugins (`wp-content/mu-plugins/local_*`), the deploy action excludes them from rsync so server copies survive. For regular plugins (`wp-content/plugins/local_*`), only committed copies in git are deployed reliably.

## Parameters
Add the following secrets to your github repository secrets section (https://docs.github.com/en/actions/security-guides/encrypted-secrets). We do recommend that you assign these secrets locally to your repository. You can however use organization level secret to everything except the path if you determine that they will persist. 

### Configuration - Production
Used for branch names: production

| Secret name                     | Description                                                                  | Required |
|---------------------------------|------------------------------------------------------------------------------|----------|
| DEPLOY_REMOTE_HOST_PROD         | Host domain or ip                                                            | true     |
| DEPLOY_REMOTE_PATH_PROD         | Host deployment path                                                         | true     |
| DEPLOY_REMOTE_BACKUP_DIR_PROD   | Host rsync backup path                                                       | true     |
| DEPLOY_REMOTE_USER_PROD         | Host deploy ssh user name (In sudoers with nopassword enabled)               | true     |
| DEPLOY_KEY_PROD                 | Host deploy ssh user key (Private part of ssh key)                           | true     |
| WEB_SERVER_USER_PROD            | Host web server user                                                         | true     |
| PHP_VERSION                     | What version of PHP that should be used (target env, build)                  | true     |
| GITHUB_TOKEN                    | Github token for github npm package usage, use built in secrets.GITHUB_TOKEN | true     |
| ACF URL                         | A url where a zip-file with ACF PRO can be found (ACF provides a url).       | true     |

### Configuration - Stage
Used for branch names: stage, beta, test

| Secret name                     | Description                                                                  | Required |
|---------------------------------|------------------------------------------------------------------------------|----------|
| DEPLOY_REMOTE_HOST_STAGE        | Host domain or ip                                                            | true     |
| DEPLOY_REMOTE_PATH_STAGE        | Host deployment path                                                         | true     |
| DEPLOY_REMOTE_BACKUP_DIR_STAGE  | Host rsync backup path                                                       | true     |
| DEPLOY_REMOTE_USER_STAGE        | Host deploy ssh user name (In sudoers with nopassword enabled)               | true     |
| DEPLOY_KEY_STAGE                | Host deploy ssh user key (Private part of ssh key)                           | true     |
| WEB_SERVER_USER_STAGE           | Host web server user                                                         | true     |
| PHP_VERSION                     | What version of PHP that should be used (target env, build)                  | true     |
| GITHUB_TOKEN                    | Github token for github npm package usage, use built in secrets.GITHUB_TOKEN | true     |
| ACF URL                         | A url where a zip-file with ACF PRO can be found (ACF provides a url).       | true     |

## Deploy overview

Production and stage deploy via **GitHub Actions** using our forked `municipio-deploy` action. The root `docker-compose.yml` is for local development only; it is not what runs on the servers.

### What triggers a deploy

| Environment | Branch | Workflow |
|-------------|--------|----------|
| Production | `production` | `deploy-production.yml` |
| Stage | `stage`, `beta`, `test` | `deploy-stage.yml` |

Both workflows also support manual runs (`workflow_dispatch`). Pushing to `master` does **not** deploy.

### What CI does (build → rsync)

On each deploy, GitHub Actions:

1. Checks out the branch and validates `composer.local.json` has an empty `"require": {}`
2. SSH preflight against the target server (disk space, PHP version, write permissions)
3. Runs `composer install` on the runner (populates `vendor/`, `wp/`, plugins, mu-plugins, themes)
4. Restores `wp-content/plugins/modularity` from git, downloads ACF Pro from the `ACF URL` secret, then stashes Modularity
5. Runs `php ./build.php --cleanup`, which executes child `build.php` scripts and strips dev-only files (`composer.json`, `README.md`, Docker files, `db/`, and similar)
6. Restores the stashed Modularity folder
7. Rsyncs the built workspace to the server (`--delete`, with per-deploy backups). Modularity is excluded from this pass
8. Rsyncs `wp-content/plugins/modularity/` in a second pass

`build.php --cleanup` removes dev-only files (`composer.json`, `README.md`, Docker files, `db/`, and similar) but **does not** remove `vendor/` or `composer.lock`. There is no server-side `composer install`; `vendor/` is built on the runner and rsynced to the server.

### What persists on the server (never overwritten by deploy)

These paths must already exist on the target host and are excluded from rsync:

| Path | Why |
|------|-----|
| `/config/*.php` | Production WordPress config (gitignored, stripped by `build.php`) |
| `wp-content/uploads/` | Media files |
| `wp-content/mu-plugins/local_*` | Server-only mu-plugins |
| `wp-content/languages/` | Translation files |
| `wp-content/fonts/` | Font files |
| `.htaccess` | Web server rules |

`wp-content/plugins/modularity` is excluded from the main rsync but deployed in a dedicated second rsync step after the build.

Use `config-example/` as a template when setting up a new server. Copy files to `/config/` on the host and remove the `-example` suffix where needed.

### After deploy

The action sets file permissions, compresses rsync backups (kept ~7 days), and clears caches via WP-CLI (`wp cache flush`, nginx-helper purge, blade cache) as best effort.

## Additional Setup
A fully functional website will not be automatically created when this deployment script has been executed. Some local site configuration has to be created in the a ./config/ folder on the the local machine. This is basically a wp-config.php split in multiple files for a better overview of the configuration.

All neccesary configuration-example files can be found in the ./config-example folder in this repository. All files ending in -example.php is optional. To use them, simply remove the '-example' extenstion.

The configuration files should be reviewed in full in order to configure the site to your likings.

For local Docker, WordPress config lives at `.docker/config/` and is mounted into the container. Production server config in `./config/` remains separate and is not committed.

## Contribution
You may contribute to this repository if you feel that anything is missing. Simply send a pull request, and we will review it as soon as possible. 

## Suggested target environment
We do suggest that you include the following softare on the target machine.

- Litespeed (prefered option) / NGINX / Apache
- PHP ^8.3
- Rsync (required for deployment)
- MySQL or MariaDB
- Caddy as a Reverse Proxy (ssl termination etc)

### Optional addons
Municipio runs better with these additional packages, applications and settings. 

- Redis (highly encouraged)
- Imagic (highly encouraged)
- OpCache (highly encouraged)
- S3 Compatible Object storage (Tested with Swift)

### Adding a package
If you want to add a package, register it using Composer as usual (see the Composer require command: https://getcomposer.org/doc/03-cli.md#require-r).
In some cases, Composer may fail due to dependency conflicts caused by leftover local cache or build artifacts. If this happens, run the composer update-lockfile command. This will execute Composer inside an isolated container and automatically clear any leftover residue from previous runs. The lockfile MUST be committed in all cases. 

### Resources
What resources you should give the machine is highly individual depending on your anticipated amount of traffic. But let each PHP process have at least 512MB memory to allocate. This high amount is due to some image processing being made in runtime. 

### Known issues
- Municipio platform do not perform well in highly virtualized platforms sutch as Virtouzzo or Docker containers due to lack in efficiency of disk access.
- Root-level Docker (`docker compose up`) is supported for local development. Production performance on virtualized hosts still depends on disk I/O.

## License
Distributed under the [MIT License][license-url].

## Acknowledgements
- [othneildrew Best README Template](https://github.com/othneildrew/Best-README-Template)


<!-- MARKDOWN LINKS & IMAGES -->
<!-- https://www.markdownguide.org/basic-syntax/#reference-style-links -->
[contributors-shield]: https://img.shields.io/github/contributors/municipio-se/municipio-deployment.svg?style=flat-square
[contributors-url]: https://github.com/municipio-se/municipio-deployment/graphs/contributors
[forks-shield]: https://img.shields.io/github/forks/municipio-se/municipio-deployment.svg?style=flat-square
[forks-url]: https://github.com/municipio-se/municipio-deployment/network/members
[stars-shield]: https://img.shields.io/github/stars/municipio-se/municipio-deployment.svg?style=flat-square
[stars-url]: https://github.com/municipio-se/municipio-deployment/stargazers
[issues-shield]: https://img.shields.io/github/issues/municipio-se/municipio-deployment.svg?style=flat-square
[issues-url]: https://github.com/municipio-se/municipio-deployment/issues
[license-shield]: https://img.shields.io/github/license/municipio-se/municipio-deployment.svg?style=flat-square
[license-url]: https://raw.githubusercontent.com/municipio-se/municipio-deployment/master/LICENSE
