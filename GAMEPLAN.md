# Gameplan: Sater Municipio v4 — Upstream Independence

## Overview

Full upstream independence for the Sater Municipio v4 deployment. Every external dependency locked so that Helsingborg stad, any third-party package author, or any external service can change whatever they want without affecting this deployment.

**Total estimate: 60–75 hours**
**Client time required: ~1 hour** (final walkthrough + sign-off)

### Locked decisions

1. `vendor/` will be committed to git for full upstream independence.
2. Forks of `municipio-deploy` and `municipio-e2e-tests` are owned by us, not the client.
3. Three-branch model: `master`, `production`, `stage`. No separate `stage-client` branch. `stage` is always based on `production` and serves as both the client UAT environment and the integration branch where cherry-picks from `master` are tested before merging into `production`.

---

## Progress Checklist

Track execution here. Tick each box as work completes. Detailed descriptions of every step are in Section 2 and Section 3 below.

### Phase 1 — Composer Lockdown *(2 hrs)*
- [x] 1.1 Remove `composer.lock` from `$removables` in `build.php` line 42
- [x] 1.2 Lock down `composer.local.json` usage in CI (document + guard)
- [x] 1.3 Lock `acf-export-manager` to exact version `1.0.12` across all 13 affected `composer.json` files
- [x] 1.4a Remove `/vendor/` and `composer.lock` from `.gitignore`
- [ ] 1.4b Commit full vendor tree + committed `composer.lock` (deferred)

### Phase 2 — Fork and Pin Deploy Pipeline *(2.5 hrs)*
- [ ] 2.1 Fork `helsingborg-stad/municipio-deploy` and `helsingborg-stad/municipio-e2e-tests` into our GitHub org
- [ ] 2.2 Pin all sub-actions inside the fork to commit SHAs (`appleboy/ssh-action`, `actions/setup-node`, `shivammathur/setup-php`, `actions/cache`, `burnett01/rsync-deployments`)
- [ ] 2.3 Add `vendor/` to rsync exclusions inside the forked action
- [ ] 2.4 Update all 7 workflow files to point at the fork at a pinned SHA

### Phase 3 — Theme Composer Hardening *(2.5 hrs)*
- [ ] 3.1 Research tagged releases for all 6 `dev-master` theme dependencies
- [ ] 3.2 Pin or fork each `dev-master` reference to a specific version or SHA
- [ ] 3.3 Generate and commit `wp-content/themes/municipio/composer.lock`

### Phase 4 — Branch Strategy *(1 hr)*
- [ ] 4.1 Remove `master` from `deploy-production.yml` trigger branches
- [ ] 4.2 Create `production` branch from current `master` and push
- [ ] 4.3 Verify GitHub "Sync fork" on `master` does not trigger any deploy workflow
- [ ] 4.4 Document branch rules (master / production / stage) in repo README

### Phase 5 — Verify End to End *(6–8 hrs)*
- [ ] 5.1 Trigger `stage` deploy via `workflow_dispatch`
- [ ] 5.2 Confirm `vendor/` is excluded from rsync on the server
- [ ] 5.3 Confirm `composer install` runs from committed lock file
- [ ] 5.4 Confirm theme builds correctly
- [ ] 5.5 Run E2E tests via forked action
- [ ] 5.6 Deploy to `production` from the `production` branch
- [ ] 5.7 Smoke test `stage` and `production`
- [ ] 5.8 Confirm disabled intranet workflows do not block the Actions UI (they are commented out because there are no intranet branches in use)

### Phase 6 — Root-Level Docker Setup *(3.5 hrs, independent of production track)*
- [ ] 6.1 Create root-level `Dockerfile` (PHP 8.3-fpm, pinned extensions, Composer)
- [ ] 6.2 Create root-level `docker-compose.yml` with nginx, php-fpm, mariadb, phpmyadmin
- [ ] 6.3 Create `nginx/default.conf` matching production multisite rewrite rules
- [ ] 6.4 Create `.env.example` with safe local defaults
- [ ] 6.5 Verify `docker-compose up` brings the site up and DB seeds correctly

### Wrap-up
- [ ] Update project documentation
- [ ] Regression test pass across all environments
- [ ] Client handover + walkthrough

---

## Branch Strategy

