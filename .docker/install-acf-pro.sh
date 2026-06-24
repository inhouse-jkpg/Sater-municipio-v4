#!/usr/bin/env sh
set -eu

PLUGIN_DIR="/var/www/html/wp-content/plugins/advanced-custom-fields-pro"
DOWNLOAD_URL="${MUNICIPIO_ACF_PRO_DOWNLOAD_URL:-https://bitbucket.org/knowitjonkoping/knowit-plugins/raw/ea9adef10e3e72c4513a212d072573dec655862b/wordpress/advanced-custom-fields-pro/advanced-custom-fields-pro-6.7.0.2.zip}"

if [ -f "$PLUGIN_DIR/acf.php" ]; then
  echo "install-acf-pro: already installed, skipping"
  exit 0
fi

if [ -z "$DOWNLOAD_URL" ]; then
  echo "install-acf-pro: MUNICIPIO_ACF_PRO_DOWNLOAD_URL not set, skipping" >&2
  exit 0
fi

echo "install-acf-pro: downloading from zip..."

TMP_DIR="$(mktemp -d /tmp/acf-pro-install.XXXXXX)"
trap 'rm -rf "$TMP_DIR"' EXIT

if ! curl -fsSL -o "$TMP_DIR/acf-pro.zip" "$DOWNLOAD_URL"; then
  echo "install-acf-pro: download failed" >&2
  exit 1
fi

unzip -q -o "$TMP_DIR/acf-pro.zip" -d "$TMP_DIR"

if [ -d "$TMP_DIR/advanced-custom-fields-pro" ]; then
  SRC="$TMP_DIR/advanced-custom-fields-pro"
else
  echo "install-acf-pro: zip did not contain advanced-custom-fields-pro/" >&2
  exit 1
fi

mkdir -p "$(dirname "$PLUGIN_DIR")"
cp -r "$SRC" "$PLUGIN_DIR"

echo "install-acf-pro: installed to wp-content/plugins/advanced-custom-fields-pro"
