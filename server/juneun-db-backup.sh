#!/bin/bash
# Daily MySQL dump for juneun, mirroring the classic dated-directory
# layout (/usr/local/dump/YYYYMMDD-HHMMSS/dump.sql.zst). Credentials
# come from the app .env; dumps older than 90 days are pruned.
#
# Installed at /usr/local/bin/juneun-db-backup.sh (root, mode 700) and
# run daily at 01:30 Brisbane from the root crontab.
set -euo pipefail

ENV_FILE=/var/www/juneun/.env
DUMP_ROOT=/usr/local/dump
STAMP=$(date +%Y%m%d-%H%M%S)

get() { grep -E "^$1=" "$ENV_FILE" | head -1 | cut -d= -f2- | tr -d '"'; }

CNF=$(mktemp)
trap 'rm -f "$CNF"' EXIT
chmod 600 "$CNF"
{
    echo '[client]'
    echo "host=$(get DB_HOST)"
    echo "user=$(get DB_USERNAME)"
    echo "password=$(get DB_PASSWORD)"
} > "$CNF"

mkdir -p "$DUMP_ROOT/$STAMP"
chmod 750 "$DUMP_ROOT"

mysqldump --defaults-extra-file="$CNF" --single-transaction --quick \
    --no-tablespaces --routines --triggers "$(get DB_DATABASE)" \
    | zstd -q -o "$DUMP_ROOT/$STAMP/dump.sql.zst"

find "$DUMP_ROOT" -mindepth 1 -maxdepth 1 -type d -mtime +90 -exec rm -rf {} +