| Branch | Purpose | Upstream sync | Deploys to |
|---|---|---|---|
| `master` | Helsingborg mirror, read-only | Yes, via GitHub Sync fork button | Nowhere, never deploy from this |
| `production` | Locked production code | No | Production server |
| `stage` | Based on `production`. Client UAT + integration branch where cherry-picks from `master` are tested. | No | Stage server |

`production` already exists as a trigger in `deploy-production.yml`. The only change is removing `master` from that trigger so syncing `master` never accidentally deploys.

**How upstream review works:**
1. Click "Sync fork" on GitHub → `master` gets Helsingborg's latest
2. Diff `master` against `production` to see what's new
3. Cherry-pick specific changes you want into `stage`
4. Test on stage
5. Merge to `production` when confirmed

**Rules:**
- Never deploy from `master`. It is only for viewing upstream changes.
- Never merge `master` directly into any branch. Always cherry-pick.
- Production deploys only from `production` branch.
- `stage` is always based on `production`. Cherry-picks from `master` are applied on top of `stage` for testing, never directly into `production`.
- When `stage` drifts or needs a clean slate for UAT, reset it from `production` and re-apply any in-flight cherry-picks.

---

## Section 1 — Complete Upstream & Package Inventory

### 1.1 — WordPress Core

| Package | Version | Source | What it does |
|---|---|---|---|
| `johnpbloch/wordpress` | 6.9.1 | github.com/johnpbloch/wordpress | WordPress core installed via Composer |

---

### 1.2 — Core Theme

| Package | Version | Source | What it does |
|---|---|---|---|
| `helsingborg-stad/municipio` | 6.27.6 | github.com/helsingborg-stad/Municipio | The entire site theme and framework |
| `helsingborg-stad/blade` | 3.5.1 | helsingborg-stad | PHP Blade templating engine used by the theme |
| `helsingborg-stad/component-library` | 4.54.1 | helsingborg-stad | UI component library — buttons, cards, layouts |

---

### 1.3 — Modularity System (page builder modules)

All page content is built using these modules. Removing any one could break existing page content.

| Package | Version | What it does |
|---|---|---|
| `helsingborg-stad/modularity` | 6.85.0 | Core module framework — page builder system |
| `helsingborg-stad/modularity-contact-banner` | 4.0.12 | Contact information banner |
| `helsingborg-stad/modularity-entryscape` | 5.0.12 | Entryscape open data integration |
| `helsingborg-stad/modularity-form-builder` | 4.0.6 | Dynamic form creation |
| `helsingborg-stad/modularity-guides` | 5.0.4 | Step-by-step guide module |
| `helsingborg-stad/modularity-json-render` | 4.0.2 | Renders data from JSON sources |
| `helsingborg-stad/modularity-open-street-map` | 3.0.7 | OpenStreetMap display |
| `helsingborg-stad/modularity-products` | 4.0.4 | Product/service catalog |
| `helsingborg-stad/modularity-recommend` | 4.1.0 | Content recommendations |
| `helsingborg-stad/modularity-sections` | 4.0.4 | Page section layouts |
| `helsingborg-stad/modularity-testimonials` | 4.0.4 | Reviews and testimonials |
| `helsingborg-stad/modularity-timeline` | 5.0.4 | Timeline and history |
| `helsingborg-stad/modularity-interactive-img-map` | pinned | Interactive image maps |
| `helsingborg-stad/modularity-local-events` | pinned | Local events display |
| `helsingborg/modularity-dynamic-guides` | 2.0.7 | Dynamic guide module |
| `municipio-se/modularity-noticeboard` | 1.1.0 | Notice board |

---

### 1.4 — Search & Indexing

| Package | Version | What it does | External service |
|---|---|---|---|
| `helsingborg-stad/algolia-index` | 3.5.10 | Algolia search indexing | Algolia API |
| `helsingborg-stad/algolia-index-js-searchpage-addon` | 3.6.12 | JavaScript search UI | Algolia API |
| `helsingborg-stad/algolia-index-modularity-addon` | 3.1.5 | Indexes Modularity modules | Algolia API |
| `helsingborg-stad/algolia-index-typesense-provider` | 1.0.13 | Typesense search backend | Typesense API |
| `helsingborg-stad/search-notices` | 3.0.6 | Notice display in search results | — |
| `helsingborg-stad/wp-search-statistics` | 1.0.6 | Tracks what users search for | — |

---

### 1.5 — API Integrations (external Helsingborg services)

These connect to external Helsingborg stad systems. If those systems are decommissioned these plugins stop working.

