$ErrorActionPreference = "Stop"
Set-Location (Join-Path $PSScriptRoot "..")
New-Item -ItemType Directory -Force backups | Out-Null
$file = "backups/gestao-$(Get-Date -Format yyyyMMdd-HHmmss).sql"
# Redirection occurs inside the container to preserve UTF-8 on Windows PowerShell 5.
docker compose exec -T db sh -c 'mariadb-dump --single-transaction --quick --skip-lock-tables -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" "$MARIADB_DATABASE" > /tmp/gestao-backup.sql'
if ($LASTEXITCODE -ne 0) { throw "Falha no backup" }
docker compose cp db:/tmp/gestao-backup.sql $file
if ($LASTEXITCODE -ne 0) { throw "Falha ao copiar backup" }
docker compose exec -T db rm /tmp/gestao-backup.sql
Write-Host "Backup: $file"
