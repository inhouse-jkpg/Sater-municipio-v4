# Production build and deploy (complete picture)

This repo deploys to production via **GitHub Actions** and the composite action **`helsingborg-stad/municipio-deploy/4.0@master`**. The root `docker-compose.yml` and `Dockerfile` are for **local development**, not the production deploy artifact.

## What triggers a production deploy

Production deploy workflow: `.github/workflows/deploy-production.yml`

- Triggers on:
  - manual run: `workflow_dispatch`
  - push to branches: `production`, `master`
- Runs:
  - `helsingborg-stad/municipio-deploy/4.0@master`

Stage deploy workflow: `.github/workflows/deploy-stage.yml`

- Triggers on:
  - manual run: `workflow_dispatch`
  - push to branches: `stage`, `beta`, `test`
- Runs:
  - `helsingborg-stad/municipio-deploy/4.0@master`

Stage smoke tests workflow: `.github/workflows/smoke-tests-stage.yml`

- Triggers on:
  - manual run: `workflow_dispatch`
  - completion of: `Build and deploy stage, beta, test release.`
- Runs:
  - `helsingborg-stad/municipio-smoke-tests@v1` against sitemaps in `vars.E2E_SITEMAP_URLS`

## CI guardrails in this repo

Both deploy workflows contain a guard step:

- `composer.local.json` **must** have an empty `"require": {}` in CI.
- If it is not empty, the workflow fails early.

This prevents accidental drift from custom Composer requirements during deploy.

## What the deploy action does (build artifact creation)

The composite action `helsingborg-stad/municipio-deploy` version 4.0 builds the deployable site on the GitHub runner, then rsyncs it to the target server.

### 1) Preflight checks against the target server (SSH)

Unless `skip-preflight` is set to true, the action validates it can:

- SSH into the host
- create, chmod/chgrp, and remove files
- verify disk space
- verify the PHP version on the host matches the configured `php-version`

### 2) Setup build tooling on the GitHub runner

- Node is installed (fixed by the action): `22.15.0`
- PHP is installed (from workflow secret): `${{ secrets.PHP_VERSION }}`
- Composer v2 is installed
- Caches used:
  - Composer cache keyed by `**/composer.lock`
  - npm cache keyed by `**/package.json`
- Temporary npm auth:
  - The action writes a temporary `~/.npmrc` that authenticates to GitHub Packages for the `@helsingborg-stad` npm scope using `github-token`, then deletes it after the build.

### 3) Install PHP dependencies via Composer

Runs:

- `composer validate`
- `composer install --prefer-dist --no-progress --no-suggest --optimize-autoloader --classmap-authoritative`
- Writes `composer.versions.md` via `composer show` (useful for auditing exactly what got installed in CI for a given deploy).

Key implications for this repo:

- WordPress core is installed via Composer into the `wp/` directory because `composer.json` sets:
  - `"wordpress-install-dir": "wp"`
- Plugins, mu-plugins, and themes are installed into `wp-content/...` via `composer/installers` using the installer paths defined in root `composer.json`.

### 4) Fetch ACF Pro

The action downloads ACF Pro from the secret `acf-url`:

- downloads zip to `acf.zip`
- unzips into `wp-content/plugins/`
- removes the zip

### 5) Run build scripts and cleanup

The action runs the repo root script:

`php ./build.php --cleanup --no-composer-in-child-packages --install-npm`

What the root `build.php` does:

- Searches these directories for child `build.php` scripts and executes them:
  - `wp-content/themes/*`
  - `wp-content/plugins/*`
  - `wp-content/mu-plugins/*`
- With `--cleanup`, it removes paths that should not end up on a public server. The list includes (not exhaustive):
  - `.git`, `.github`, `.devcontainer`, `.vscode`
  - `db`
  - `install.html`
  - `config` (important: production config is expected to exist on the server, not be shipped)
  - `wp-content/uploads` (uploads are expected to exist on the server, not be shipped)

#### Root `build.php` behavior (more in depth)

This repo’s root `build.php` is a simple “orchestrator” script. Key behavior:

- **CLI only**: If it is ever loaded in a web request context, it returns immediately and does not run any build logic.
- **Discovery**: It scans only one level deep under these directories:
  - `wp-content/themes/*`
  - `wp-content/plugins/*`
  - `wp-content/mu-plugins/*`
  If a directory contains a `build.php`, it will run it.
