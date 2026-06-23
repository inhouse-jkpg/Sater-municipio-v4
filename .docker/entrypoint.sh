#!/usr/bin/env bash
set -euo pipefail

/usr/local/bin/install-acf-pro.sh || {
  echo "entrypoint: ACF Pro install failed; starting PHP-FPM anyway" >&2
}

exec docker-php-entrypoint "$@"
