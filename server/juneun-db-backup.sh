#!/bin/bash
# Daily MySQL dump for juneun, uploaded to the private
# juneun-church-backups R2 bucket as YYYYMMDD-HHMMSS/dump.sql.zst.
# Credentials come from the app .env; nothing is kept on local disk.
#
# Installed at /usr/local/bin/juneun-db-backup.sh (root, mode 700) and
# run daily at 01:30 Brisbane from the root crontab.
set -euo pipefail

ENV_FILE=/var/www/juneun/.env
STAMP=$(date +%Y%m%d-%H%M%S)

get() { grep -E "^$1=" "$ENV_FILE" | head -1 | cut -d= -f2- | tr -d '"'; }

WORK=$(mktemp -d)
trap 'rm -rf "$WORK"' EXIT
chmod 700 "$WORK"

CNF="$WORK/client.cnf"
touch "$CNF"
chmod 600 "$CNF"
{
    echo '[client]'
    echo "host=$(get DB_HOST)"
    echo "user=$(get DB_USERNAME)"
    echo "password=$(get DB_PASSWORD)"
} > "$CNF"

mysqldump --defaults-extra-file="$CNF" --single-transaction --quick \
    --no-tablespaces --routines --triggers "$(get DB_DATABASE)" \
    | zstd -q -o "$WORK/dump.sql.zst"

export AWS_ACCESS_KEY_ID=$(get CLOUDFLARE_R2_ACCESS_KEY)
export AWS_SECRET_ACCESS_KEY=$(get CLOUDFLARE_R2_SECRET_KEY)
export AWS_DEFAULT_REGION=auto
aws s3 cp --only-show-errors \
    "$WORK/dump.sql.zst" \
    "s3://juneun-church-backups/$STAMP/dump.sql.zst" \
    --endpoint-url "$(get CLOUDFLARE_R2_ENDPOINT)"