- **Execution model**: For each found child build script:
  - changes working directory into the child package folder
  - runs `php build.php` with a set of forwarded flags
  - if any child script exits non-zero, the entire deploy build fails
- **Flags and what they mean in the root script**:
  - `--cleanup`: after all child builds complete, delete “removables” from the repo root (see below)
  - `--no-composer-in-child-packages`: passed through as `--no-composer` to child scripts. The intent is: CI does the main `composer install` once at the repo root, and child packages should not run composer again.
  - `--install-npm`: passed through verbatim to child scripts. Whether it does anything depends entirely on that child script implementation.
- **Cleanup scope**: The root `--cleanup` step deletes a hardcoded list with `rm -rf`, including:
  - repo metadata and dev tooling: `.git`, `.github`, `.devcontainer`, `.vscode`
  - local only/runtime: `wp-content/uploads`
  - configuration: `config` and `composer.local.json`
  - repository only files: `build.php`, `composer.json`, `README.md`, `install.html`, `db`
  This is why production config is expected to live on the server in `/config` and never be deployed.
- **Output**: It prints timings per child package and a total runtime summary.

#### What child `build.php` scripts exist in this repo

At the time of writing, the root script finds these child build scripts under `wp-content/plugins/*`:

- `wp-content/plugins/media-usage/build.php`
  - Runs **Composer** (install + dump-autoload) unless `--no-composer` is passed.
  - Contains npm/webpack/gulp build logic, but it is currently **commented out**, so it does not build JS/CSS in CI.
  - With `--cleanup`, it deletes dev files such as `package.json`, `package-lock.json`, `webpack.config.js`, `node_modules`, `source/`, etc. from the plugin folder (to reduce what gets deployed).
- `wp-content/plugins/local_ultimate-branding/build.php`
  - Does not run any build tooling. It only defines `BRANDA_BUILD_TYPE`.

Implication:

- Today, the “build” phase is mostly about **cleanup and pruning** (removing dev files) and optionally running Composer in child packages, not about compiling frontend assets with Node.

### 6) Rsync to the target host

The action deploys with `burnett01/rsync-deployments@7.0.1` using switches:

- `-avzrog --delete --backup --backup-dir=<backup>/<sha>`

Important rsync exclusions (high impact):

- `/config` and `config-example`
- `wp-content/uploads`
- `wp-content/plugins/local_*` and `wp-content/mu-plugins/local_*` (server local plugins survive deploy)
- `wp-content/languages`
- `wp-content/fonts`
- `.htaccess`
- and some other project specific exclusions

Note on backups:

- Because rsync uses `--backup --backup-dir=<...>/<sha>`, any changed or deleted files are copied to a per-deploy backup directory on the target host.
- The action then compresses that backup directory to `<sha>.tar.gz` and deletes compressed backups older than 7 days.

## Deployed payload, created folders, and excluded paths (explicit)

This section is intentionally literal. Production deploy rsyncs `path: .` from the GitHub Actions workspace to the server, after the build steps have run, but with explicit exclusions.

### What the CI build creates or populates before rsync

These are the key directories and files that are created or populated in the GitHub Actions workspace by the deploy action, and therefore can be deployed (unless later removed by cleanup or excluded by rsync).

- **`vendor/`**: created by `composer install` at the repo root.
- **`wp/`**: created by `composer install` because `composer.json` sets `"wordpress-install-dir": "wp"`.
- **`wp-content/plugins/`**:
  - populated by Composer installed plugins
  - additionally, ACF Pro is downloaded as a zip and unzipped into `wp-content/plugins/`
- **`wp-content/mu-plugins/`** and **`wp-content/themes/`**:
  - populated by Composer installs using the installer paths from root `composer.json`
- **`composer.versions.md`**: created by `composer show >> composer.versions.md` to record installed versions for that build.

Important: the action writes a temporary `~/.npmrc` for authenticated npm access during the build, but it deletes it before rsync. It is not part of the deployed payload.

### What is removed from the deploy payload by `build.php --cleanup`

Before rsync runs, the action executes `php ./build.php --cleanup ...`. That cleanup step removes these paths (if present) from the workspace using `rm -rf`, so they will not be deployed:

- `.git`
- `.gitignore`
- `GitVersion.yml`
- `config`
- `wp-content/uploads`
- `.github`
- `build.php`
- `composer.json`
- `composer.local.json`
- `post-install.php`
- `images`
- `README.md`
- `guide`
- `db`
- `.devcontainer`
- `.vscode`
- `install.html`