| Package | Version | What it does | External service |
|---|---|---|---|
| `helsingborg-stad/api-alarm-integration` | 5.0.3 | Alarm/alert system display | Helsingborg alarm API |
| `helsingborg-stad/api-event-manager-integration` | 3.1.0 | Events from external API | Helsingborg event API |
| `helsingborg-stad/api-volunteer-manager-integration` | 5.1.3 | Volunteer management | Helsingborg volunteer API |
| `helsingborg-stad/active-directory-api-wp-integration` | 3.1.8 | Active Directory authentication | Helsingborg AD |

---

### 1.6 — Authentication & Security

| Package | Version | What it does |
|---|---|---|
| `helsingborg-stad/miniorange-saml-20-single-sign-on` | 5.2.4 | SAML 2.0 SSO login |
| `helsingborg-stad/wpmu-propagate-miniorange-saml-sso-settings` | 0.1.3 | Propagates SSO config across multisite |
| `helsingborg-stad/wpmu-security` | 1.9.0 | Security hardening for multisite |
| `helsingborg-stad/wpmu-remove-user-endpoint` | 0.2.2 | Removes user REST endpoint |
| `helsingborg-stad/force-ssl` | 3.0.3 | Enforces HTTPS |

---

### 1.7 — File Storage (AWS S3)

| Package | Version | What it does | External service |
|---|---|---|---|
| `humanmade/s3-uploads` | 3.0.12 | Stores media uploads in S3 | AWS S3 |
| `aws/aws-sdk-php` | 3.371.0 | AWS SDK for PHP | AWS |
| `helsingborg-stad/s3-uploads-custom-endpoint` | pinned | Custom S3 endpoint config | S3-compatible storage |

---

### 1.8 — Cache & Performance

| Package | Version | What it does |
|---|---|---|
| `wpackagist-plugin/litespeed-cache` | 7.7 | LiteSpeed server cache |
| `wpackagist-plugin/nginx-helper` | 2.3.5 | Nginx FastCGI cache purging |
| `wpackagist-plugin/redis-cache` | 2.7.0 | Redis object cache |
| `wpackagist-plugin/varnish-http-purge` | 5.6.5 | Varnish cache purging |
| `helsingborg-stad/wpmu-litespeed-common-settings` | 2.0.2 | Shared LiteSpeed config across multisite |
| `helsingborg-stad/wpmu-invalidate-login-pagecache` | 1.1.1 | Clears cache on login |

---

### 1.9 — Content & Admin Management

| Package | Version | What it does |
|---|---|---|
| `helsingborg-stad/attachment-revisions` | 3.0.11 | Attachment revision history |
| `helsingborg-stad/better-post-ui` | 3.1.2 | Enhanced post editor |
| `helsingborg-stad/broken-link-detector` | 4.2.26 | Detects broken links |
| `helsingborg-stad/custom-short-links` | 3.0.8 | URL shortening |
| `helsingborg-stad/customer-feedback` | 4.2.0 | Feedback collection |
| `helsingborg-stad/easy-to-read-alternative` | 3.0.8 | Accessibility alternative text |
| `helsingborg-stad/job-listings` | 4.1.8 | Job postings |
| `helsingborg-stad/like-posts` | 3.1.3 | Post likes/favorites |
| `helsingborg-stad/lix-calculator` | 4.1.3 | Readability scoring |
| `helsingborg-stad/media-usage` | 3.0.8 | Media usage reporting |
| `helsingborg-stad/redirection-extended` | 3.0.6 | URL redirects |
| `helsingborg-stad/webhooks-manager` | 1.3.0 | Webhook management |
| `wpackagist-plugin/redirection` | 5.7.3 | URL redirect management |
| `wpackagist-plugin/stream` | 4.1.1 | Activity audit log |
| `wpackagist-plugin/post-expirator` | 4.9.4 | Auto-expire posts |
| `wpackagist-plugin/wp-nested-pages` | 3.2.13 | Hierarchical page management |
| `wpackagist-plugin/worddown` | 1.1.3 | Markdown support |

---

### 1.10 — Multisite Infrastructure

