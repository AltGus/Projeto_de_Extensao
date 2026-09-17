#!/bin/sh
set -eu
cd "$(dirname "$0")/.."
umask 077
mkdir -p backups
file="backups/gestao-$(date +%Y%m%d-%H%M%S).sql"
docker compose exec -T db sh -c 'exec mariadb-dump --single-transaction --quick --skip-lock-tables -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" "$MARIADB_DATABASE"' > "$file.tmp"
mv "$file.tmp" "$file"
printf 'Backup: %s\n' "$file"
