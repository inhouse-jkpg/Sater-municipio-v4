#!/usr/bin/env sh
set -eu

# MariaDB runs init scripts only on first boot (empty /var/lib/mysql).
# This importer keeps the workflow simple: drop a .sql (or .sql.gz) in ./db
# and it will be imported into $MYSQL_DATABASE regardless of whether the dump
# includes USE/CREATE DATABASE statements.

db="${MYSQL_DATABASE:-}"
root_pw="${MYSQL_ROOT_PASSWORD:-}"

if [ -z "$db" ]; then
  echo "db/00-import.sh: MYSQL_DATABASE is empty, nothing to import into" >&2
  exit 1
fi

sql="$(ls -1 /docker-entrypoint-initdb.d/*.sql 2>/dev/null | head -n 1 || true)"
sql_gz="$(ls -1 /docker-entrypoint-initdb.d/*.sql.gz 2>/dev/null | head -n 1 || true)"

if [ -n "$sql" ]; then
  echo "db/00-import.sh: importing $(basename "$sql") into ${db}"
  mariadb -uroot -p"${root_pw}" "${db}" < "$sql"
  exit 0
fi

if [ -n "$sql_gz" ]; then
  echo "db/00-import.sh: importing $(basename "$sql_gz") into ${db}"
  gunzip -c "$sql_gz" | mariadb -uroot -p"${root_pw}" "${db}"
  exit 0
fi

echo "db/00-import.sh: no .sql or .sql.gz found, skipping import"