| Package | Version | What it does |
|---|---|---|
| `helsingborg-stad/multi-network-urls` | 2.0.0 | Multinetwork URL handling |
| `helsingborg-stad/multisite-role-propagation` | 3.0.7 | Propagates roles across sites |
| `helsingborg-stad/wpmu-correct-file-paths` | 1.2.2 | Corrects file paths in multisite |
| `helsingborg-stad/wpmu-focus-point` | 0.2.6 | Image focus point for multisite |
| `helsingborg-stad/wpmu-mu-plugins-url-everywhere` | 2.0.3 | Standardises mu-plugin URLs |
| `helsingborg-stad/wpmu-network-admin-url` | 2.0.4 | Network admin URL helper |
| `helsingborg-stad/wpmu-acf-google-maps-key` | 1.0.1 | Shared Google Maps API key |
| `wpackagist-plugin/wp-multi-network` | 3.0.0 | WordPress multinetwork support |
| `wpackagist-plugin/network-plugin-auditor` | 1.10.1 | Network-wide plugin audit |

---

### 1.11 — ACF (Advanced Custom Fields) Extensions

Installed as mu-plugins. Extend ACF Pro with additional field types.

| Package | Version | What it does | Upstream risk |
|---|---|---|---|
| `advanced-custom-fields-pro` | via ACF licence URL | Custom fields for all content | ACF licence required |
| `helsingborg-stad/acf-openstreetmap-field` | 0.79.13 | Map picker field | — |
| `helsingborg-stad/acf-icon-field` | 0.2.46 | Icon selector field | — |
| `helsingborg-stad/acf-select-image-field` | 1.6.1 | Image selector field | — |
| `johannheyne/advanced-custom-fields-table-field` | `dev-master` ⚠️ | Table field | Tracks live master branch |
| `clark-nikdel-powell/post-type-select-for-acf` | `dev-master` ⚠️ | Post type selector | Tracks live master branch |
| `jeradin/acf-website-field` | `dev-master` ⚠️ | Website field | Tracks live master branch |
| `jeradin/acf-dynamic-table-field` | `dev-master` ⚠️ | Dynamic table | Tracks live master branch |
| `ooksanen/acf-focuspoint` | `^1.2.1` ⚠️ | Image focus point | Floating constraint |
| `helsingborg-stad/acf-export-manager` | `>=1.0.0` ⚠️ | ACF field export | Live VCS reference — 11 packages point here |

---

### 1.12 — SEO, Cookies & Compliance

| Package | Version | What it does |
|---|---|---|
| `wpackagist-plugin/autodescription` | 5.1.4 | SEO meta tags |
| `wpackagist-plugin/cookies-and-content-security-policy` | 2.37 | Cookie consent + CSP |
| `wpackagist-plugin/pressidium-cookie-consent` | 1.9.1 | Cookie consent UI |
| `helsingborg-stad/visit-custom-posttypes-taxonomies` | 2.0.8 | Custom post types for tourism |

---

### 1.13 — User Portal (My Pages)

| Package | Version | What it does |
|---|---|---|
| `helsingborg-stad/mod-my-pages` | 2.0.3 | User personal pages |
| `helsingborg-stad/gdi-modularity-my-pages-about-me` | 2.0.1 | User profile section |

---

### 1.14 — Theme Customizer

| Package | Version | What it does |
|---|---|---|
| `helsingborg-stad/kirki` | pinned | Theme customizer options UI |

---

### 1.15 — Utility & Infrastructure (PHP)

| Package | Version | What it does |
|---|---|---|
| `firebase/php-jwt` | v7.0.2 | JWT token handling |
| `composer/installers` | v2.3.0 | WordPress package installation paths |
| `helsingborg-stad/wpservice` | `^2.0` ⚠️ | WordPress service layer used by most plugins |
| `helsingborg-stad/wputilservice` | `^0.2.44` ⚠️ | WordPress utility service used by most plugins |
| `helsingborg-stad/acfservice` | `^1.0+` ⚠️ | ACF service layer |
| `helsingborg-stad/schema-library` | 0.5.4 | Structured data / schema.org |
| `wpackagist-plugin/simple-smtp` | 1.3.4.1 | SMTP mail sending |
| `wpackagist-plugin/user-switching` | 1.11.1 | Admin user impersonation |
| `wpackagist-plugin/username-changer` | 3.2.3 | Username editing |
| `wpackagist-plugin/simplify-admin-menus` | 1.3.2 | Admin menu customization |
| `wpackagist-plugin/fakerpress` | 0.8.0 | Test data generation |

---

### 1.16 — Custom Sater Plugins (owned by Sater, not upstream)

These are the only packages fully owned by Sater.

