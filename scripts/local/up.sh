#!/usr/bin/env bash
# Restart MariaDB and the PHP dev server for an existing local copy (background processes stop between sessions).
REPO="$(cd "$(dirname "$0")/../.." && pwd)"; S=/root/silq/site
mysqladmin ping >/dev/null 2>&1 || { (setsid mysqld_safe --user=root </dev/null >/tmp/mysqld.log 2>&1 &); for i in $(seq 30); do mysqladmin ping >/dev/null 2>&1 && break; sleep 1; done; }
curl -s -o /dev/null http://127.0.0.1:8080/ || (cd "$S" && PHP_CLI_SERVER_WORKERS=4 setsid nohup php -d memory_limit=512M -S 127.0.0.1:8080 -t "$S" "$REPO/scripts/local/router.php" </dev/null >/root/silq/php.log 2>&1 &)
sleep 1; mysqladmin ping; curl -s -o /dev/null -w "web %{http_code}\n" http://localhost:8080/