This is in addition to any cleanup performed by child build scripts that may run under `wp-content/*/*/build.php`.

### What rsync deploys

Rsync deploys the post-build workspace to `deploy-host-path` with `--delete`. That means:

- Anything present in the workspace (after cleanup) is eligible to be deployed.
- Anything present on the server but not present in the workspace can be deleted, unless excluded.

### Full rsync exclusions (these paths are NOT deployed)

These are the explicit rsync excludes configured in `municipio-deploy/4.0`:

- `/config`
- `config-example`
- `wp-content/uploads`
- `wp-content/plugins/gravityforms`
- `wp-content/plugins/wp-schema-pro`
- `track-assets`
- `wp-content/plugins/volontar-wordpress`
- `wp-content/languages`
- `.htaccess`
- `security.txt`
- `wp-content/plugins/local_*`
- `wp-content/mu-plugins/local_*`
- `wp-content/fonts`

Implications of the above:

- **`/config/*`** and **`wp-content/uploads/*`** must exist on the server and persist across deploys. They are never overwritten by deploy.
- Any plugin folder prefixed with **`local_`** on the server is treated as host-managed state and is preserved across deploys.

This means:

- The action deploys the built code to the server.
- It does **not** overwrite production configuration in `/config`.
- It does **not** delete or overwrite media uploads.

### 7) Server-side post deploy steps

After rsync:

- compress the rsync backup directory and delete backups older than 7 days
- set group read permissions across the deploy path, and group read/write under uploads
- clear caches via WP CLI on the server:
  - `wp nginx-helper purge-all` (best effort)
  - `wp cache flush` (best effort)
  - clear blade cache directories (best effort)
- optionally:
  - delete LiteSpeed page cache directory if provided
  - kill `lsphp` processes if configured
  - ping NewRelic and Sentry deploy endpoints if configured

## What must already exist on the production server

### `/config/*.php` files

This repo’s `wp-config.php` loads configuration from `config/*.php`. The `config/` directory is:

- gitignored (see `.gitignore`)
- removed by `build.php --cleanup`
- excluded by rsync

So production must have its own `config/` folder already present on the server.

Required files (hard required by `wp-config.php`):

- `config/memory.php`
- `config/salts.php`
- `config/content.php`
- `config/database.php`
- `config/plugins.php`
- `config/update.php`
- `config/upload.php`
- `config/cron.php`

Optional files (loaded if present):

- `config/ad.php`
- `config/search.php`
- `config/sentry.php`
- `config/cookie.php`
- `config/cache.php`
- `config/scripts.php`
- `config/multisite.php`
- `config/developer.php`

Example templates are in `config-example/`.

### `wp-content/uploads/`

Uploads are not deployed and must persist on the server:

- gitignored
- removed by `build.php --cleanup`
- excluded by rsync

## Production prerequisites checklist (target server assumptions)

These are requirements the `municipio-deploy/4.0` action either explicitly checks in preflight, or relies on later during the deploy and post deploy steps.

### Required

- **SSH access**: The GitHub Action runner must be able to SSH to the host with the configured user + private key.
- **Rsync available**: The deploy uses rsync to update the release directory.
- **PHP CLI installed on the host**: Preflight checks `php -v` and validates the major.minor matches `php-version` input.
- **Correct PHP version**: Must match the workflow configured `${{ secrets.PHP_VERSION }}` (example: `8.2`).
- **WP-CLI installed on the host**: Post deploy runs `wp nginx-helper purge-all` and `wp cache flush` (best effort, but expected).
- **WordPress release directory exists and is writable**: The deploy user must be able to create, modify, and delete files under `deploy-host-path`.
- **Web server group exists and is usable**:
  - Preflight runs `chgrp litespeed testfile.txt` (hardcoded in the action), so the group `litespeed` must exist and the deploy user must be allowed to change group to it.
- **Permissions model**: The deploy assumes it can `chmod` files and apply group read permissions. Uploads need group write.

### Optional but commonly required in practice