| Plugin | Type | What it does |
|---|---|---|
| `sater-hello-module` | Plugin | Demo Modularity module — can be removed |
| `sater-modularity-search` | Plugin | Filters search to pages/news/events, excludes Modularity module post types |
| `sater-sdg-meta-tags` | Plugin | EU Single Digital Gateway compliance meta tags |
| `custom-events-order` | MU-plugin | Forces events post type to sort by `start_datum` ASC, hides past events |

---

### 1.17 — GitHub Actions Pipeline

| Workflow | Triggers on | Action used | Pinned? |
|---|---|---|---|
| `deploy-production.yml` | push to `production`, `master` | `helsingborg-stad/municipio-deploy/4.1@master` | ❌ |
| `deploy-stage.yml` | push to `stage`, `beta`, `test` | `helsingborg-stad/municipio-deploy/4.1@master` | ❌ |
| `deploy-intranet-production.yml` | push to `production-intranet` | `helsingborg-stad/municipio-deploy/4.1@master` | ❌ |
| `deploy-intranet-stage.yml` | push to `stage-intranet` | `helsingborg-stad/municipio-deploy/4.1@master` | ❌ |
| `deploy-zip.yml` | push to `zip` | `helsingborg-stad/municipio-deploy/5.0@master` | ❌ |
| `composer-update.yml` | manual only | `actions/checkout@v3`, `shivammathur/setup-php@v2` | ❌ |
| `e2e.yml` | manual only | `helsingborg-stad/municipio-e2e-tests/.github/workflows/e2e.yml@main` | ❌ |

---

### 1.18 — External Runtime Services

Services the live site connects to. Not package dependencies but upstream connections nonetheless.

| Service | Used for | What breaks if gone |
|---|---|---|
| Algolia / Typesense | Search indexing and results | Search stops working |
| AWS S3 | Media file storage | Images/files inaccessible |
| Helsingborg Alarm API | Alarm display | Alarm module empty |
| Helsingborg Event API | Event listings | Events module empty |
| Helsingborg Volunteer API | Volunteer listings | Volunteer module empty |
| Helsingborg Active Directory | Staff SSO login | AD logins fail |
| ACF licence server | ACF Pro download during deploy | Deploy fails if key invalid |
| NewRelic | Performance monitoring | Monitoring only — site still works |
| Sentry | Error tracking | Error tracking only — site still works |

---

## Section 2 — Production Hardening Phases

---

### Phase 1 — Composer Lockdown
**Estimated time: 2 hours**

**1.1 — Fix `build.php`** *(15 min)*
Remove `composer.lock` from the `$removables` array on line 42. Without this fix every single deploy deletes the lock file and all hardening is bypassed.

**1.2 — Lock down `composer.local.json` usage in CI** *(30 min)*
This repo supports local dependency overrides via `composer.local.json` (merged via `wikimedia/composer-merge-plugin`).

For upstream independence, CI must not silently add extra dependencies at deploy time. Rule: `composer.local.json` must keep an empty `"require": {}` in CI.

Implementation:
- Add a guard step in all deploy workflows that fails if `composer.local.json` has any packages in `"require"`.
- Keep `composer.local.json` committed, but empty, to avoid merge conflicts while still supporting controlled local overrides when explicitly intended.

**1.3 — Lock `acf-export-manager` to current installed version** *(45 min)*
13 child `composer.json` files declared loose constraints (`>=1.0.0`, `^1.0`, `^1.0.12`) against a live VCS source on GitHub. Resolved differently across child locks (`1.0.11` vs `1.0.12`); the root `vendor/composer/installed.json` has `1.0.12` actually installed.

Fix: replaced every constraint with the exact version `1.0.12` (the currently installed version). Combined with the committed `composer.lock` (1.1) and committed `vendor/` (1.4), this makes upstream repo changes a non-issue for our deploys. The VCS `repositories` blocks were left in place since they are only consulted on `composer update`, which CI does not run.

Files updated:
- `wp-content/plugins/api-alarm-integration/composer.json`
- `wp-content/plugins/api-event-manager-integration/composer.json`
- `wp-content/plugins/broken-link-detector/composer.json`
- `wp-content/plugins/customer-feedback/composer.json`
- `wp-content/plugins/easy-to-read-alternative/composer.json`
- `wp-content/plugins/job-listings/composer.json`
- `wp-content/plugins/modularity-form-builder/composer.json`
- `wp-content/plugins/modularity-guides/composer.json`
- `wp-content/plugins/modularity-json-render/composer.json`
- `wp-content/plugins/modularity-sections/composer.json`
- `wp-content/plugins/visit-custom-posttypes-taxonomies/composer.json`
- `wp-content/plugins/webhooks-manager/composer.json`
- `wp-content/mu-plugins/wpmu-security/composer.json`

