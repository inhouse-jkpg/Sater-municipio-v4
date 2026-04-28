#!/usr/bin/env bash
# Generate local TLS certs for sater.test + subdomains using mkcert.
# Prereqs:
#   macOS:  brew install mkcert
#   Linux:  apt install libnss3-tools && install mkcert from
#           https://github.com/FiloSottile/mkcert/releases
set -euo pipefail

mkcert -install

mkdir -p ./nginx/ssl

mkcert \
  -key-file ./nginx/ssl/sater.test.key \
  -cert-file ./nginx/ssl/sater.test.crt \
  "*.sater.test" "sater.test"

cat <<EOF

Certs generated in ./nginx/ssl.

Next: add these lines to /etc/hosts (macOS/Linux) or
C:\\Windows\\System32\\drivers\\etc\\hosts (Windows) as admin:

127.0.0.1 sater.test
127.0.0.1 pma.sater.test
127.0.0.1 mail.sater.test
EOF