- **sudo permission to kill PHP workers**: If the workflow sets `kill-lsphp: true`, the action runs `sudo killall -KILL lsphp`. The deploy user needs sudo rights for that command without an interactive password prompt.
- **LiteSpeed page cache path**: If `deploy-host-pagecache-path` is set, the action will `rm -rf` that path. It must be correct and writable.
- **Cache directories**:
  - The action clears Blade cache and upload caches. Those paths must exist or at least not error fatally when removed (the action uses best effort `|| true` for some of them).

### Persisted server managed data (must not be overwritten by deploy)

- **`/config/*`**: production configuration is server managed (required by `wp-config.php`).
- **`wp-content/uploads/*`**: media uploads are server managed.
- **Any server local plugins prefixed `local_*`**: the action excludes these from rsync.

### Quick debug checklist for failed deploys

- If preflight fails:
  - verify SSH connectivity, key, port, and that the user can create files
  - verify `php -v` returns the expected major.minor
  - verify the `litespeed` group exists and `chgrp litespeed <file>` works for the deploy user
- If post deploy cache clear fails:
  - verify `wp` exists on the host and is runnable by the deploy user
- If deploy succeeds but site is broken:
  - verify `/config/*` exists and matches the new code expectations
  - verify built asset directories were committed for any repo local frontend changes (`dist/`, `build/`, `public/`)

## What is the deployable artifact (what ends up on the server)

After a deploy, the server receives a tree that typically includes:

- `wp/` (WordPress core installed via Composer during CI)
- `vendor/` (PHP dependencies installed during CI)
- `wp-content/` (themes, plugins, mu-plugins, built assets, plus ACF Pro downloaded by CI)

And it preserves:

- `/config/*` (server managed)
- `wp-content/uploads/*` (server managed)
- any `local_*` plugins/mu-plugins present on the server

## Local Docker is separate from production deploy

Local development (`docker compose up`) uses:

- root `Dockerfile` and `docker-compose.yml`
- `.env` (gitignored)
- `.docker/config` mounted into the container at `/var/www/html/config` so `wp-config.php` can load config locally without touching production config.

Production does not use this Docker Compose setup.

## Node tooling, built assets, and what must be committed

### What CI does today in this repo

- The deploy action installs Node on the GitHub runner.
- However, in this repo as it currently stands, the build phase that runs `php ./build.php ... --install-npm` does **not** actually run `npm`, `yarn`, `pnpm`, `gulp`, `npx`, `wp-scripts`, or `webpack` for project code because:
  - the root `build.php` only executes child `build.php` scripts if they exist, and
  - the only child build script that contains npm commands (`wp-content/plugins/media-usage/build.php`) has the npm and gulp block commented out.

Practical result: production deploy currently depends on **prebuilt frontend assets already present in the repo** (or shipped by upstream packages).

### When you must run Node tooling locally before deploying

If you change frontend source code in a plugin/theme that expects compiled output, you must:

- run that package’s build locally, and
- commit the generated artifacts (`dist/`, `build/`, `public/bundle.*`, etc.)

Otherwise CI will deploy without updated compiled assets.

In this repo, Node tooling is declared (has `package.json`) in:

- `wp-content/plugins/media-usage/` (webpack, outputs `dist/`)
- `wp-content/plugins/stream/` (webpack config present, outputs `build/` in this repo)
- `wp-content/plugins/pressidium-cookie-consent/` (`wp-scripts`, outputs `public/bundle.*`)
- `wp-content/plugins/local_sater-modularity/` (gulp, outputs `dist/`)
- `wp-content/plugins/local_fel_fel_modularity-flowbox/` (gulp tooling declared)
- `wp-content/plugins/local_wp-media-folder/class/divi-widgets/` (`divi-scripts`, outputs `scripts/` and `styles/`)
- `wp-content/plugins/litespeed-cache/` (prettier only, formatting, not a build artifact producer)

### Do Composer packages already include built assets?

Usually, yes.

If a plugin/theme comes in via Composer as a normal tagged release, it typically ships with its compiled assets already included in the package. In that case, CI deploying the Composer installed files is enough.

Known exceptions to treat carefully:

- the package is source only and expects a build step during install
- the package is a `dev-*` or VCS reference (not a packaged release) and compiled assets are not reliably present
- a release was published incorrectly without built assets

Rule of thumb for this repo:

- If you did not modify the plugin/theme in this repo, and it comes from a tagged Composer release, assume assets are already built.
- If you modify a repo local plugin/theme that has `package.json` and a `dist/` or `build/` output, build locally and commit the output unless CI is explicitly updated to build it.