**1.4 — Un-ignore `vendor/` and `composer.lock`, then commit** *(30 min)*

Two sub-steps:

**1.4a (done on SAT-172):** Remove `/vendor/` and `composer.lock` from `.gitignore` so they can be tracked. `composer.lock` was also on the ignore list, which partially defeated the 1.1 fix; removing both locks down dependency resolution.

**1.4b (deferred):** After Phase 1 stabilises, commit the full `vendor/` tree (~118MB) and the generated `composer.lock`. After this, any upstream repo deletion has zero effect on deploys: all code lives in git history.

---

### Phase 2 — Fork and Pin the Deploy Pipeline
**Estimated time: 2.5 hours**

**2.1 — Fork on GitHub** *(15 min)*
Fork these two repos into our own GitHub org:
- `helsingborg-stad/municipio-deploy` (covers versions 4.1 and 5.0)
- `helsingborg-stad/municipio-e2e-tests`

**2.2 — Pin all sub-actions inside the fork** *(60 min)*
Inside the forked `action.yml`, replace all floating sub-action references with commit SHAs:
- `appleboy/ssh-action`
- `actions/setup-node`
- `shivammathur/setup-php`
- `actions/cache`
- `burnett01/rsync-deployments`

**2.3 — Add `vendor/` to rsync exclusions in the fork** *(15 min)*
The server builds `vendor/` via `composer install` during the pipeline. It must not be overwritten by rsync.

**2.4 — Update all 7 workflow files** *(45 min)*
Every workflow file points to upstream Helsingborg actions. All must be updated to point to the fork at a pinned commit SHA.

| File | Current | Action |
|---|---|---|
| `deploy-production.yml` | `municipio-deploy/4.1@master` | Point to fork, pin SHA |
| `deploy-stage.yml` | `municipio-deploy/4.1@master` | Point to fork, pin SHA |
| `deploy-intranet-production.yml` | `municipio-deploy/4.1@master` | Point to fork, pin SHA |
| `deploy-intranet-stage.yml` | `municipio-deploy/4.1@master` | Point to fork, pin SHA |
| `deploy-zip.yml` | `municipio-deploy/5.0@master` | Point to fork, pin SHA |
| `e2e.yml` | `municipio-e2e-tests/.github/workflows/e2e.yml@main` | Point to fork, pin SHA |
| `composer-update.yml` | `actions/checkout@v3`, `shivammathur/setup-php@v2` | Pin both to SHA |

---

### Phase 3 — Theme Composer Hardening
**Estimated time: 2.5 hours**

The theme `wp-content/themes/municipio/composer.json` has 6 live `dev-master` references tracking upstream branches directly.

| Package | Current | Risk |
|---|---|---|
| `johannheyne/advanced-custom-fields-table-field` | `dev-master` | Tracks live master branch |
| `clark-nikdel-powell/post-type-select-for-acf` | `dev-master` | Tracks live master branch |
| `jeradin/acf-website-field` | `dev-master` | Tracks live master branch |
| `jeradin/acf-dynamic-table-field` | `dev-master` | Tracks live master branch |
| `enshrined/svg-sanitize` | `dev-master` | Tracks live master branch |
| `landrok/language-detector` | `dev-master` | Tracks live master branch |

**3.1 — Research each package for tagged releases** *(60 min)*
Some may have no tagged releases — those will need forking or a commit SHA reference.

**3.2 — Pin or fork each one** *(60 min)*
Replace each `dev-master` with exact version or SHA.

**3.3 — Commit theme `composer.lock`** *(30 min)*
The theme currently has no `composer.lock`. Generate and force-track it in git.

---

### Phase 4 — Branch Strategy
**Estimated time: 1 hour**

**4.1 — Remove `master` from deploy triggers** *(15 min)*
In `deploy-production.yml`, change:
```yaml
branches: [production, master]
```
to:
```yaml
branches: [production]
```
This is the key change. After this, clicking "Sync fork" on `master` never triggers a deploy.

**4.2 — Create `production` branch from current `master`** *(15 min)*
```bash
git checkout master
git checkout -b production
git push origin production
```
All production deploys go through this branch from now on.

**4.3 — Verify fork sync works** *(15 min)*
On GitHub, view the `master` branch. Confirm the "Sync fork" button appears and does NOT trigger any deploy workflow when clicked.

**4.4 — Document branch rules** *(15 min)*
- `master`: Helsingborg mirror. Never deploy, never develop. Only purpose: click "Sync fork" to see what's new.
- `production`: locked production code. Only receives tested changes from `stage`.
- `stage`: always based on `production`. Integration + client UAT. Cherry-pick from `master` onto `stage`, test, then merge to `production`. Reset from `production` when a clean UAT snapshot is needed.

---

### Phase 5 — Verify End to End
**Estimated time: 6–8 hours**

- Trigger deploy to `stage` via `workflow_dispatch`.
- Confirm `vendor/` is excluded from rsync on server.
- Confirm `composer install` runs from committed lock file.
- Confirm theme builds correctly.
- Run E2E tests via forked action.
- Deploy to `production` from the `production` branch.
- Smoke test both environments (`stage`, `production`).
- Confirm the commented-out intranet workflows (`deploy-intranet-stage.yml`, `deploy-intranet-production.yml`) do not surface errors in the Actions UI.

---

## Section 3 — Local Dev Track

*(Completely separate from production — no overlap)*

---

### Phase 6 — Root-Level Docker Setup
**Estimated time: 3.5 hours**

Existing `.devcontainer/` stays untouched for VS Code users. This adds a simpler root-level alternative — `docker-compose up` and the site is running. No VS Code required.

**6.1 — `Dockerfile` at root** *(60 min)*
PHP 8.3-fpm with all required extensions (imagick, gd, mysqli, zip, intl), Composer — all at pinned versions.

**6.2 — `docker-compose.yml` at root** *(30 min)*
Four services, all images at pinned versions:
- `nginx` — web server
- Custom PHP-fpm image from root Dockerfile
- `mariadb` — pre-seeded from `db/seed.sql`
- `phpmyadmin` — database UI

**6.3 — `nginx/default.conf`** *(60 min)*
WordPress multisite rewrite rules, php-fpm passthrough, static file serving. Matches production server behaviour.

**6.4 — `.env.example` at root** *(20 min)*
All variables documented with safe local defaults. ACF Pro key and GitHub token clearly marked as optional — site loads for basic dev without them.

**6.5 — Test** *(30 min)*
`docker-compose up` → site loads. Verify DB seeds correctly, multisite works, Composer install runs from lock file.

---

## Section 4 — Time & Ownership Summary

| Phase | Track | What | Who | Time |
|---|---|---|---|---|
| 1 | Production | Composer lockdown + vendor/ + VCS fix | Me | 2 hrs |
| 2.1 | Production | Fork deploy repos on GitHub | Me | 15 min |
| 2.2–2.4 | Production | Pin sub-actions + update 7 workflows | Me | 2.5 hrs |
| 3 | Production | Theme composer hardening | Me | 2.5 hrs |
| 4 | Production | Branch strategy + fork sync verify | Me | 1 hr |
| 5 | Production | Full end-to-end verify (stage + production + intranet) | Me | 6–8 hrs |
| 6 | Local dev | Root Docker + nginx setup | Me | 3.5 hrs |
| — | Both | Documentation | Me | 3 hrs |
| — | Both | Testing + regression across environments | Me | 8 hrs |
| — | Both | Buffer for unknowns | — | 8 hrs |
| — | Both | Client handover + walkthrough | Both | 1 hr |

**Total: 60–75 hours**
**Client time: ~1 hour** (handover + walkthrough only)

---

## Section 5 — Risk Register

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| A `dev-master` theme dep has no tagged release | Medium | High | Fork that package |
| `acf-export-manager` repo deleted by Helsingborg | Medium | High | Pin VCS to SHA in Phase 1.3 |
| `composer.local.json` customised in CI accidentally | Low | High | Document + add CI check |
| Rsync overwrites `vendor/` on server | Low | High | Verify exclusion before live deploy |
| Nginx multisite config needs server-specific tweaks | Medium | Medium | Mirror production config closely |
| Deploy action sub-action SHA goes stale | Low | Low | Review pinned SHAs annually |
| Helsingborg decommissions an external API | Medium | Medium | Per-module fallback content |
